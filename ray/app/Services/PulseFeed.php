<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Builds the three Pulse tabs from GeckoTerminal's free on-chain API.
 *
 *   new      -> /new_pools (newest pools), filtered to the pump.fun bonding curve
 *   final    -> pump-fun bonding-curve pools near the top of the curve
 *   migrated -> pumpswap pools (tokens that graduated off the bonding curve)
 *
 * Bonding-curve progress is computed from the pool FDV using pump.fun's curve
 * invariant: total supply 1e9, virtual SOL 30, virtual token reserves 1.073e9,
 * graduation at 85 real SOL.  See progressFromFdv().
 *
 * Axiom-proprietary fields (holder distribution, dev/bundler/sniper percentages,
 * views, fee) remain placeholders.
 */
class PulseFeed
{
    private const BASE  = 'https://api.geckoterminal.com/api/v2/networks/solana';
    private const ASSET = 'https://axiom-assets-v2.axiom-cdn.io/images';
    private const SOL_MINT = 'So11111111111111111111111111111111111111112';

    private const GRAD_SOL = 85.0;
    private const K        = 32.19;

    private const CHAIN_ART = self::ASSET . '/sol-fill.svg';

    private const VENUE = [
        'pump-fun'        => 'SolanaPump V1',
        'pumpswap'        => 'SolanaPump AMM',
        'stonkfun'        => 'SolanaStonkfun',
        'meteora-damm-v2' => 'SolanaMeteora AMM V2',
        'meteora'         => 'SolanaMeteora AMM',
        'raydium'         => 'SolanaRaydium AMM',
        'raydium-clmm'    => 'SolanaRaydium CLMM',
        'orca'            => 'SolanaOrca',
    ];

    /** @return array{new:array,final:array,migrated:array} */
    public function all(int $perTab = 20): array
    {
        return Cache::store('file')->remember('pulse_feed_v2', now()->addSeconds(60), function () use ($perTab) {
            $built = [
                'new'      => $this->newPairs($perTab),
                'final'    => $this->finalStretch($perTab),
                'migrated' => $this->migrated($perTab),
            ];

            $built = $this->attachSocials($built);

            $last = Cache::store('file')->get('pulse_feed_lastgood_v2', []);
            foreach ($built as $k => $rows) {
                if (empty($rows) && ! empty($last[$k])) {
                    $built[$k] = $last[$k];
                }
            }

            if (! empty($built['new']) && ! empty($built['final']) && ! empty($built['migrated'])) {
                Cache::store('file')->forever('pulse_feed_lastgood_v2', $built);
            }

            return $built;
        });
    }

    /** @return array<int,array<string,mixed>> */
    public function newPairs(int $limit = 20): array
    {
        $rows = $this->pools(self::BASE . '/new_pools?page=1&include=base_token')
            ->filter(fn ($r) => $r['dex'] === 'pump-fun')
            ->values();

        return $rows
            ->sortByDesc(fn ($r) => $r['createdTs'])
            ->take($limit)
            ->map(fn ($r) => $this->map($r))
            ->values()
            ->all();
    }

    /** @return array<int,array<string,mixed>> */
    public function finalStretch(int $limit = 20): array
    {
        $all = $this->pools(self::BASE . '/dexes/pump-fun/pools?page=1&include=base_token')
            ->filter(fn ($r) => $r['dex'] === 'pump-fun')
            ->values();

        $rows = $all->filter(fn ($r) => $r['progress'] >= 60)->values();
        if ($rows->count() < 4) {
            $rows = $all->sortByDesc('progress')->take(6)->values();
        }

        return $rows
            ->sortByDesc('progress')
            ->take($limit)
            ->map(fn ($r) => $this->map($r))
            ->values()
            ->all();
    }

    /** @return array<int,array<string,mixed>> */
    public function migrated(int $limit = 20): array
    {
        return $this->pools(self::BASE . '/dexes/pumpswap/pools?page=1&include=base_token')
            ->filter(fn ($r) => $r['dex'] === 'pumpswap')
            ->sortByDesc(fn ($r) => $r['createdTs'])
            ->take($limit)
            ->map(fn ($r) => $this->map($r))
            ->values()
            ->all();
    }

