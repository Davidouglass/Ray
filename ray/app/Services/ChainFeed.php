<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Per-chain trending feed built from the free GeckoTerminal API.
 *
 * GeckoTerminal exposes a "trending pools" list per network, which gives us
 * real rows for chains that the boost-based DiscoverFeed rarely covers
 * (Ethereum, Base, Ink, ...). Results are mapped into the same row shape the
 * Discover table expects so the chain page can reuse the markup.
 */
class ChainFeed
{
    private const GT = 'https://api.geckoterminal.com/api/v2';
    private const PM = 'https://axiom-assets-v2.axiom-cdn.io/images/protocol-marks/v1';

    /** our chain key => GeckoTerminal network id */
    private const NETWORK = [
        'sol'       => 'solana',
        'eth'       => 'eth',
        'bnb'       => 'bsc',
        'base'      => 'base',
        'ink'       => 'ink',
        'hype'      => 'hyperevm',
        'robinhood' => 'robinhood',
        'arc'       => 'arc',
    ];

    private const NATIVE = [
        'sol' => 'SOL', 'bnb' => 'BNB', 'eth' => 'ETH', 'base' => 'ETH',
        'ink' => 'ETH', 'hype' => 'HYPE',
    ];

    /** @return array<int,array<string,mixed>> */
    public function trending(string $chain, int $limit = 30): array
    {
        $network = self::NETWORK[$chain] ?? null;
        if ($network === null) {
            return [];
        }

        $store = Cache::store('file');
        $freshKey = "chain_feed_{$chain}_v2";
        $staleKey = "chain_feed_{$chain}_stale";

        $fresh = $store->get($freshKey);
        if (is_array($fresh) && ! empty($fresh)) {
            return $fresh;
        }

        $rows = $this->build($chain, $network, $limit);

        if (! empty($rows)) {
            $store->put($freshKey, $rows, now()->addSeconds(120));
            $store->put($staleKey, $rows, now()->addHours(6));
            return $rows;
        }

        // Transient failure (e.g. GeckoTerminal 429): serve the last good data.
        $stale = $store->get($staleKey);
        if (is_array($stale) && ! empty($stale)) {
            return $stale;
        }

        return [];
    }

    private function build(string $chain, string $network, int $limit): array
    {
        try {
            $res = Http::connectTimeout(4)->timeout(8)->acceptJson()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; AxiomClone/1.0)'])
                ->get(self::GT . "/networks/{$network}/trending_pools", ['page' => 1]);

            if ($res->status() === 429) {
                usleep(600000);
                $res = Http::connectTimeout(4)->timeout(8)->acceptJson()
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; AxiomClone/1.0)'])
                    ->get(self::GT . "/networks/{$network}/trending_pools", ['page' => 1]);
            }

            if (! $res->ok()) {
                return [];
            }

            $pools = $res->json('data') ?? [];
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];
        foreach (array_slice($pools, 0, $limit) as $p) {
            $row = $this->map($chain, $p);
            if ($row !== null) {
                $out[] = $row;
            }
        }

        return $out;
    }

    private function map(string $chain, array $p): ?array
    {
        $a = $p['attributes'] ?? [];
        $name = (string) ($a['name'] ?? '');
        if ($name === '') {
            return null;
        }

        $baseId = $p['relationships']['base_token']['data']['id'] ?? '';
        $address = $baseId === '' ? '' : substr($baseId, strpos($baseId, '_') + 1);
        $sym = trim(explode('/', $name)[0]);
        $dex = (string) ($p['relationships']['dex']['data']['id'] ?? '');

        $buys  = (int) ($a['transactions']['h24']['buys'] ?? 0);
        $sells = (int) ($a['transactions']['h24']['sells'] ?? 0);
        $pct   = (float) ($a['price_change_percentage']['h24'] ?? 0);
        $up    = $pct >= 0;

        return [
            'sym'      => $sym,
            'name'     => $name,
            'address'  => $chain === 'sol' ? $address : strtolower($address),
            'chain'    => $chain,
            'dex'      => $dex,
            'dexArt'   => $chain === 'sol'
                ? self::PM . '/pump.webp'
                : self::PM . '/evm-uniswap-purple.webp',
            'tax'      => '—',
            'age'      => $this->age(isset($a['pool_created_at']) ? strtotime($a['pool_created_at']) : null),
            'views'    => '—',
            'img'      => '',
            'quote'    => null,
            'quoteSym' => null,
            'quoteArt' => null,
            'native'   => self::NATIVE[$chain] ?? 'ETH',
            'mc'       => $this->usd($a['market_cap_usd'] ?? $a['fdv_usd'] ?? null),
            'pct'      => ($up ? '+' : '') . number_format($pct, 2) . '%',
            'up'       => $up,
            'liq'      => $this->usd($a['reserve_in_usd'] ?? null),
            'vol'      => $this->usd($a['volume_usd']['h24'] ?? null),
            'txns'     => $this->compact($buys + $sells),
            'buys'     => $this->compact($buys),
            'sells'    => $this->compact($sells),
            'paid'     => false,
            'safe'     => false,
            'quill'    => false,
            'globe'    => false,
            'pump'     => $chain === 'sol',
            'rocket'   => null,
            'uniswap'  => str_contains($dex, 'uniswap'),
            'imgCount' => null,
            'price'    => $this->price($a['base_token_price_usd'] ?? null),
            'spark'    => $this->spark($a['price_change_percentage'] ?? []),
            'cols'     => $this->placeholderCols(),
        ];
    }

    private function placeholderCols(): array
    {
        return [
            [['ri-user-star-line', '—', 'sec'], ['ri-restaurant-2-line', '—', 'sec'], ['ri-crosshair-2-line', '—', 'sec']],
            [['ri-ghost-line', '—', 'sec'], ['ri-stack-line', '—', 'sec']],
            [['ri-user-line', '—', 'sec'], ['ri-vip-crown-line', '—', 'sec']],
            [['ri-money-dollar-circle-line', 'Unpaid', 'red'], ['ri-shield-line', '—', 'sec']],
        ];
    }

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
