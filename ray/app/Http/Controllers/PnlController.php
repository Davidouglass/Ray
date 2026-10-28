<?php

namespace App\Http\Controllers;

use App\Services\SolanaWallet;

class PnlController extends Controller
{
    public function index(SolanaWallet $sol)
    {
        $user    = request()->user();
        $wallets = $user->connectedWallets()->orderByDesc('is_primary')->latest()->get();

        $positions = [];
        $totalUsd  = 0.0;
        $pnl24     = 0.0;

        foreach ($wallets as $w) {
            $pf = $sol->portfolio($w->address);
            if (! empty($pf['error'])) {
                continue;
            }
            $totalUsd += (float) ($pf['total'] ?? 0);

            foreach (($pf['tokens'] ?? []) as $t) {
                $usd    = (float) ($t['usd'] ?? 0);
                $change = (float) ($t['change'] ?? 0);
                $prev   = $change > -100 ? $usd / (1 + $change / 100) : $usd;
                $pnl    = $usd - $prev;
                $pnl24 += $pnl;
                $positions[] = [
                    'wallet' => $w->label ?: substr($w->address, 0, 6),
                    'symbol' => $t['symbol'] ?? '?',
                    'name'   => $t['name'] ?? '',
                    'img'    => $t['img'] ?? '',
                    'amount' => (float) ($t['amount'] ?? 0),
                    'price'  => (float) ($t['price'] ?? 0),
                    'usd'    => $usd,
                    'change' => $change,
                    'pnl'    => $pnl,
                    'mint'   => $t['mint'] ?? '',
                ];
            }
        }

        usort($positions, fn ($a, $b) => $b['usd'] <=> $a['usd']);

        return view('pnl', [
            'title'     => 'PnL',
            'active'    => 'pnl',
            'positions' => $positions,
            'totalUsd'  => $totalUsd,
            'pnl24'     => $pnl24,
            'walletCount' => $wallets->count(),
        ]);
    }
}
