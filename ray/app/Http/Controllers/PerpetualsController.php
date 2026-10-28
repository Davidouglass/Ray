<?php

namespace App\Http\Controllers;

use App\Services\HyperliquidFeed;

class PerpetualsController extends Controller
{
    public function index(HyperliquidFeed $hl)
    {
        return view('perpetuals', [
            'title'   => 'Perpetuals',
            'active'  => 'perpetuals',
            'markets' => $hl->markets(60),
            'summary' => $hl->summary(),
        ]);
    }
}
