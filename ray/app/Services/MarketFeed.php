<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Native-asset spot prices for the status bar (SOL / BNB / HYPE).
 * Source: CoinGecko free simple-price endpoint. Falls back to the
 * last-known values when the API is unreachable or rate-limited.
 */
class MarketFeed
{
    private const FALLBACK = ['SOL' => '$109.33', 'BNB' => '$743', 'HYPE' => '$84.22'];

    public function spot(): array
    {
        return Cache::store('file')->remember('spot_prices_v1', now()->addSeconds(60), function () {
            try {
                $res = Http::connectTimeout(4)->timeout(8)->acceptJson()
                    ->get('https://api.coingecko.com/api/v3/simple/price', [
                        'ids'           => 'solana,binancecoin,hyperliquid',
                        'vs_currencies' => 'usd',
                    ]);

                if (! $res->ok()) {
                    return self::FALLBACK;
                }

                $json = $res->json();

                return [
                    'SOL'  => $this->fmt($json['solana']['usd'] ?? null, self::FALLBACK['SOL']),
                    'BNB'  => $this->fmt($json['binancecoin']['usd'] ?? null, self::FALLBACK['BNB']),
                    'HYPE' => $this->fmt($json['hyperliquid']['usd'] ?? null, self::FALLBACK['HYPE']),
                ];
            } catch (\Throwable $e) {
                return self::FALLBACK;
            }
        });
    }

    private function fmt($v, string $fallback): string
    {
        if (! is_numeric($v)) {
            return $fallback;
        }
        $v = (float) $v;
        if ($v >= 1000) {
            return '$' . number_format($v, 0);
        }

        return '$' . rtrim(rtrim(number_format($v, 2), '0'), '.');
    }
}
