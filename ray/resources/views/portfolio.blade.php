@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/portfolio.css') }}?v={{ @filemtime(public_path('css/portfolio.css')) }}">
@endpush

@section('content')
@php
    $SOL_MINT = 'So11111111111111111111111111111111111111112';
    $SOL_IMG  = 'https://axiom-assets-v2.axiom-cdn.io/images/sol-fill.svg';

    $usd = function ($v) {
        $v = (float) $v;
        if ($v >= 1e9) return '$' . number_format($v / 1e9, 2) . 'B';
        if ($v >= 1e6) return '$' . number_format($v / 1e6, 2) . 'M';
        if ($v >= 1e3) return '$' . number_format($v / 1e3, 2) . 'K';
        if ($v > 0 && $v < 0.01) return '$' . rtrim(rtrim(number_format($v, 6), '0'), '.');
        return '$' . number_format($v, 2);
    };
    $qty = function ($v) {
        $v = (float) $v;
        if ($v <= 0) return '0';
        if ($v >= 1000) return number_format($v, 0);
        if ($v >= 1) return rtrim(rtrim(number_format($v, 2), '0'), '.');
        return rtrim(rtrim(number_format($v, 4), '0'), '.');
    };
    $short = fn ($a) => strlen($a) > 12 ? substr($a, 0, 4) . '…' . substr($a, -4) : $a;
    $pct = fn ($v) => ($v >= 0 ? '+' : '') . number_format((float) $v, 2) . '%';
    $chg = fn ($v) => $v > 0.005 ? 'up' : ($v < -0.005 ? 'down' : 'flat');
    $palette = ['#526FFF', '#2FE3AC', '#F5A623', '#B07CFF', '#EC397A', '#38BDF8', '#FACC15'];

    $loaded = $portfolio && empty($portfolio['error']);
    $total  = $loaded ? (float) $portfolio['total'] : 0.0;

    $assets = [];
    if ($loaded) {
        $assets[] = [
            'symbol' => 'SOL', 'name' => 'Solana', 'mint' => $SOL_MINT, 'img' => $SOL_IMG,
            'price' => (float) $portfolio['solPrice'], 'change' => (float) ($portfolio['solChange'] ?? 0),
            'amount' => (float) $portfolio['sol'], 'usd' => (float) $portfolio['solUsd'],
        ];
        foreach ($portfolio['tokens'] as $t) {
            $assets[] = [
                'symbol' => $t['symbol'], 'name' => $t['name'], 'mint' => $t['mint'], 'img' => $t['img'],
                'price' => (float) $t['price'], 'change' => (float) $t['change'],
                'amount' => (float) $t['amount'], 'usd' => (float) $t['amount'] * (float) $t['price'],
            ];
        }
        usort($assets, fn ($a, $b) => $b['usd'] <=> $a['usd']);
    }

    $alloc = [];
    foreach ($assets as $a) {
        if ($a['usd'] > 0) $alloc[] = ['label' => $a['symbol'], 'value' => $a['usd']];
    }
    $topAlloc = array_slice($alloc, 0, 6);
    $restVal  = array_sum(array_column(array_slice($alloc, 6), 'value'));
    if ($restVal > 0) $topAlloc[] = ['label' => 'Others', 'value' => $restVal];
    foreach ($topAlloc as $i => &$seg) { $seg['color'] = $palette[$i % count($palette)]; }
    unset($seg);

    $C = 2 * 3.141592653589793 * 52;
    $cursor = 0.0;
    $segments = [];
    foreach ($topAlloc as $seg) {
        $dash = $total > 0 ? ($seg['value'] / $total) * $C : 0;
        $segments[] = ['color' => $seg['color'], 'dash' => round($dash, 2), 'offset' => round(-$cursor, 2)];
        $cursor += $dash;
    }

    $chg24 = $loaded ? (float) ($portfolio['change24h'] ?? 0) : 0.0;
    $pnl   = $loaded && $chg24 > -99.9 ? $total - ($total / (1 + $chg24 / 100)) : 0.0;

    $movers = $assets;
    usort($movers, fn ($a, $b) => abs($b['change']) <=> abs($a['change']));
    $movers = array_slice($movers, 0, 8);

    $assetCount = count($assets);
@endphp

