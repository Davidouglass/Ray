<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Read-only Solana wallet data via the public JSON-RPC endpoint
 * (balances, SPL token holdings, recent signatures) with USD pricing
 * resolved through DexScreener. No signing / no secrets.
 */
class SolanaWallet
{
    private const RPC     = 'https://api.mainnet-beta.solana.com';
    private const TOKEN_PROGRAM = 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA';
    private const TOKEN_2022_PROGRAM = 'TokenzQdBNbLqP5VEhdkAS6EPFLC1PHnBqCXEpPxuEb';
    private const SOL_MINT = 'So11111111111111111111111111111111111111112';

    /** @return array<string,mixed> */
    public function portfolio(string $address): array
    {
        $address = trim($address);
        if (! $this->isValidAddress($address)) {
            return ['error' => 'Invalid Solana address'];
        }

        return Cache::store('file')->remember('sol_portfolio_' . $address, now()->addSeconds(45), function () use ($address) {
            $rpc = $this->rpcPool([
                'balance'   => ['getBalance', [$address]],
                'legacy'    => ['getTokenAccountsByOwner', [$address, ['programId' => self::TOKEN_PROGRAM], ['encoding' => 'jsonParsed']]],
                'token2022' => ['getTokenAccountsByOwner', [$address, ['programId' => self::TOKEN_2022_PROGRAM], ['encoding' => 'jsonParsed']]],
            ]);

            $lamports = (int) ($rpc['balance']['value'] ?? 0);
            $sol      = $lamports / 1_000_000_000;

            $accounts = array_merge($rpc['legacy']['value'] ?? [], $rpc['token2022']['value'] ?? []);

            $holdings = [];
            $mints    = [];
            foreach ($accounts as $acc) {
                $info = $acc['account']['data']['parsed']['info'] ?? [];
                $mint = $info['mint'] ?? '';
                $amt  = (float) ($info['tokenAmount']['uiAmount'] ?? 0);
                if ($mint === '' || $amt <= 0) {
                    continue;
                }
                $holdings[$mint] = $amt;
                $mints[] = $mint;
            }

            $prices = $this->prices(array_merge([self::SOL_MINT], $mints));
            $solPrice = $prices[self::SOL_MINT]['price'] ?? 0.0;
            $solChange = (float) ($prices[self::SOL_MINT]['change'] ?? 0);

            $tokens = [];
            $total  = $sol * $solPrice;
            foreach ($holdings as $mint => $amt) {
                $meta = $prices[$mint] ?? [];
                $p    = (float) ($meta['price'] ?? 0);
                if ($p <= 0) {
                    continue;
                }
                $usd  = $amt * $p;
                $total += $usd;
                $tokens[] = [
                    'mint'   => $mint,
                    'symbol' => $meta['symbol'] ?? substr($mint, 0, 4),
                    'name'   => $meta['name'] ?? 'Unknown token',
                    'img'    => $meta['image'] ?? '',
                    'price'  => $p,
                    'change' => (float) ($meta['change'] ?? 0),
                    'amount' => $amt,
                    'usd'    => $usd,
                ];
            }
            usort($tokens, fn ($a, $b) => $b['usd'] <=> $a['usd']);

            $weighted = ($sol * $solPrice) * $solChange;
            $weightsum = $sol * $solPrice;
            foreach ($tokens as $t) {
                $weighted  += $t['usd'] * $t['change'];
                $weightsum += $t['usd'];
            }
            $change24h = $weightsum > 0 ? $weighted / $weightsum : 0.0;

            return [
                'address'    => $address,
                'sol'        => $sol,
                'solPrice'   => $solPrice,
                'solUsd'     => $sol * $solPrice,
                'solChange'  => $solChange,
                'change24h'  => $change24h,
                'tokens'     => $tokens,
                'total'      => $total,
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    /** @return array<int,array<string,mixed>> recent signatures */
    public function activity(string $address, int $limit = 12): array
    {
        $address = trim($address);
        if (! $this->isValidAddress($address)) {
            return [];
        }

        return Cache::store('file')->remember('sol_activity_' . $address . '_' . $limit, now()->addSeconds(30), function () use ($address, $limit) {
            $sig = $this->rpc('getSignaturesForAddress', [$address, ['limit' => $limit]]);
            $out = [];
            foreach ($sig ?? [] as $s) {
                $out[] = [
                    'signature' => $s['signature'] ?? '',
                    'slot'      => $s['slot'] ?? 0,
                    'blockTime' => $s['blockTime'] ?? null,
                    'err'       => ! empty($s['err']),
                ];
            }

            return $out;
        });
    }

    /** RPC endpoint (override with SOLANA_RPC_URL for a dedicated node). */
    private function endpoint(): string
    {
        return (string) (config('services.solana_rpc') ?: self::RPC);
    }

    /**
     * Run several JSON-RPC calls concurrently.
     *
     * @param  array<string,array{0:string,1:array}>  $calls  key => [method, params]
     * @return array<string,array<string,mixed>|null>  key => result|null
     */
    private function rpcPool(array $calls): array
    {
        $endpoint  = $this->endpoint();
        $responses = Http::pool(function ($pool) use ($calls, $endpoint) {
            $reqs = [];
            foreach ($calls as $key => [$method, $params]) {
                $reqs[$key] = $pool->as($key)
                    ->asJson()
                    ->connectTimeout(4)
                    ->timeout(12)
                    ->acceptJson()
                    ->post($endpoint, [
                        'jsonrpc' => '2.0',
                        'id'      => 1,
                        'method'  => $method,
                        'params'  => $params,
                    ]);
            }

            return $reqs;
        });

        $out = [];
        foreach ($calls as $key => $_) {
            $res = $responses[$key] ?? null;
            $out[$key] = ($res instanceof \Illuminate\Http\Client\Response && $res->ok())
                ? ($res->json('result') ?? null)
                : null;
        }

        return $out;
    }

    /** @return array<string,array<string,mixed>> mint => meta */
    public function prices(array $mints): array
    {
        $mints = array_values(array_unique(array_filter($mints)));
        if (! $mints) {
            return [];
        }

        $chunks    = array_chunk($mints, 30);
        $responses = Http::pool(function ($pool) use ($chunks) {
            $reqs = [];
            foreach ($chunks as $i => $chunk) {
                $reqs[] = $pool->as('c' . $i)
                    ->connectTimeout(4)
                    ->timeout(8)
                    ->acceptJson()
                    ->get('https://api.dexscreener.com/tokens/v1/solana/' . implode(',', $chunk));
            }

            return $reqs;
        });

        $out = [];
        foreach ($responses as $res) {
            if (! $res instanceof \Illuminate\Http\Client\Response || ! $res->ok()) {
                continue;
            }
            foreach ($res->json() ?? [] as $p) {
                $addr = $p['baseToken']['address'] ?? '';
                if ($addr === '' || isset($out[$addr])) {
                    continue;
                }
                $out[$addr] = [
                    'price'  => (float) ($p['priceUsd'] ?? 0),
                    'symbol' => $p['baseToken']['symbol'] ?? '',
                    'name'   => $p['baseToken']['name'] ?? '',
                    'image'  => $p['info']['imageUrl'] ?? '',
                    'change' => (float) ($p['priceChange']['h24'] ?? 0),
                ];
            }
        }

        return $out;
    }

    /** @return array<string,mixed>|null */
    private function rpc(string $method, array $params): ?array
    {
        try {
            $res = Http::connectTimeout(4)->timeout(12)->acceptJson()->post($this->endpoint(), [
                'jsonrpc' => '2.0',
                'id'      => 1,
                'method'  => $method,
                'params'  => $params,
            ]);

            return $res->ok() ? ($res->json('result') ?? null) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Mint decimals for a token (9 for native SOL). */
    public function mintDecimals(string $mint): int
    {
        if ($mint === self::SOL_MINT) {
            return 9;
        }

        return Cache::store('file')->remember('mint_dec_' . $mint, now()->addDay(), function () use ($mint) {
            $info = $this->rpc('getAccountInfo', [$mint, ['encoding' => 'jsonParsed']]);

            return (int) ($info['value']['data']['parsed']['info']['decimals'] ?? 9);
        });
    }

    /** Owner balance (UI amount) for native SOL or an SPL mint. */
    public function balance(string $owner, string $mint): float
    {
        if ($mint === self::SOL_MINT) {
            $r = $this->rpc('getBalance', [$owner]);

            return (float) ((int) ($r['value'] ?? 0)) / 1e9;
        }

        $res = $this->rpc('getTokenAccountsByOwner', [
            $owner,
            ['mint' => $mint],
            ['encoding' => 'jsonParsed'],
        ]);

        $sum = 0.0;
        foreach (($res['value'] ?? []) as $acc) {
            $sum += (float) ($acc['account']['data']['parsed']['info']['tokenAmount']['uiAmount'] ?? 0);
        }

        return $sum;
    }

    public function isValidAddress(string $address): bool
    {
        if (! preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address)) {
            return false;
        }

        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $num = '0';
        foreach (str_split($address) as $ch) {
            $index = strpos($alphabet, $ch);
            if ($index === false) {
                return false;
            }
            $num = bcadd(bcmul($num, '58'), (string) $index);
        }

        $bytes = 0;
        $n = $num;
        while (bccomp($n, '0') > 0) {
            $bytes++;
            $n = bcdiv($n, '256', 0);
        }

        return $bytes >= 32 && $bytes <= 44;
    }
}
