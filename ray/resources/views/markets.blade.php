@extends('layouts.app')

@section('content')
@php
    $asset = 'https://axiom-assets-v2.axiom-cdn.io/images';
    $chains = [
        ['Solana', 'sol-fill.svg'],
        ['BNB', 'bnb-fill.svg'],
        ['Robinhood', 'robinhood-logo.svg'],
        ['HYPE', 'hype-fill.svg'],
    ];
@endphp

<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-line-chart-line"></i></div>
        <div>
            <h1>Markets</h1>
            <div class="fx-sub">Native prices, chain coverage and the tokens moving right now.</div>
        </div>
        <div class="fx-spacer"></div>
        <a class="fx-btn ghost" href="/discover"><i class="ri-compass-3-line"></i>Discover</a>
    </div>

    <div class="fx-panel">
        <div class="fx-stat-row">
            <div class="fx-stat"><b style="color:#14F195">{{ $spot['SOL'] ?? '$—' }}</b><span>Solana · SOL</span></div>
            <div class="fx-stat"><b style="color:#F0B90B">{{ $spot['BNB'] ?? '$—' }}</b><span>BNB Chain · BNB</span></div>
            <div class="fx-stat"><b style="color:#97FCE4">{{ $spot['HYPE'] ?? '$—' }}</b><span>Hyperliquid · HYPE</span></div>
        </div>
    </div>

    <div class="fx-panel">
        <h2><i class="ri-links-line"></i> Chains</h2>
        <div class="fx-grid">
            @foreach ($chains as $c)
                <a class="fx-card" href="/discover">
                    <div class="fx-card-top">
                        <img class="fx-token-logo" style="width:36px;height:36px;border-radius:9px" src="https://axiom-assets-v2.axiom-cdn.io/images/{{ $c[1] }}" alt="{{ $c[0] }}">
                        <div>
                            <div class="fx-token-name">{{ $c[0] }}</div>
                            <div class="fx-token-tick">Explore markets</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="fx-panel">
        <h2><i class="ri-fire-line"></i> Trending tokens</h2>
        @if (empty($trending))
            <div class="fx-empty"><i class="ri-signal-wifi-off-line"></i>Market data unavailable right now.</div>
        @else
            <div style="overflow-x:auto">
            <table class="fx-table">
                <thead><tr><th>Token</th><th>Price</th><th>24h</th><th>Liquidity</th><th>Volume</th><th>Chain</th><th></th></tr></thead>
                <tbody>
                    @foreach ($trending as $t)
                        <tr>
                            <td>
                                <a class="fx-token" href="/token/{{ $t['address'] }}">
                                    <img class="fx-token-logo" style="width:30px;height:30px;border-radius:8px" src="{{ $t['img'] }}" alt="">
                                    <span><span class="fx-sym">{{ $t['sym'] }}</span><br><span class="fx-token-tick">{{ $t['name'] }}</span></span>
                                </a>
                            </td>
                            <td>{{ $t['price'] }}</td>
                            <td class="{{ $t['up'] ? 'fx-up' : 'fx-down' }} fx-pct small">{{ $t['pct'] }}</td>
                            <td>{{ $t['liq'] }}</td>
                            <td>{{ $t['vol'] }}</td>
                            <td><span class="fx-chip">{{ strtoupper($t['chain']) }}</span></td>
                            <td><a class="fx-btn sm ghost" href="/token/{{ $t['address'] }}">Trade</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>
@endsection
