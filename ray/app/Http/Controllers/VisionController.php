<?php

namespace App\Http\Controllers;

use App\Services\DiscoverFeed;
use App\Services\PulseFeed;
use App\Services\SolanaWallet;
use App\Services\TokenService;
use Illuminate\Http\Request;

class VisionController extends Controller
{
    public function index(DiscoverFeed $discover, PulseFeed $pulse)
    {
        return view('vision', [
            'title'    => 'Vision',
            'active'   => 'vision',
            'trending' => $discover->trending(15),
            'pools'    => $pulse->newPairs(12),
        ]);
    }

    /** Token scanner endpoint used by the Vision "Scan" tool. */
    public function scan(Request $request, TokenService $tokens, SolanaWallet $sol)
    {
        $address = (string) $request->query('address', '');
        $address = preg_replace('/[^A-Za-z0-9]/', '', $address) ?? '';

        if (strlen($address) >= 32 && strlen($address) <= 44 && $sol->isValidAddress($address)) {
            return response()->json($tokens->detail($address));
        }

        return response()->json(['error' => 'Enter a valid Solana token address'], 422);
    }
}
