<?php

namespace App\Http\Controllers;

use App\Models\TrackedWallet;
use App\Services\SolanaWallet;
use Illuminate\Http\Request;

class FriendsController extends Controller
{
    public function index(SolanaWallet $sol)
    {
        $friends = request()->user()->trackedWallets()->latest()->get()->map(function (TrackedWallet $w) use ($sol) {
            $pf = $sol->portfolio($w->address);

            return [
                'id'      => $w->id,
                'address' => $w->address,
                'label'   => $w->label,
                'sol'     => $pf['sol'] ?? 0,
                'total'   => $pf['total'] ?? 0,
                'tokens'  => $pf['tokens'] ?? [],
                'error'   => $pf['error'] ?? null,
            ];
        });

        return view('friends', [
            'title'   => 'Friends',
            'active'  => 'friends',
            'friends' => $friends,
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

        return redirect('/friends')->with('ok', 'Friend added.');
    }

    public function destroy(Request $request, TrackedWallet $wallet)
    {
        abort_unless($wallet->user_id === $request->user()->id, 403);
        $wallet->delete();

        return redirect('/friends')->with('ok', 'Friend removed.');
    }
}
