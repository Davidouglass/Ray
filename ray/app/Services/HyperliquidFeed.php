<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Live perpetual-futures markets from Hyperliquid's public info API
 * (the same venue Axiom routes perps through).
 *
 *   POST https://api.hyperliquid.xyz/info  { "type": "metaAndAssetCtxs" }
 *   -> [ { universe:[{name,maxLeverage,szDecimals}] }, [ {markPx,funding,openInterest,dayNtlVlm,prevDayPx} ] ]
 */
class HyperliquidFeed
{
    private const INFO = 'https://api.hyperliquid.xyz/info';

    /** @return array<int,array<string,mixed>> */
    public function markets(int $limit = 60): array
    {
        return Cache::store('file')->remember('hl_perps_v1', now()->addSeconds(30), function () use ($limit) {
            $json = $this->post(['type' => 'metaAndAssetCtxs']);
            if (! is_array($json) || count($json) < 2) {
                return Cache::store('file')->get('hl_perps_lastgood', []);
            }

            $universe = $json[0]['universe'] ?? [];
            $ctxs     = $json[1] ?? [];
            $rows     = [];

            foreach ($universe as $i => $m) {
                $name = $m['name'] ?? '';
                $c    = $ctxs[$i] ?? [];
                if ($name === '' || $c === []) {
                    continue;
                }

                $mark = (float) ($c['markPx'] ?? 0);
                $prev = (float) ($c['prevDayPx'] ?? 0);
                $chg  = $prev > 0 ? (($mark - $prev) / $prev) * 100 : 0.0;

                $rows[] = [
                    'name'    => $name,
                    'index'   => $i,
                    'szDec'   => (int) ($m['szDecimals'] ?? 0),
                    'mark'    => $mark,
                    'prev'    => $prev,
                    'change'  => $chg,
                    'up'      => $chg >= 0,
                    'funding' => (float) ($c['funding'] ?? 0),
                    'oi'      => (float) ($c['openInterest'] ?? 0),
                    'volume'  => (float) ($c['dayNtlVlm'] ?? 0),
                    'lev'     => (int) ($m['maxLeverage'] ?? 0),
                ];
            }

            usort($rows, fn ($a, $b) => $b['volume'] <=> $a['volume']);
            $rows = array_slice($rows, 0, $limit);

            if (! empty($rows)) {
                Cache::store('file')->forever('hl_perps_lastgood', $rows);
            }

            return $rows;
        });
    }

    /** @return array<string,mixed> summary stats for the header */
    public function summary(): array
    {
        $m = $this->markets(200);
        $vol = array_sum(array_column($m, 'volume'));
        $oi  = array_sum(array_map(fn ($r) => $r['oi'] * $r['mark'], $m));

        return [
            'markets' => count($m),
            'volume'  => $vol,
            'oi'      => $oi,
        ];
    }

    /** @return array<string,mixed>|null */
    private function post(array $body): ?array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $res = Http::connectTimeout(4)->timeout(12)->acceptJson()->post(self::INFO, $body);
                if ($res->status() === 429) {
                    usleep(1_000_000);
                    continue;
                }

                return $res->ok() ? $res->json() : null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
