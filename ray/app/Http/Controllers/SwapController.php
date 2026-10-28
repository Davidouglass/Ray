<?php

namespace App\Http\Controllers;

use App\Services\JupiterSwap;
use App\Services\SolanaWallet;
use Illuminate\Http\Request;

class SwapController extends Controller
{
    private const SOL = 'So11111111111111111111111111111111111111112';

    /** GET /api/swap/quote?inputMint=&outputMint=&amount=&slippageBps= */
    public function quote(Request $request)
    {
        $in       = (string) $request->query('inputMint', '');
        $out      = (string) $request->query('outputMint', '');
        $amount   = preg_replace('/[^0-9]/', '', (string) $request->query('amount', '')) ?? '';
        $slippage = max(1, min(5000, (int) $request->query('slippageBps', 100)));

        if (! $this->mint($in) || ! $this->mint($out)) {
            return response()->json(['error' => 'Invalid token address'], 422);
        }
        if ($amount === '' || (int) $amount <= 0) {
            return response()->json(['error' => 'Enter an amount'], 422);
        }

        $quote = app(JupiterSwap::class)->quote($in, $out, $amount, $slippage);
        if (! $quote) {
            return response()->json(['error' => 'No route found for this trade'], 422);
        }

        return response()->json($quote);
    }

    /** POST /api/swap/build  { quoteResponse, userPublicKey } */
    public function build(Request $request)
    {
        $data = $request->validate([
            'quoteResponse' => ['required', 'array'],
            'userPublicKey' => ['required', 'string'],
        ]);

        if (! $this->mint($data['userPublicKey'])) {
            return response()->json(['error' => 'Invalid wallet address'], 422);
        }

        $res = app(JupiterSwap::class)->swap($data['quoteResponse'], $data['userPublicKey']);
        if (! $res || empty($res['swapTransaction'])) {
            return response()->json(['error' => 'Could not build the transaction'], 422);
        }

        return response()->json(['swapTransaction' => $res['swapTransaction']]);
    }

    /** GET /api/swap/meta?mint=&owner= -> decimals + owner balance */
    public function meta(Request $request, SolanaWallet $sol)
    {
        $mint  = (string) $request->query('mint', '');
        $owner = (string) $request->query('owner', '');

        if (! $this->mint($mint)) {
            return response()->json(['error' => 'Invalid token address'], 422);
        }

        return response()->json([
            'mint'     => $mint,
            'decimals' => $sol->mintDecimals($mint),
            'balance'  => $this->mint($owner) ? $sol->balance($owner, $mint) : 0.0,
            'sol'      => $mint === self::SOL,
        ]);
    }

    private function mint(string $a): bool
    {
        return (bool) preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $a);
    }
}
