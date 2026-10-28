<?php

namespace App\Http\Controllers;

use App\Models\ConnectedWallet;
use App\Services\SolanaWallet;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(SolanaWallet $sol)
    {
        $user     = request()->user();
        $wallets  = $user->connectedWallets()->orderByDesc('is_primary')->latest()->get();
        $primary  = $wallets->firstWhere('is_primary', true) ?? $wallets->first();

        $portfolio = $primary ? $sol->portfolio($primary->address) : null;
        $activity  = $primary ? $sol->activity($primary->address) : [];

        return view('portfolio', [
            'title'      => 'Portfolio',
            'active'     => 'portfolio',
            'wallets'    => $wallets,
            'primary'    => $primary,
            'portfolio'  => $portfolio,
            'activity'   => $activity,
            'watchlist'  => request()->user()->watchlist()->latest()->get(),
        ]);
    }

    public function connect(\Illuminate\Http\Request $request, SolanaWallet $sol)
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:120'],
            'label'   => ['nullable', 'string', 'max:80'],
        ]);

        if (! $sol->isValidAddress($data['address'])) {
            return back()->withErrors(['address' => 'That is not a valid Solana wallet address.']);
        }

        $isFirst = $request->user()->connectedWallets()->count() === 0;

        $request->user()->connectedWallets()->firstOrCreate(
            ['chain' => 'sol', 'address' => $data['address']],
            ['label' => $data['label'] ?? null, 'is_primary' => $isFirst]
        );

        return redirect('/portfolio')->with('ok', 'Wallet connected.');
    }

    public function primary(\Illuminate\Http\Request $request, \App\Models\ConnectedWallet $wallet)
    {
        abort_unless($wallet->user_id === $request->user()->id, 403);
        $request->user()->connectedWallets()->update(['is_primary' => false]);
        $wallet->update(['is_primary' => true]);

        return redirect('/portfolio')->with('ok', 'Primary wallet updated.');
    }

    public function disconnect(\Illuminate\Http\Request $request, \App\Models\ConnectedWallet $wallet)
    {
        abort_unless($wallet->user_id === $request->user()->id, 403);
        $wallet->delete();

        return redirect('/portfolio')->with('ok', 'Wallet disconnected.');
    }

    public function watchStore(\Illuminate\Http\Request $request, SolanaWallet $sol)
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:120'],
            'symbol'  => ['nullable', 'string', 'max:40'],
        ]);

        if (! $sol->isValidAddress($data['address'])) {
            return back()->withErrors(['address' => 'Enter a valid Solana token address to watch.']);
        }

        $request->user()->watchlist()->firstOrCreate(
            ['chain' => 'sol', 'address' => $data['address']],
            ['symbol' => $data['symbol'] ?? null]
        );

        return redirect('/portfolio')->with('ok', 'Added to watchlist.');
    }

    public function watchDestroy(\Illuminate\Http\Request $request, \App\Models\WatchlistItem $item)
    {
        abort_unless($item->user_id === $request->user()->id, 403);
        $item->delete();

        return redirect('/portfolio')->with('ok', 'Removed from watchlist.');
    }
}
