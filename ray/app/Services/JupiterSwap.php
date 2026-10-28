<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Server-side helpers around Jupiter's swap API. These only build the
 * transaction — signing/sending happens in the user's own browser wallet,
 * so no private keys ever touch the server.
 *
 *   GET  lite-api.jup.ag/swap/v1/quote
 *   POST https://lite-api.jup.ag/swap/v1/swap
 */
class JupiterSwap
{
    public const BASE = 'https://lite-api.jup.ag/swap/v1';
    public const SOL  = 'So11111111111111111111111111111111111111112';

    /** @return array<string,mixed>|null */
    public function quote(string $inputMint, string $outputMint, string $amount, int $slippageBps): ?array
    {
        $key = 'jup_q_' . md5("$inputMint|$outputMint|$amount|$slippageBps");

        return Cache::store('file')->remember($key, now()->addSeconds(12), function () use ($inputMint, $outputMint, $amount, $slippageBps) {
            try {
                $res = Http::connectTimeout(4)->timeout(12)->acceptJson()->get('https://lite-api.jup.ag/swap/v1/quote', [
                    'inputMint'   => $inputMint,
                    'outputMint'  => $outputMint,
                    'amount'      => $amount,
                    'slippageBps' => $slippageBps,
                    'swapMode'    => 'ExactIn',
                ]);

                return $res->ok() ? $res->json() : null;
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /** @return array<string,mixed>|null */
    public function swap(array $quoteResponse, string $userPublicKey): ?array
    {
        try {
            $res = Http::connectTimeout(4)->timeout(15)->acceptJson()->post('https://lite-api.jup.ag/swap/v1/swap', [
                'quoteResponse'    => $quoteResponse,
                'userPublicKey'    => $userPublicKey,
                'wrapAndUnwrapSol' => true,
                'dynamicComputeUnitLimit' => true,
                'prioritizationFeeLamports' => ['priorityLevelWithMaxLamports' => ['maxLamports' => 1_000_000, 'priorityLevel' => 'high']],
            ]);

            return $res->ok() ? $res->json() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
