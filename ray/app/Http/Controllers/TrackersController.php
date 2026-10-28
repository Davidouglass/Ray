<?php

namespace App\Http\Controllers;

use App\Models\TrackedWallet;
use App\Services\SolanaWallet;
use Illuminate\Http\Request;

class TrackersController extends Controller
{
    public function index(SolanaWallet $sol)
    {
        $wallets = request()->user()->trackedWallets()->latest()->get();

        $rows = $wallets->map(function (TrackedWallet $w) use ($sol) {
            $pf = $sol->portfolio($w->address);

            return [
                'id'      => $w->id,
                'address' => $w->address,
                'label'   => $w->label,
                'chain'   => $w->chain,
                'sol'     => $pf['sol'] ?? 0,
                'total'   => $pf['total'] ?? 0,
                'tokens'  => count($pf['tokens'] ?? []),
                'error'   => $pf['error'] ?? null,
            ];
        });

        return view('trackers', [
            'title'  => 'Trackers',
            'active' => 'trackers',
            'rows'   => $rows,
        ]);
    }

    public function store(Request $request, SolanaWallet $sol)
    {
        $data = $request->validate([
            'address' => ['required', 'string', 'max:120'],
            'label'   => ['nullable', 'string', 'max:80'],
        ]);

        if (! $sol->isValidAddress($data['address'])) {
            return back()->withErrors(['address' => 'That is not a valid Solana wallet address.']);
        }

        $request->user()->trackedWallets()->firstOrCreate(
            ['chain' => 'sol', 'address' => $data['address']],
            ['label' => $data['label'] ?? null]
        );

        return redirect('/trackers')->with('ok', 'Wallet added to your tracker.');
    }

    public function destroy(Request $request, TrackedWallet $wallet)
    {
        abort_unless($wallet->user_id === $request->user()->id, 403);
        $wallet->delete();

        return redirect('/trackers')->with('ok', 'Wallet removed.');
    }
}
