<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Full market detail for a single token: best DexScreener pair,
 * RugCheck safety report, and a GeckoTerminal OHLCV price series.
 */
class TokenService
{
    private const GT = 'https://api.geckoterminal.com/api/v2';
    private const NET = ['solana' => 'solana', 'bsc' => 'bsc', 'ethereum' => 'eth', 'base' => 'base'];

    public function __construct(private PulseFeed $pulse)
    {
    }

    /** @return array<string,mixed> */
    public function detail(string $address, string $chain = 'solana'): array
    {
        $address = preg_replace('/[^A-Za-z0-9]/', '', $address) ?? '';
        if ($address === '') {
            return ['error' => 'Invalid token address'];
        }

        return Cache::store('file')->remember('token_' . $address, now()->addSeconds(45), function () use ($address, $chain) {
            $pairs = $this->pairs($address);
            if (empty($pairs)) {
                return ['error' => 'Token not found', 'address' => $address];
            }

            usort($pairs, fn ($a, $b) => ($b['liquidity']['usd'] ?? 0) <=> ($a['liquidity']['usd'] ?? 0));
            $p = $pairs[0];

            $pool = $p['pairAddress'] ?? '';

            return [
                'address'   => $address,
                'chain'     => $p['chainId'] ?? 'solana',
                'dex'       => $p['dexId'] ?? '',
                'symbol'    => $p['baseToken']['symbol'] ?? '?',
                'name'      => $p['baseToken']['name'] ?? '',
                'img'       => $p['info']['imageUrl'] ?? '',
                'price'     => (float) ($p['priceUsd'] ?? 0),
                'priceRaw'  => $p['priceUsd'] ?? '0',
                'mcap'      => (float) ($p['marketCap'] ?? $p['fdv'] ?? 0),
                'fdv'       => (float) ($p['fdv'] ?? 0),
                'liq'       => (float) ($p['liquidity']['usd'] ?? 0),
                'vol24'     => (float) ($p['volume']['h24'] ?? 0),
                'vol6'      => (float) ($p['volume']['h6'] ?? 0),
                'vol1'      => (float) ($p['volume']['h1'] ?? 0),
                'chg'       => $p['priceChange'] ?? [],
                'txns'      => $p['txns'] ?? [],
                'buys24'    => (int) ($p['txns']['h24']['buys'] ?? 0),
                'sells24'   => (int) ($p['txns']['h24']['sells'] ?? 0),
                'createdAt' => isset($p['pairCreatedAt']) ? (int) ($p['pairCreatedAt'] / 1000) : null,
                'socials'   => $p['info']['socials'] ?? [],
                'websites'  => $p['info']['websites'] ?? [],
                'pool'      => $pool,
                'url'       => $p['url'] ?? '',
                'safety'    => $this->pulse->tokenReport($address),
            ];
        });
    }