<div class="pf-page">
    <div class="pf-head">
        <div class="pf-head-ic"><i class="ri-pie-chart-2-line"></i></div>
        <div class="pf-head-title">
            <h1>Portfolio</h1>
            <div class="pf-sub">Track balances, token holdings and recent on-chain activity.</div>
        </div>
        <div class="pf-head-right">
            @if ($primary)
                <button class="pf-walletchip" type="button" data-copy="{{ $primary->address }}" title="Copy wallet address">
                    <span class="pf-dot"></span>
                    <code>{{ $short($primary->address) }}</code>
                    <i class="ri-file-copy-line"></i>
                </button>
            @endif
            <button class="pf-connect-btn" type="button" data-wallet-connect>
                <i class="ri-wallet-3-line"></i> Connect Wallet
                @if ($wallets->count() > 1)<span class="pf-connect-count">{{ $wallets->count() }}</span>@endif
            </button>
        </div>
    </div>

    @if (session('ok'))<div class="pf-alert ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="pf-alert err">{{ $errors->first() }}</div>@endif

    @if ($loaded)
        <div class="pf-hero">
            <div class="pf-hero-main">
                <div class="pf-hero-label">Total portfolio value</div>
                <div class="pf-hero-value">{{ $usd($total) }}</div>
                <div class="pf-hero-meta">
                    <span class="pf-pill {{ $chg($chg24) }}">
                        <i class="ri-arrow-{{ $chg24 >= 0 ? 'up' : 'down' }}-line"></i>
                        {{ $pct($chg24) }} <em>24h</em>
                    </span>
                    @if (abs($pnl) >= 0.01)
                        <span class="pf-hero-pnl {{ $chg($chg24) }}">{{ $pnl >= 0 ? '+' : '-' }}{{ $usd(abs($pnl)) }} today</span>
                    @endif
                    <span class="pf-hero-updated">Updated {{ \Illuminate\Support\Carbon::parse($portfolio['updated_at'])->diffForHumans(null, true) }} ago</span>
                </div>
            </div>
            <div class="pf-hero-side">
                <div class="pf-donut-wrap">
                    <svg viewBox="0 0 120 120" class="pf-donut">
                        <circle class="pf-donut-track" cx="60" cy="60" r="52"></circle>
                        @foreach ($segments as $s)
                            <circle class="pf-donut-seg" cx="60" cy="60" r="52"
                                style="stroke: {{ $s['color'] }}; stroke-dasharray: {{ $s['dash'] }} {{ $C }}; stroke-dashoffset: {{ $s['offset'] }};"></circle>
                        @endforeach
                    </svg>
                    <div class="pf-donut-center">
                        <b>{{ $assetCount }}</b>
                        <span>{{ $assetCount === 1 ? 'Asset' : 'Assets' }}</span>
                    </div>
                </div>
                <div class="pf-legend">
                    @foreach ($topAlloc as $seg)
                        <div class="pf-legend-row">
                            <span class="pf-legend-dot" style="background: {{ $seg['color'] }}"></span>
                            <span class="pf-legend-name">{{ $seg['label'] }}</span>
                            <span class="pf-legend-val">{{ $total > 0 ? number_format($seg['value'] / $total * 100, 1) : '0.0' }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pf-stats">
            <div class="pf-stat">
                <span class="pf-stat-ic"><i class="ri-wallet-3-line"></i></span>
                <div><b>{{ $qty($portfolio['sol']) }} SOL</b><span>SOL balance</span></div>
            </div>
            <div class="pf-stat">
                <span class="pf-stat-ic"><i class="ri-coin-line"></i></span>
                <div><b>{{ $usd($portfolio['solPrice']) }}</b><span>SOL price</span></div>
            </div>
            <div class="pf-stat">
                <span class="pf-stat-ic"><i class="ri-stack-line"></i></span>
                <div><b>{{ count($portfolio['tokens']) }}</b><span>SPL tokens</span></div>
            </div>
            <div class="pf-stat">
                <span class="pf-stat-ic"><i class="ri-line-chart-line"></i></span>
                <div><b class="{{ $chg($chg24) }}">{{ $pnl != 0 ? ($pnl > 0 ? '+' : '') . $usd($pnl) : $pct($chg24) }}</b><span>24h P/L</span></div>
            </div>
        </div>

        @if ($assetCount > 1)
        <div class="pf-panel pf-movers-panel">
            <div class="pf-panel-head"><h2><i class="ri-flashlight-line"></i> Top movers</h2><span class="pf-count">Last 24h</span></div>
            <div class="pf-movers">
                @foreach ($movers as $m)
                    <a class="pf-mover" href="/token/{{ $m['mint'] }}">
                        <div class="pf-mover-top">
                            @if ($m['img'])
                                <img class="pf-mover-logo" src="{{ $m['img'] }}" alt="" loading="lazy" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'pf-mover-logo txt',textContent:'{{ strtoupper(substr($m['symbol'],0,2)) }}'}))">
                            @else
                                <span class="pf-mover-logo txt">{{ strtoupper(substr($m['symbol'], 0, 2)) }}</span>
                            @endif
                            <span class="pf-mover-sym">{{ $m['symbol'] }}</span>
                        </div>
                        <span class="pf-mover-price">{{ $m['price'] > 0 ? $usd($m['price']) : '—' }}</span>
                        <span class="pf-mover-chg {{ $chg($m['change']) }}">{{ $m['change'] >= 0 ? '+' : '' }}{{ number_format($m['change'], 2) }}%</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="pf-two">
            <div class="pf-panel">
                <div class="pf-panel-head">
                    <h2><i class="ri-coin-line"></i> Holdings</h2>
                    <span class="pf-count" data-pf-holdings-count>{{ $assetCount }} {{ $assetCount === 1 ? 'asset' : 'assets' }}</span>
                </div>
                <div class="pf-tool">
                    <label class="pf-search">
                        <i class="ri-search-line"></i>
                        <input type="search" placeholder="Search holdings" data-pf-search autocomplete="off">
                    </label>
                </div>
                <div class="pf-scroll">
                    <table class="pf-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-pf-sort="search">Asset</th>
                                <th class="sortable" data-pf-sort="price">Price <i class="ri-expand-up-down-line pf-sort"></i></th>
                                <th class="sortable" data-pf-sort="change">24h <i class="ri-expand-up-down-line pf-sort"></i></th>
                                <th class="sortable" data-pf-sort="amount">Amount <i class="ri-expand-up-down-line pf-sort"></i></th>
                                <th class="sortable" data-pf-sort="usd">Value <i class="ri-expand-up-down-line pf-sort"></i></th>
                                <th class="sortable" data-pf-sort="weight">Weight <i class="ri-expand-up-down-line pf-sort"></i></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody data-pf-holdings>
                            @foreach ($assets as $a)
                                <tr class="pf-row"
                                    data-href="/token/{{ $a['mint'] }}"
                                    tabindex="0" role="link" title="Open {{ $a['symbol'] }}"
                                    data-search="{{ strtolower($a['symbol'] . ' ' . $a['name']) }}"
                                    data-price="{{ $a['price'] }}"
                                    data-change="{{ $a['change'] }}"
                                    data-amount="{{ $a['amount'] }}"
                                    data-usd="{{ $a['usd'] }}"
                                    data-weight="{{ $total > 0 ? $a['usd'] / $total * 100 : 0 }}">
                                    <td>
                                        <div class="pf-asset">
                                            @if ($a['img'])
                                                <img class="pf-logo" src="{{ $a['img'] }}" alt="" loading="lazy" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'pf-logo txt',textContent:'{{ strtoupper(substr($a['symbol'],0,2)) }}'}))">
                                            @else
                                                <span class="pf-logo txt">{{ strtoupper(substr($a['symbol'], 0, 2)) }}</span>
                                            @endif
                                            <div class="pf-asset-txt">
                                                <b>{{ $a['symbol'] }}</b>
                                                <span>{{ $a['name'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $a['price'] > 0 ? $usd($a['price']) : '—' }}</td>
                                    <td class="pf-chg {{ $chg($a['change']) }}">{{ $a['change'] >= 0 ? '+' : '' }}{{ number_format($a['change'], 2) }}%</td>
                                    <td>{{ $qty($a['amount']) }}</td>
                                    <td><b class="pf-val">{{ $usd($a['usd']) }}</b></td>
                                    <td>
                                        <div class="pf-weight">
                                            <div class="pf-bar"><i style="width: {{ $total > 0 ? min(100, $a['usd'] / $total * 100) : 0 }}%"></i></div>
                                            <span>{{ $total > 0 ? number_format($a['usd'] / $total * 100, 1) : '0.0' }}%</span>
                                        </div>
                                    </td>
                                    <td><a class="pf-x" href="/token/{{ $a['mint'] }}" title="Open token"><i class="ri-arrow-right-up-line"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pf-empty" data-pf-holdings-empty style="display:none"><i class="ri-search-line"></i>No holdings match your search.</div>
            </div>

            <div class="pf-panel">
                <div class="pf-panel-head"><h2><i class="ri-history-line"></i> Recent activity</h2>{{-- <a class="pf-link" href="https://solscan.io/account/{{ $primary->address ?? '' }}" target="_blank" rel="noopener">View all</a> --}}</div>
                @if (empty($activity))
                    <div class="pf-empty"><i class="ri-inbox-line"></i>No recent transactions.</div>
                @else
                    <div class="pf-activity">
                        @foreach ($activity as $a)
                            <a class="pf-act-row" href="https://solscan.io/tx/{{ $a['signature'] }}" target="_blank" rel="noopener">
                                <span class="pf-act-ic {{ $a['err'] ? 'bad' : 'ok' }}"><i class="{{ $a['err'] ? 'ri-close-line' : 'ri-check-line' }}"></i></span>
                                <div class="pf-act-body">
                                    <b>{{ $short($a['signature']) }}</b>
                                    <span>{{ $a['err'] ? 'Failed transaction' : 'Confirmed transaction' }}</span>
                                </div>
                                <span class="pf-act-time">{{ $a['blockTime'] ? \Illuminate\Support\Carbon::createFromTimestamp($a['blockTime'])->diffForHumans(null, true) : '' }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @elseif ($primary)
        <div class="pf-alert err">{{ $portfolio['error'] ?? 'Could not load portfolio data.' }}</div>
    @endif

    @if ($wallets->isEmpty())
        <div class="pf-connect">
            <i class="pf-big ri-wallet-3-line"></i>
            <h3>Connect a wallet to get started</h3>
            <p>Link a Solana wallet to track your total value, token allocation and recent on-chain activity in one place.</p>
            <button class="pf-connect-btn" type="button" data-wallet-connect><i class="ri-wallet-3-line"></i> Connect Wallet</button>
        </div>
    @endif

    <div class="pf-panel">
        <div class="pf-panel-head">
            <h2><i class="ri-link"></i> Wallets</h2>
            <span class="pf-count">{{ $wallets->count() }}</span>
            <button class="pf-mini" type="button" data-wallet-connect><i class="ri-add-line"></i> Connect</button>
        </div>
        @if ($wallets->isEmpty())
            <div class="pf-empty"><i class="ri-wallet-3-line"></i>No wallets connected yet.</div>
        @else
            <div class="pf-wallets">
                @foreach ($wallets as $w)
                    <div class="pf-wallet-row">
                        <span class="pf-avatar"><i class="ri-wallet-3-line"></i></span>
                        <div class="pf-wallet-info">
                            <b>{{ $w->label ?: 'Wallet' }} @if ($w->is_primary)<span class="pf-badge" style="margin-left:6px">Primary</span>@endif</b>
                            <code>{{ $short($w->address) }}</code>
                        </div>
                        <div class="fx-actions">
                            <button class="pf-x" type="button" title="Copy address" data-copy="{{ $w->address }}"><i class="ri-file-copy-line"></i></button>
                            @unless ($w->is_primary)
                                <form method="POST" action="/portfolio/{{ $w->id }}/primary">@csrf<button class="pf-x" title="Make primary"><i class="ri-star-line"></i></button></form>
                            @endunless
                            <form method="POST" action="/portfolio/{{ $w->id }}" onsubmit="return confirm('Disconnect this wallet?')">@csrf @method('DELETE')<button class="pf-x" title="Disconnect"><i class="ri-link-unlink"></i></button></form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="pf-panel">
        <div class="pf-panel-head"><h2><i class="ri-star-line"></i> Watchlist</h2><span class="pf-count">{{ $watchlist->count() }}</span></div>
        <form method="POST" action="/watchlist" class="pf-form">
            @csrf
            <input class="fx-input" style="flex:1 1 300px" name="address" placeholder="Solana token address to watch" required>
            <input class="fx-input" style="flex:0 1 140px" name="symbol" placeholder="Symbol">
            <button class="fx-btn"><i class="ri-add-line"></i> Watch</button>
        </form>
        @if ($watchlist->isEmpty())
            <div class="pf-empty"><i class="ri-star-line"></i>Your watchlist is empty.</div>
        @else
            <div class="pf-watch">
                @foreach ($watchlist as $wl)
                    <div class="pf-watch-card">
                        <a href="/token/{{ $wl->address }}">
                            <b>{{ $wl->symbol ?: 'Token' }}</b>
                            <code>{{ $short($wl->address) }}</code>
                        </a>
                        <form method="POST" action="/watchlist/{{ $wl->id }}">@csrf @method('DELETE')<button class="pf-x" title="Remove"><i class="ri-close-line"></i></button></form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/axiom-portfolio.js') }}?v={{ @filemtime(public_path('js/axiom-portfolio.js')) }}" defer></script>
@endpush
