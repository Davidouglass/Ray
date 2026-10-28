<?php

namespace App\Http\Controllers;

use App\Services\RewardsService;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    public function index(Request $request, RewardsService $rewards)
    {
        $user = $request->user();
        $code = $rewards->ensureCode($user);
        $total = $rewards->total($user);

        return view('rewards', [
            'title'      => 'Rewards',
            'active'     => 'rewards',
            'total'      => $total,
            'tier'       => $rewards->tier($total),
            'code'       => $code,
            'referrals'  => $rewards->referrals($user),
            'history'    => $rewards->history($user),
            'claimedToday' => $rewards->claimedToday($user),
            'refLink'    => url('/signup?ref=' . $code),
        ]);
    }

    public function claim(Request $request, RewardsService $rewards)
    {
        $res = $rewards->claimDaily($request->user());

        return redirect('/rewards')->with(
            'ok',
            $res['claimed'] ? "Claimed {$res['points']} points for today!" : 'Already claimed today — come back tomorrow.'
        );
    }
}