    /** @return array<int,array<string,mixed>> */
    private function pairs(string $address): array
    {
        try {
            $res = Http::connectTimeout(4)->timeout(8)->acceptJson()
                ->get('https://api.dexscreener.com/latest/dex/tokens/' . $address);

            return $res->ok() ? ($res->json('pairs') ?? []) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Highest-liquidity pool address for a token, or ''. */
    public function poolFor(string $address): string
    {
        $address = preg_replace('/[^A-Za-z0-9]/', '', $address) ?? '';
        if ($address === '') {
            return '';
        }

        $pairs = $this->pairs($address);
        if (empty($pairs)) {
            return '';
        }

        usort($pairs, fn ($a, $b) => ($b['liquidity']['usd'] ?? 0) <=> ($a['liquidity']['usd'] ?? 0));

        return (string) ($pairs[0]['pairAddress'] ?? '');
    }

    /**
     * Resolve a TradingView symbol for a mint, when TradingView carries the
     * pair. TradingView only indexes on-chain pools on Orca / Raydium /
     * Meteora (symbols use the "PREFIX:SYMBOL" form); pump.fun bonding-curve
     * and pumpswap pools return nothing. Cached 30 min per mint.
     *
     * @return array{symbol:?string,exchange:?string,provider:?string,source:?string}
     */
    public function tradingView(string $mint, string $symbol = ''): array
    {
        $mint = preg_replace('/[^A-Za-z0-9]/', '', $mint) ?? '';
        if ($mint === '') {
            return ['symbol' => null, 'exchange' => null, 'provider' => null, 'source' => null];
        }

        $want = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $symbol) ?? '');

        return Cache::store('file')->remember('tv_symbol_' . $mint . '_' . $want, now()->addMinutes(30), function () use ($mint, $want) {
            try {
                $res = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
                    'Accept'     => 'application/json',
                    'Origin'     => 'https://www.tradingview.com',
                    'Referer'    => 'https://www.tradingview.com/',
                ])->connectTimeout(4)->timeout(8)->get('https://symbol-search.tradingview.com/symbol_search/', [
                    'text'     => $mint,
                    'hl'       => '1',
                    'exchange' => '',
                    'lang'     => 'en',
                    'type'     => '',
                    'domain'   => 'production',
                ]);

                $rows = $res->ok() ? ($res->json() ?? []) : [];
            } catch (\Throwable $e) {
                $rows = [];
            }

            $first = null;
            foreach ($rows as $r) {
                $prefix = (string) ($r['prefix'] ?? '');
                $sym    = (string) ($r['symbol'] ?? '');
                if ($prefix === '' || $sym === '') {
                    continue;
                }

                $entry = [
                    'symbol'   => $prefix . ':' . $sym,
                    'exchange' => (string) ($r['exchange'] ?? ''),
                    'provider' => (string) ($r['provider_id'] ?? ''),
                    'source'   => (string) ($r['description'] ?? ''),
                ];

                if ($first === null) {
                    $first = $entry;
                }

                // Prefer the pool where this token is the base side
                // (e.g. "BONKUSDC_..." for BONK, not "BATTBONK_...").
                if ($want !== '' && str_starts_with(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $sym) ?? ''), $want)) {
                    return $entry;
                }
            }

            return $first ?? ['symbol' => null, 'exchange' => null, 'provider' => null, 'source' => null];
        });
    }

    /**
     * Real OHLCV candles (GeckoTerminal) for the best pool of a token.
     *
     * @return array{candles:array<int,array<string,mixed>>,pool:string,timeframe:string}
     */
    public function candles(string $address, string $timeframe = 'hour', int $aggregate = 1, int $limit = 168, string $pool = ''): array
    {
        $pool = $pool !== '' ? (string) (preg_replace('/[^A-Za-z0-9]/', '', $pool) ?? '') : $this->poolFor($address);
        if ($pool === '') {
            return ['candles' => [], 'pool' => '', 'timeframe' => $timeframe];
        }

        $allowed = ['minute' => [1, 5, 15], 'hour' => [1, 4, 12], 'day' => [1]];
        if (! isset($allowed[$timeframe])) {
            $timeframe = 'hour';
        }
        if (! in_array($aggregate, $allowed[$timeframe], true)) {
            $aggregate = $allowed[$timeframe][0];
        }
        $limit = max(10, min(1000, $limit));

        return Cache::store('file')->remember("candles_{$pool}_{$timeframe}_{$aggregate}_{$limit}", now()->addSeconds(30), function () use ($pool, $timeframe, $aggregate, $limit) {
            try {
                $res = Http::withHeaders(['User-Agent' => 'AxiomClone/1.0'])->connectTimeout(4)->timeout(10)->acceptJson()
                    ->get(self::GT . "/networks/solana/pools/{$pool}/ohlcv/{$timeframe}", [
                        'aggregate' => $aggregate,
                        'limit'     => $limit,
                    ]);
                $rows = $res->json('data.attributes.ohlcv_list') ?? [];
            } catch (\Throwable $e) {
                $rows = [];
            }

            $candles = [];
            foreach ($rows as $r) {
                $candles[] = [
                    'time'   => (int) ($r[0] ?? 0),
                    'open'   => (float) ($r[1] ?? 0),
                    'high'   => (float) ($r[2] ?? 0),
                    'low'    => (float) ($r[3] ?? 0),
                    'close'  => (float) ($r[4] ?? 0),
                    'volume' => (float) ($r[5] ?? 0),
                ];
            }
            usort($candles, fn ($a, $b) => $a['time'] <=> $b['time']);

            return ['candles' => $candles, 'pool' => $pool, 'timeframe' => $timeframe];
        });
    }
}
