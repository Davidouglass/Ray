<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Builds Discover rows from the free DexScreener API.
 *
 * Trending source = DexScreener token-boosts (latest + top), then the
 * /latest/dex/tokens endpoint is used to resolve full pair data.
 *
 * Fields that only Axiom can provide (views, tax, holder distribution,
 * paid/safe flags) are emitted as placeholders ("—" / Unpaid).
 */
class DiscoverFeed
{
    private const ASSET = 'https://axiom-assets-v2.axiom-cdn.io/images';
    private const PM    = 'https://axiom-assets-v2.axiom-cdn.io/images/protocol-marks/v1';

    /** DexScreener chainId => our chain key (only chains we have art for). */
    private const CHAIN_MAP = [
        'solana'   => 'sol',
        'bsc'      => 'bnb',
        'ethereum' => 'eth',
        'base'     => 'base',
    ];

    private const CHAIN_ART = [
        'sol'  => self::ASSET . '/sol-fill.svg',
        'bnb'  => self::ASSET . '/bnb-fill.svg',
        'eth'  => self::ASSET . '/eth-fill.svg',
        'base' => self::ASSET . '/base.svg',
    ];

    private const NATIVE = ['sol' => 'SOL', 'bnb' => 'BNB', 'eth' => 'ETH', 'base' => 'ETH'];

    public function trending(int $limit = 25): array
    {
        return Cache::store('file')->remember(
            'discover_feed_v1',
            now()->addSeconds(30),
            fn () => $this->build($limit)
        );
    }

    private function build(int $limit): array
    {
        $pairs = $this->fetchPairs();
        if (empty($pairs)) {
            return [];
        }

        // Keep the best (highest-liquidity) pair per base token.
        $best = [];
        foreach ($pairs as $p) {
            $chain = self::CHAIN_MAP[$p['chainId'] ?? ''] ?? null;
            $token = strtolower($p['baseToken']['address'] ?? '');
            if ($chain === null || $token === '') {
                continue;
            }
            $liq = (float) ($p['liquidity']['usd'] ?? 0);
            if (!isset($best[$token]) || $liq > $best[$token]['_liq']) {
                $p['_liq']   = $liq;
                $p['_chain'] = $chain;
                $best[$token] = $p;
            }
        }

        usort($best, fn ($a, $b) => $b['_liq'] <=> $a['_liq']);
        $best = array_slice($best, 0, $limit);

        return array_map(fn ($p) => $this->map($p), $best);
    }

    /** @return array<int,string> unique boosted token addresses (latest first) */
    private function boostedAddresses(): array
    {
        $addrs = [];
        foreach (['latest', 'top'] as $which) {
            try {
                $res = Http::connectTimeout(4)->timeout(8)->acceptJson()
                    ->get("https://api.dexscreener.com/token-boosts/{$which}/v1");
                foreach ($res->json() ?? [] as $b) {
                    $a = $b['tokenAddress'] ?? null;
                    if ($a) {
                        $addrs[strtolower($a)] = $a;
                    }
                }
            } catch (\Throwable $e) {
                // ignore, fall through to the other feed
            }
        }

        return array_values($addrs);
    }