    /**
     * Fetch + normalise pools from a GeckoTerminal endpoint.
     *
     * @return \Illuminate\Support\Collection<int,array<string,mixed>>
     */
    private function pools(string $url): \Illuminate\Support\Collection
    {
        $json = $this->getJson($url);
        if (! is_array($json)) {
            return collect();
        }

        $included = [];
        foreach ($json['included'] ?? [] as $inc) {
            if (($inc['type'] ?? '') === 'token' && isset($inc['id'])) {
                $included[$inc['id']] = $inc['attributes'] ?? [];
            }
        }

        $out = collect();
        foreach ($json['data'] ?? [] as $d) {
            $a   = $d['attributes'] ?? [];
            $dex = $d['relationships']['dex']['data']['id'] ?? '';
            $baseId = $d['relationships']['base_token']['data']['id'] ?? '';
            $quoteId = $d['relationships']['quote_token']['data']['id'] ?? '';

            $mint = str_starts_with($baseId, 'solana_') ? substr($baseId, 7) : '';
            if ($mint === '' || $quoteId !== 'solana_' . self::SOL_MINT) {
                continue;
            }

            $tok  = $included[$baseId] ?? [];
            $sol  = (float) ($a['quote_token_price_usd'] ?? 0);
            $fdv  = (float) ($a['fdv_usd'] ?? $a['market_cap_usd'] ?? 0);
            $name = trim((string) ($tok['name'] ?? ''));
            $sym  = trim((string) ($tok['symbol'] ?? ''));
            if ($sym === '') {
                $parts = explode(' / ', (string) ($a['name'] ?? ''));
                $sym = trim($parts[0] ?? '?');
            }
            if ($name === '') {
                $name = $sym;
            }

            $out->push([
                'dex'        => $dex,
                'venue'      => self::VENUE[$dex] ?? ucfirst($dex),
                'sym'        => $sym,
                'name'       => $name,
                'img'        => ($tok['image_url'] ?? '') ?: self::CHAIN_ART,
                'mint'       => $mint,
                'createdTs'  => ($t = strtotime((string) ($a['pool_created_at'] ?? ''))) ? $t : 0,
                'mc'         => (float) ($a['market_cap_usd'] ?? $fdv),
                'fdv'        => $fdv,
                'sol'        => $sol,
                'progress'   => $this->progressFrom($fdv, $sol, $dex),
                'pct'        => (float) ($a['price_change_percentage']['m5'] ?? 0),
                'vol'        => (float) ($a['volume_usd']['h24'] ?? 0),
                'buys'       => (int) ($a['transactions']['h24']['buys'] ?? 0),
                'sells'      => (int) ($a['transactions']['h24']['sells'] ?? 0),
                'price'      => (float) ($tok['price_usd'] ?? ($a['base_token_price_usd'] ?? 0)),
            ]);
        }

        return $out;
    }

    /** GET a URL as JSON, retrying once on a GeckoTerminal/DexScreener 429. */
    private function getJson(string $url): ?array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $res = Http::withHeaders(['User-Agent' => 'AxiomClone/1.0'])
                    ->connectTimeout(4)->timeout(10)->acceptJson()->get($url);
                if ($res->status() === 429) {
                    usleep(1_500_000);
                    continue;
                }

                return $res->ok() ? $res->json() : null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Attach social links (X / Telegram / website) to every tab's rows using a
     * single batched DexScreener /tokens call per 28 mints (free, no key).
     *
     * @param  array{new:array,final:array,migrated:array}  $tabs
     * @return array{new:array,final:array,migrated:array}
     */
    private function attachSocials(array $tabs): array
    {
        $mints = [];
        foreach ($tabs as $rows) {
            foreach ($rows as $r) {
                if (! empty($r['address'])) {
                    $mints[] = $r['address'];
                }
            }
        }

        $mints = array_values(array_unique($mints));
        if ($mints === []) {
            return $tabs;
        }

        $socials = [];
        foreach (array_chunk($mints, 28) as $chunk) {
            $json = $this->getJson('https://api.dexscreener.com/tokens/v1/solana/' . implode(',', $chunk));
            if (! is_array($json)) {
                continue;
            }

            foreach ($json as $p) {
                $addr = $p['baseToken']['address'] ?? '';
                if ($addr === '') {
                    continue;
                }

                $web = $socials[$addr]['web'] ?? '';
                $tw  = $socials[$addr]['twitter'] ?? '';
                $tg  = $socials[$addr]['telegram'] ?? '';

                foreach (($p['info']['websites'] ?? []) as $w) {
                    $u = (string) ($w['url'] ?? '');
                    if ($u !== '' && $web === '') {
                        $web = $u;
                    }
                }
                foreach (($p['info']['socials'] ?? []) as $s) {
                    $type = strtolower((string) ($s['type'] ?? ''));
                    $u    = (string) ($s['url'] ?? '');
                    if ($u === '') {
                        continue;
                    }
                    if ($type === 'twitter' && $tw === '') {
                        $tw = $u;
                    }
                    if ($type === 'telegram' && $tg === '') {
                        $tg = $u;
                    }
                }

                $socials[$addr] = ['web' => $web, 'twitter' => $tw, 'telegram' => $tg];
            }
        }

        foreach ($tabs as $k => $rows) {
            foreach ($rows as $i => $r) {
                $s = $socials[$r['address']] ?? [];
                $tabs[$k][$i]['twitter']  = $s['twitter'] ?? '';
                $tabs[$k][$i]['web']      = $s['web'] ?? '';
                $tabs[$k][$i]['telegram'] = $s['telegram'] ?? '';
            }
        }

        return $tabs;
    }

