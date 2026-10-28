<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $tab = (string) $request->query('tab', 'presets');
        $allowed = ['presets', 'appearance', 'notifications', 'data', 'support'];
        if (! in_array($tab, $allowed, true)) {
            $tab = 'presets';
        }

        return view('settings', [
            'title'  => 'Settings',
            'active' => 'settings',
            'tab'    => $tab,
        ]);
    }
}
