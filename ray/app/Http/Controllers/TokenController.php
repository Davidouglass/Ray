<?php

namespace App\Http\Controllers;

use App\Services\TokenService;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function show(TokenService $tokens, string $address)
    {
        $detail = $tokens->detail($address);

        return view('token', [
            'title'  => ($detail['symbol'] ?? 'Token'),
            'active' => 'pulse',
            'detail' => $detail,
        ]);
    }

    public function candles(Request $request, TokenService $tokens, string $address)
    {
        $timeframe = (string) $request->query('tf', 'hour');
        $aggregate = (int) $request->query('agg', 1);
        $limit     = (int) $request->query('limit', 168);
        $pool      = (string) $request->query('pool', '');

        return response()->json($tokens->candles($address, $timeframe, $aggregate, $limit, $pool));
    }

    public function tradingview(Request $request, TokenService $tokens, string $address)
    {
        return response()->json($tokens->tradingView($address, (string) $request->query('symbol', '')));
    }
}