    /**
     * On-demand holder / safety report for a single mint (RugCheck, free, no key).
     * Cached 5 minutes per mint because holder sets move slowly.
     *
     * @return array<string,mixed>
     */
    public function tokenReport(string $mint): array
    {
        $mint = preg_replace('/[^A-Za-z0-9]/', '', $mint) ?? '';
        if ($mint === '') {
            return [];
        }

        return Cache::store('file')->remember('pulse_report_' . $mint, now()->addSeconds(300), function () use ($mint) {
            $j = $this->getJson('https://api.rugcheck.xyz/v1/tokens/' . $mint . '/report');
            if (! is_array($j)) {
                return [];
            }

            $top = is_array($j['topHolders'] ?? null) ? $j['topHolders'] : [];
            $pcts = [];
            foreach ($top as $h) {
                $pcts[] = (float) ($h['pct'] ?? 0);
            }
            rsort($pcts);

            $risks = [];
            foreach (($j['risks'] ?? []) as $r) {
                $risks[] = [
                    'name'  => (string) ($r['name'] ?? ''),
                    'level' => (string) ($r['level'] ?? ''),
                ];
            }

            return [
                'holders' => (int) ($j['totalHolders'] ?? 0),
                'top10'   => round(array_sum(array_slice($pcts, 0, 10)), 1),
                'top1'    => round($pcts[0] ?? 0, 1),
                'score'   => (int) ($j['score_normalised'] ?? ($j['score'] ?? 0)),
                'risks'   => $risks,
            ];
        });
    }

    private function progressFrom(float $fdvUsd, float $solUsd, string $dex): int
    {
        if ($dex !== 'pump-fun') {
            return 100;
        }
        if ($fdvUsd <= 0 || $solUsd <= 0) {
            return 0;
        }
        $fdvSol = $fdvUsd / $solUsd;
        $raised = sqrt(self::K * $fdvSol) - 30.0;

        return (int) max(0, min(100, round($raised / self::GRAD_SOL * 100)));
    }

    /** @param array<string,mixed> $r */
    private function map(array $r): array
    {
        $up   = $r['pct'] >= 0;
        $txns = $r['buys'] + $r['sells'];

        return [
            'sym'          => $r['sym'],
            'name'         => $r['name'],
            'chain'        => 'sol',
            'dex'          => $r['dex'],
            'venue'        => $r['venue'],
            'badge'        => $r['dex'] === 'pump-fun' ? 'pump' : substr($r['dex'], 0, 6),
            'age'          => $this->age($r['createdTs'] ?: null),
            'img'          => $r['img'],
            'native'       => 'SOL',
            'address'      => $r['mint'],
            'addressShort' => $this->short($r['mint']),
            'mc'           => $this->usd($r['mc']),
            'pct'          => ($up ? '+' : '') . number_format($r['pct'], 2) . '%',
            'up'           => $up,
            'vol'          => $this->usd($r['vol']),
            'liq'          => '',
            'txns'         => $this->compact($txns),
            'buys'         => $this->compact($r['buys']),
            'sells'        => $this->compact($r['sells']),
            'price'        => $this->price($r['price'] ?: null),
            'progress'     => (int) $r['progress'],
            'twitter'      => false,
            'web'          => false,
        ];
    }

    private function short(string $addr): string
    {
        if (strlen($addr) < 10) {
            return $addr;
        }

        return substr($addr, 0, 4) . '…' . substr($addr, -4);
    }

    private function price($v): string
    {
        if ($v === null || $v === '' || (float) $v == 0.0) {
            return '';
        }
        $v = (float) $v;
        if ($v >= 1000) return '$' . number_format($v, 0);
        if ($v >= 1) return '$' . number_format($v, 2);
        if ($v >= 0.01) return '$' . number_format($v, 4);

        return '$' . rtrim(rtrim(sprintf('%.8f', $v), '0'), '.');
    }

    private function age(?int $ts): string
    {
        if (! $ts) {
            return '';
        }
        $d = time() - $ts;
        if ($d < 60)    return max(1, $d) . 's';
        if ($d < 3600)  return floor($d / 60) . 'm';
        if ($d < 86400) return floor($d / 3600) . 'h';

        return floor($d / 86400) . 'd';
    }

    private function usd($v): string
    {
        $v = (float) $v;
        if ($v <= 0) {
            return '$0';
        }
        if ($v >= 1e6) return '$' . number_format($v / 1e6, 2) . 'M';
        if ($v >= 1e3) return '$' . number_format($v / 1e3, 2) . 'K';

        return '$' . number_format($v, 0);
    }

    private function compact($v): string
    {
        $v = (float) $v;
        if ($v >= 1e6) return number_format($v / 1e6, 2) . 'M';
        if ($v >= 1e3) return number_format($v / 1e3, 1) . 'K';

        return number_format($v, 0);
    }
}
