<?php

namespace App\Http\Controllers;

use App\Services\DiscoverFeed;
use App\Services\MarketFeed;

class MarketsController extends Controller
{
    public function index(DiscoverFeed $discover, MarketFeed $market)
    {
        return view('markets', [
            'title'    => 'Markets',
            'active'   => 'markets',
            'spot'     => $market->spot(),
            'trending' => $discover->trending(24),
        ]);
    }
}
