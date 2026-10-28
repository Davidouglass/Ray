<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Live prediction markets from Polymarket's public Gamma API.
 *
 *   GET https://gamma-api.polymarket.com/markets?closed=false&active=true
 *       &order=volume24hr&ascending=false&limit=N
 *
 * outcomes / outcomePrices arrive as JSON-encoded strings.
 */
class PolymarketFeed
{
    private const BASE = 'https://gamma-api.polymarket.com/markets';

    /** @return array<int,array<string,mixed>> */
    public function markets(int $limit = 24): array
    {
        return Cache::store('file')->remember('pm_markets_v1', now()->addSeconds(60), function () use ($limit) {
            try {
                $res = Http::connectTimeout(4)->timeout(12)->acceptJson()->get(self::BASE, [
                    'closed'        => 'false',
                    'active'        => 'true',
                    'limit'         => min($limit, 100),
                    'order'         => 'volume24hr',
                    'ascending'     => 'false',
                ]);
                if (! $res->ok()) {
                    return Cache::store('file')->get('poly_lastgood', []);
                }
                $json = $res->json() ?? [];
            } catch (\Throwable $e) {
                return Cache::store('file')->get('pm_lastgood', []);
            }

            $out = [];
            foreach ($json as $m) {
                $outcomes = $this->arr($m['outcomes'] ?? null);
                $prices   = array_map('floatval', $this->arr($m['outcomePrices'] ?? null));
                if (count($outcomes) < 2) {
                    continue;
                }

                $out[] = [
                    'id'       => (string) ($m['id'] ?? ''),
                    'slug'     => (string) ($m['slug'] ?? ''),
                    'question' => (string) ($m['question'] ?? ''),
                    'image'    => (string) ($m['image'] ?? ($m['icon'] ?? '')),
                    'category' => (string) ($m['category'] ?? ''),
                    'outcomes' => $outcomes,
                    'prices'   => $prices,
                    'tokens'   => array_map('strval', $this->arr($m['clobTokenIds'] ?? null)),
                    'negRisk'  => (bool) ($m['negRisk'] ?? false),
                    'tick'     => (float) ($m['orderPriceMinTickSize'] ?? 0.01),
                    'minSize'  => (float) ($m['orderMinSize'] ?? 5),
                    'vol'      => (float) ($m['volume'] ?? 0),
                    'vol24'    => (float) ($m['volume24hr'] ?? 0),
                    'liq'      => (float) ($m['liquidity'] ?? 0),
                    'end'      => (string) ($m['endDate'] ?? ''),
                    'url'      => 'https://polymarket.com/event/' . ($m['slug'] ?? ''),
                ];
            }

            $out = array_slice($out, 0, 24);
            if (! empty($out)) {
                Cache::store('file')->forever('pm_lastgood', $out);
            }

            return $out;
        });
    }

    /** @return array<int,string> */
    private function arr($v): array
    {
        if (is_array($v)) {
            return array_map('strval', $v);
        }
        if (is_string($v)) {
            $j = json_decode($v, true);

            return is_array($j) ? array_map('strval', $j) : [];
        }

        return [];
    }
}
