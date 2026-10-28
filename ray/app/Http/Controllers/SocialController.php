<?php

namespace App\Http\Controllers;

use App\Services\PulseFeed;

class SocialController extends Controller
{
    public function index(PulseFeed $pulse)
    {
        $tabs = $pulse->all(18);

        $seen  = [];
        $feed  = [];
        foreach (['new', 'final', 'migrated'] as $k) {
            foreach (($tabs[$k] ?? []) as $r) {
                $addr = $r['address'] ?? '';
                if ($addr === '' || isset($seen[$addr])) {
                    continue;
                }
                $seen[$addr] = true;

                $feed[] = [
                    'sym'     => $r['sym'] ?? '?',
                    'name'    => $r['name'] ?? '',
                    'img'     => $r['img'] ?? '',
                    'address' => $addr,
                    'pct'     => $r['pct'] ?? '',
                    'up'      => (bool) ($r['up'] ?? true),
                    'price'   => $r['price'] ?? '',
                    'mc'      => $r['mc'] ?? '',
                    'venue'   => $r['venue'] ?? '',
                    'twitter' => is_string($r['twitter'] ?? false) ? $r['twitter'] : '',
                    'web'     => is_string($r['web'] ?? false) ? $r['web'] : '',
                    'telegram' => is_string($r['telegram'] ?? false) ? $r['telegram'] : '',
                ];
            }
        }

        $withSocials = array_values(array_filter($feed, fn ($r) => $r['twitter'] || $r['web'] || $r['telegram']));

        return view('social', [
            'title'  => 'Social',
            'active' => 'social',
            'feed'   => array_slice($withSocials ?: $feed, 0, 24),
        ]);
    }
}