    /** @return array<int,array<string,mixed>> */
    private function fetchPairs(): array
    {
        $addrs = array_slice($this->boostedAddresses(), 0, 30);
        if (empty($addrs)) {
            return [];
        }

        try {
            $res = Http::connectTimeout(4)->timeout(8)->acceptJson()
                ->get('https://api.dexscreener.com/latest/dex/tokens/' . implode(',', $addrs));

            return $res->ok() ? ($res->json('pairs') ?? []) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function map(array $p): array
    {
        $chain  = $p['_chain'];
        $info   = $p['info'] ?? [];
        $buys   = (int) ($p['txns']['h24']['buys'] ?? 0);
        $sells  = (int) ($p['txns']['h24']['sells'] ?? 0);
        $pct    = isset($p['priceChange']['h24']) ? (float) $p['priceChange']['h24'] : 0.0;
        $up     = $pct >= 0;
        $dex    = (string) ($p['dexId'] ?? '');
        $social = collect($info['socials'] ?? []);

        return [
            'sym'      => $p['baseToken']['symbol'] ?? '?',
            'name'     => $p['baseToken']['name'] ?? '',
            'address'  => $chain === 'sol' ? (string) ($p['baseToken']['address'] ?? '') : strtolower((string) ($p['baseToken']['address'] ?? '')),
            'chain'    => $chain,
            'dex'      => $dex,
            'dexArt'   => $chain === 'sol'
                ? self::PM . '/pump.webp'
                : self::PM . '/evm-uniswap-purple.webp',
            'tax'      => '—',
            'age'      => $this->age(isset($p['pairCreatedAt']) ? (int) ($p['pairCreatedAt'] / 1000) : null),
            'views'    => '—',
            'img'      => $info['imageUrl'] ?? self::CHAIN_ART[$chain],
            'quote'    => null,
            'quoteSym' => null,
            'quoteArt' => null,
            'native'   => self::NATIVE[$chain],
            'mc'       => $this->usd($p['marketCap'] ?? $p['fdv'] ?? null),
            'pct'      => ($up ? '+' : '') . number_format($pct, 2) . '%',
            'up'       => $up,
            'liq'      => $this->usd($p['liquidity']['usd'] ?? null),
            'vol'      => $this->usd($p['volume']['h24'] ?? null),
            'txns'     => $this->compact($buys + $sells),
            'buys'     => $this->compact($buys),
            'sells'    => $this->compact($sells),
            'paid'     => (($p['boosts']['active'] ?? 0) > 0),
            'safe'     => false,
            'quill'    => $social->contains(fn ($s) => ($s['type'] ?? '') === 'twitter'),
            'globe'    => ! empty($info['websites']),
            'pump'     => $chain === 'sol',
            'rocket'   => null,
            'uniswap'  => str_contains($dex, 'uniswap'),
            'imgCount' => null,
            'price'    => $this->price($p['priceUsd'] ?? null),
            'spark'    => $this->spark($p['priceChange'] ?? []),
            'cols'     => $this->placeholderCols(),
        ];
    }

    /** Token Info is Axiom-proprietary; emit neutral placeholders. */
    private function placeholderCols(): array
    {
        return [
            [['ri-user-star-line', '—', 'sec'], ['ri-restaurant-2-line', '—', 'sec'], ['ri-crosshair-2-line', '—', 'sec']],
            [['ri-ghost-line', '—', 'sec'], ['ri-stack-line', '—', 'sec']],
            [['ri-user-line', '—', 'sec'], ['ri-vip-crown-line', '—', 'sec']],
            [['ri-money-dollar-circle-line', 'Unpaid', 'red'], ['ri-shield-line', '—', 'sec']],
        ];
    }

    /** Build a 5-point sparkline from DexScreener's m5/h1/h6/h24 change set. */
    private function spark(array $pc): array
    {
        $denom = function ($x) {
            $d = 1 + ((float) $x / 100);
            return abs($d) < 1e-6 ? 1.0 : $d;
        };

        $now    = 100.0;
        $series = [
            $now / $denom($pc['h24'] ?? 0),
            $now / $denom($pc['h6'] ?? 0),
            $now / $denom($pc['h1'] ?? 0),
            $now / $denom($pc['m5'] ?? 0),
            $now,
        ];

        $min = min($series);
        $max = max($series);
        $rng = max($max - $min, 1e-9);

        $pts = [];
        $n   = count($series);
        foreach ($series as $i => $c) {
            $x     = 1 + ($i / ($n - 1)) * 98;
            $y     = 38 - (($c - $min) / $rng) * 34;
            $pts[] = round($x, 1) . ',' . round($y, 1);
        }

        $line = 'M' . implode(' L', $pts);
        $up   = $series[$n - 1] >= $series[0];

        return [
            'line'   => $line,
            'area'   => $line . ' L99,39.2 L1,39.2 Z',
            'up'     => $up,
            'stroke' => $up ? '#2FE3AC' : '#EC397A',
            'fill'   => $up ? '#2FE3AC1A' : '#EC397A1A',
        ];
    }

    private function price($v): string
    {
        if ($v === null || $v === '') {
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
            return '—';
        }
        $d = time() - $ts;
        if ($d < 60) return $d . 's';
        if ($d < 3600) return intdiv($d, 60) . 'm';
        if ($d < 86400) return intdiv($d, 3600) . 'h';
        if ($d < 2592000) return intdiv($d, 86400) . 'd';

        return intdiv($d, 2592000) . 'mo';
    }

    private function usd($v): string
    {
        if ($v === null || $v === '') {
            return '—';
        }
        $v = (float) $v;
        if ($v >= 1e9) return '$' . number_format($v / 1e9, 2) . 'B';
        if ($v >= 1e6) return '$' . number_format($v / 1e6, 2) . 'M';
        if ($v >= 1e3) return '$' . number_format($v / 1e3, 1) . 'K';

        return '$' . number_format($v, 0);
    }

    private function compact($v): string
    {
        $v = (float) $v;
        if ($v >= 1e6) return number_format($v / 1e6, 2) . 'M';
        if ($v >= 1e3) return number_format($v / 1e3, 2) . 'K';

        return number_format($v, 0);
    }
}
