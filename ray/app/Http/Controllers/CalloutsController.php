<?php

namespace App\Http\Controllers;

use App\Models\WatchlistItem;
use App\Services\DiscoverFeed;
use App\Services\SolanaWallet;
use Illuminate\Http\Request;

class CalloutsController extends Controller
{
    public function index(SolanaWallet $sol, DiscoverFeed $discover)
    {
        $items = request()->user()->watchlist()->latest()->get();

        $mints  = $items->pluck('address')->all();
        $prices = $mints ? $sol->prices($mints) : [];

        $callouts = $items->map(function (WatchlistItem $it) use ($prices) {
            $meta = $prices[$it->address] ?? [];

            return [
                'id'     => $it->id,
                'symbol' => $meta['symbol'] ?? ($it->symbol ?: substr($it->address, 0, 4)),
                'name'   => $meta['name'] ?? '',
                'img'    => $meta['image'] ?? '',
                'price'  => $meta['price'] ?? 0,
                'change' => $meta['change'] ?? 0,
                'address' => $it->address,
            ];
        });

        return view('callouts', [
            'title'    => 'Callouts',
            'active'   => 'callouts',
            'callouts' => $callouts,
            'community' => $discover->trending(12),
        ]);
    }

    public function store(Request $request, SolanaWallet $sol)
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:120'],
            'symbol'  => ['nullable', 'string', 'max:40'],
        ]);

        if (! $sol->isValidAddress($data['address'])) {
            return back()->withErrors(['address' => 'Enter a valid Solana token address to call out.']);
        }

        $request->user()->watchlist()->firstOrCreate(
            ['chain' => 'sol', 'address' => $data['address']],
            ['symbol' => $data['symbol'] ?? null]
        );

        return redirect('/callouts')->with('ok', 'Callout posted.');
    }
}
