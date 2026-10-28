@extends('layouts.app')

@section('content')
@php
    $usd = function ($v) { $v=(float)$v; $a=abs($v); if ($a>=1e6) return '$'.number_format($v/1e6,2).'M'; if ($a>=1e3) return '$'.number_format($v/1e3,2).'K'; return '$'.number_format($v,2); };
    $pnlFmt = function ($v) use ($usd) { $v=(float)$v; return ($v>=0?'+':'-').$usd(abs($v)); };
@endphp

<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-bar-chart-line"></i></div>
        <div>
            <h1>PnL</h1>
            <div class="fx-sub">Profit &amp; loss across your connected wallets, priced live.</div>
        </div>
        <div class="fx-spacer"></div>
        <a class="fx-btn ghost" href="/portfolio"><i class="ri-wallet-3-line"></i>Manage wallets</a>
    </div>

    <div class="fx-panel">
        <div class="fx-stat-row">
            <div class="fx-stat"><b>{{ $usd($totalUsd) }}</b><span>Portfolio value</span></div>
            <div class="fx-stat"><b class="{{ $pnl24 >= 0 ? 'fx-up' : 'fx-down' }}">{{ $pnlFmt($pnl24) }}</b><span>24h PnL</span></div>
            <div class="fx-stat"><b>{{ $totalUsd > 0 ? number_format($pnl24 / $totalUsd * 100, 2) . '%' : '—' }}</b><span>24h Return</span></div>
            <div class="fx-stat"><b>{{ count($positions) }}</b><span>Positions</span></div>
            <div class="fx-stat"><b>{{ $walletCount }}</b><span>Wallets</span></div>
        </div>
    </div>

    <div class="fx-panel">
        <h2><i class="ri-list-check-2"></i> Positions</h2>
        @if (empty($positions))
            <div class="fx-empty">
                <i class="ri-wallet-3-line"></i>
                No positions to show. <a href="/portfolio" style="color:var(--blue)">Connect a wallet</a> to track PnL.
            </div>
        @else
            <div style="overflow-x:auto">
            <table class="fx-table">
                <thead><tr><th>Token</th><th>Amount</th><th>Price</th><th>24h</th><th>Value</th><th>24h PnL</th></tr></thead>
                <tbody>
                    @foreach ($positions as $p)
                        <tr>
                            <td>
                                <a class="fx-token" href="/token/{{ $p['mint'] }}">
                                    @if ($p['img'])<img class="fx-token-logo" style="width:30px;height:30px;border-radius:8px" src="{{ $p['img'] }}" alt="">@endif
                                    <span><span class="fx-sym">{{ $p['symbol'] }}</span><br><span class="fx-token-tick">{{ $p['wallet'] }}</span></span>
                                </a>
                            </td>
                            <td>{{ rtrim(rtrim(number_format($p['amount'], 6), '0'), '.') }}</td>
                            <td>{{ $p['price'] >= 1 ? '$'.number_format($p['price'],2) : '$'.rtrim(rtrim(sprintf('%.8f',$p['price']),'0'),'.') }}</td>
                            <td class="{{ $p['change'] >= 0 ? 'fx-up' : 'fx-down' }} fx-pct small">{{ $p['change'] >= 0 ? '+' : '' }}{{ number_format($p['change'], 2) }}%</td>
                            <td><b class="fx-sym">{{ $usd($p['usd']) }}</b></td>
                            <td class="{{ $p['pnl'] >= 0 ? 'fx-up' : 'fx-down' }}">{{ $pnlFmt($p['pnl']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>
@endsection
