<?php

namespace App\Http\Controllers;

use App\Services\PolymarketFeed;

class PredictionsController extends Controller
{
    public function index(PolymarketFeed $pm)
    {
        return view('predictions', [
            'title'   => 'Predictions',
            'active'  => 'predictions',
            'markets' => $pm->markets(24),
        ]);
    }
}
