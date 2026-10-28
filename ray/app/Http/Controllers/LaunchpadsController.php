<?php

namespace App\Http\Controllers;

use App\Services\PulseFeed;

class LaunchpadsController extends Controller
{
    public function index(PulseFeed $pulse)
    {
        $tabs = $pulse->all(16);

        return view('launchpads', [
            'title'  => 'Launchpads',
            'active' => 'launchpads',
            'tabs'   => $tabs,
        ]);
    }
}
