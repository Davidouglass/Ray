@extends('layouts.app')

@section('content')
<div class="fx-page">
    @if (session('ok'))
        <div class="fx-alert ok">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="fx-alert err">{{ $errors->first() }}</div>
    @endif

    <a href="/copy-trading" class="cp-back"><i class="ri-arrow-left-line"></i>All Traders</a>

    <div class="cp-hero" style="--cp-hue:{{ ($trader->id * 47) % 360 }}">
        <div class="cp-hero-banner"></div>
        <div class="cp-hero-body">
            <div class="cp-hero-avatar"><img src="{{ asset($trader->avatar_url) }}" alt="" onerror="this.style.display='none'"></div>
            <div class="cp-hero-main">
                <div class="cp-name-row">
                    <h1 class="cp-hero-name">{{ $trader->display_name }}</h1>
                    @if ($trader->is_verified)<i class="ri-verified-badge-fill cp-verified lg"></i>@endif
                    @if ($trader->is_featured)<span class="cp-featured-pill"><i class="ri-star-fill"></i></span>@endif
                </div>
                <div class="cp-tagline">{{ $trader->tagline }}</div>
                <div class="cp-chips">
                    <span class="cp-chip risk-{{ $trader->risk_level }}">{{ ucfirst($trader->risk_level) }} risk</span>
                    @foreach ($trader->tags ?? [] as $tag)
                        <span class="cp-chip">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            <div class="cp-hero-followers">
                <b>{{ number_format($trader->followers_count) }}</b>
                <span>copying</span>
            </div>
        </div>
    </div>

    @auth
        <div class="cp-subscribe">
            @if ($subscribed)
                <div class="cp-sub-active">
                    <i class="ri-checkbox-circle-fill"></i>
                    <div>
                        <b>You're copying {{ $trader->display_name }}</b>
                        <span>$100/month · every new position they open mirrors to your wallet</span>
                    </div>
                </div>
            @else
                <div class="cp-sub-info">
                    <b>$100<span>/month</span></b>
                    <span>Auto-mirrors this trader's positions to your connected wallet</span>
                </div>
                @if (Auth::user()->connectedWallets()->exists())
                    <form method="POST" action="{{ route('copy-trading.subscribe', ['id' => $trader->id]) }}">
                        @csrf
                        <button type="submit" class="fx-btn cp-sub-btn"><i class="ri-user-follow-line"></i>Copy {{ $trader->display_name }}</button>
                    </form>
                @else
                    <a href="/portfolio" class="fx-btn ghost"><i class="ri-wallet-line"></i>Connect a wallet first</a>
                @endif
            @endif
        </div>
    @else
        <div class="cp-subscribe">
            <div class="cp-sub-info">
                <b>$100<span>/month</span></b>
                <span>Log in and connect a wallet to start copying</span>
            </div>
            <a href="/login" class="fx-btn"><i class="ri-login-box-line"></i>Log in to copy</a>
        </div>
    @endauth

    <div class="cp-stats-grid">
        <div class="fx-panel cp-stat"><span>30d PnL</span><b class="{{ $trader->pnl_color }} cp-big">{{ $trader->total_pnl_usd >= 0 ? '+' : '-' }}${{ rtrim(rtrim(number_format(abs((float) $trader->total_pnl_usd), 0), '0'), ',') }}</b></div>
        <div class="fx-panel cp-stat"><span>ROI 7d</span><b class="{{ $trader->roi_7d >= 0 ? 'fx-up' : 'fx-down' }} cp-big">{{ number_format((float) $trader->roi_7d, 1) }}%</b></div>
        <div class="fx-panel cp-stat"><span>ROI 30d</span><b class="{{ $trader->roi_30d >= 0 ? 'fx-up' : 'fx-down' }} cp-big">{{ number_format((float) $trader->roi_30d, 1) }}%</b></div>
        <div class="fx-panel cp-stat"><span>Win Rate</span><b class="cp-big">{{ number_format((float) $trader->win_rate, 1) }}%</b></div>
        <div class="fx-panel cp-stat"><span>Max Drawdown</span><b class="cp-big fx-down">{{ number_format((float) $trader->max_drawdown, 1) }}%</b></div>
        <div class="fx-panel cp-stat"><span>24h Volume</span><b class="cp-big">${{ rtrim(rtrim(number_format((float) $trader->volume_24h_usd, 0), '0'), ',') }}</b></div>
        <div class="fx-panel cp-stat"><span>Avg Trade</span><b class="cp-big">${{ rtrim(rtrim(number_format((float) $trader->avg_trade_usd, 0), '0'), ',') }}</b></div>
        <div class="fx-panel cp-stat"><span>Total Trades</span><b class="cp-big">{{ number_format($trader->trades_count) }}</b></div>
    </div>

    <div class="cp-cols">
        <div class="cp-main-col">
            <div class="fx-panel">
                <div class="cp-panel-head">
                    <h2><i class="ri-line-chart-line"></i>Equity Curve — Last 30 Days</h2>
                    <span class="cp-panel-tag">Daily close · indexed to 100</span>
                </div>
                @php $hist = $trader->stats_history ?? []; @endphp
                @if (count($hist) >= 2)
                    @php
                        $n = count($hist);
                        $min = min($hist); $max = max($hist);
                        $pad = max(($max - $min) * 0.12, 0.5);
                        $lo = $min - $pad; $hi = $max + $pad; $span = $hi - $lo;
                        $W = 720; $H = 230; $L = 46; $R = 14; $T = 14; $B = 26;
                        $cw = $W - $L - $R; $ch = $H - $T - $B;
                        $x = fn ($i) => $L + ($i / ($n - 1)) * $cw;
                        $y = fn ($v) => $T + (1 - ($v - $lo) / $span) * $ch;
                        $pts = [];
                        foreach ($hist as $i => $v) { $pts[] = round($x($i), 1) . ',' . round($y($v), 1); }
                        $up = $hist[$n - 1] >= $hist[0];
                        $line = $up ? '#2FE3AC' : '#FF5C7A';
                        $gradId = 'eqg' . $trader->id;
                        $grid = [];
                        for ($g = 0; $g <= 4; $g++) {
                            $gv = $lo + ($span * $g / 4);
                            $gy = $y($gv);
                            $grid[] = ['y' => round($gy, 1), 'label' => number_format($gv, 1)];
                        }
                        $baseY = round($y($hist[0]), 1);
                        $minIdx = array_search($min, $hist);
                        $maxIdx = array_search($max, $hist);
                    @endphp
                    <svg class="cp-chart" viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="none" role="img" aria-label="Equity curve">
                        <defs>
                            <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="{{ $line }}" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="{{ $line }}" stop-opacity="0.02"/>
                            </linearGradient>
                        </defs>
                        @foreach ($grid as $g)
                            <line x1="{{ $L }}" y1="{{ $g['y'] }}" x2="{{ $W - $R }}" y2="{{ $g['y'] }}" stroke="rgba(255,255,255,.06)" stroke-width="1"/>
                            <text x="{{ $L - 8 }}" y="{{ $g['y'] + 3.5 }}" text-anchor="end" font-size="9.5" fill="rgba(255,255,255,.38)">{{ $g['label'] }}</text>
                        @endforeach
                        <line x1="{{ $L }}" y1="{{ $baseY }}" x2="{{ $W - $R }}" y2="{{ $baseY }}" stroke="rgba(255,255,255,.25)" stroke-width="1" stroke-dasharray="4 4"/>
                        <polygon points="{{ $L }},{{ $H - $B }} {{ implode(' ', $pts) }} {{ $W - $R }},{{ $H - $B }}" fill="url(#{{ $gradId }})"/>
                        <polyline points="{{ implode(' ', $pts) }}" fill="none" stroke="{{ $line }}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                        <circle cx="{{ round($x($n - 1), 1) }}" cy="{{ round($y($hist[$n - 1]), 1) }}" r="3.5" fill="{{ $line }}"/>
                        <text x="{{ $L }}" y="{{ $H - 8 }}" font-size="9.5" fill="rgba(255,255,255,.38)">30d ago</text>
                        <text x="{{ $W - $R }}" y="{{ $H - 8 }}" text-anchor="end" font-size="9.5" fill="rgba(255,255,255,.38)">Today</text>
                    </svg>
                    <div class="cp-chart-legend">
                        <span><i class="dot" style="background:{{ $line }}"></i>Equity <b class="{{ $up ? 'fx-up' : 'fx-down' }}">{{ number_format($hist[$n - 1], 1) }}</b></span>
                        <span>Start {{ number_format($hist[0], 1) }}</span>
                        <span>High <b>{{ number_format($max, 1) }}</b></span>
                        <span>Low <b>{{ number_format($min, 1) }}</b></span>
                    </div>
                @else
                    <div class="fx-empty"><i class="ri-line-chart-line"></i>No history yet.</div>
                @endif
            </div>

            <div class="fx-panel">
                <div class="cp-panel-head">
                    <h2><i class="ri-exchange-line"></i>Executed Trades</h2>
                    <span class="cp-panel-tag"><span class="cp-live-dot"></span>{{ number_format($recentTrades->count()) }} most recent of {{ number_format($trader->trades_count) }} total</span>
                </div>
                @if ($recentTrades->isEmpty())
                    <div class="fx-empty"><i class="ri-inbox-line"></i>No trades recorded yet.</div>
                @else
                    <div class="cp-table-wrap">
                        <table class="fx-table cp-table">
                            <thead>
                                <tr><th>Token</th><th>Side</th><th>Size</th><th>Price</th><th>Qty</th><th>Signature</th><th>Executed</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($recentTrades as $ct)
                                    <tr>
                                        <td class="fx-sym"><span class="cp-sym-dot" style="background:hsl({{ (crc32($ct->symbol) % 360) }} 70% 55%)"></span>{{ $ct->symbol }}</td>
                                        <td><span class="cp-side {{ $ct->action === 'buy' ? 'buy' : 'sell' }}">{{ $ct->action === 'buy' ? 'BUY' : 'SELL' }}</span></td>
                                        <td class="{{ $ct->action === 'buy' ? 'fx-up' : 'fx-down' }}">${{ number_format((float) $ct->total_usd, 2) }}</td>
                                        <td>${{ rtrim(rtrim(number_format((float) $ct->price, 6), '0'), '.') }}</td>
                                        <td>{{ number_format((float) $ct->amount, (float) $ct->price > 1 ? 3 : 2) }}</td>
                                        <td><span class="fx-addr cp-tx">{{ substr($ct->tx_signature, 0, 4) }}…{{ substr($ct->tx_signature, -4) }}</span></td>
                                        <td>{{ $ct->executed_at?->diffForHumans(short: true) ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="cp-side-col">
            <div class="fx-panel">
                <h2><i class="ri-user-3-line"></i>About</h2>
                <p class="cp-bio">{{ $trader->bio ?: $trader->tagline }}</p>
                @if ($trader->short_wallet)
                    <div class="cp-wallet"><i class="ri-wallet-3-line"></i><span class="fx-addr">{{ $trader->short_wallet }}</span></div>
                @endif
                <div class="cp-risk-block">
                    <div class="cp-risk-head">
                        <span>Risk level</span>
                        <b class="risk-text risk-{{ $trader->risk_level }}">{{ ucfirst($trader->risk_level) }}</b>
                    </div>
                    <div class="fx-bar"><span style="width:{{ $trader->risk_score }}%"></span></div>
                </div>
            </div>

            <div class="fx-panel">
                <h2><i class="ri-timer-line"></i>Track Record</h2>
                <div class="fx-kv"><span>On Veridian since</span><b>{{ $trader->created_at?->format('M Y') ?? '—' }}</b></div>
                <div class="fx-kv"><span>Trades executed</span><b>{{ number_format($trader->trades_count) }}</b></div>
                <div class="fx-kv"><span>Followers</span><b>{{ number_format($trader->followers_count) }}</b></div>
                <div class="fx-kv"><span>Est. daily trades</span><b>{{ max(1, (int) round($trader->trades_count / 30)) }}</b></div>
                <div class="fx-kv"><span>Fee</span><b>$100 / month</b></div>
            </div>
        </div>
    </div>
</div>

<style>
.cp-back { display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--text-tertiary); text-decoration:none; }
.cp-back:hover { color:var(--text-primary); }
.cp-hero { border:1px solid var(--primary-stroke); border-radius:14px; overflow:hidden; background:var(--bg-secondary); }
.cp-hero-banner { height:86px; background:
    radial-gradient(600px 120px at 20% 100%, hsl(var(--cp-hue) 80% 55% / .55), transparent 70%),
    linear-gradient(120deg,hsl(var(--cp-hue) 70% 42%),hsl(calc(var(--cp-hue) + 60) 70% 26%)); }
.cp-hero-body { display:flex; gap:16px; align-items:flex-end; padding:0 20px 18px; flex-wrap:wrap; }
.cp-hero-avatar { width:74px; height:74px; border-radius:18px; margin-top:-37px; background:linear-gradient(135deg,hsl(var(--cp-hue) 75% 52%),hsl(calc(var(--cp-hue) + 50) 75% 38%)); border:3px solid var(--bg-secondary); flex:none; overflow:hidden; display:flex; align-items:center; justify-content:center; }
.cp-hero-avatar img { width:100%; height:100%; object-fit:cover; }
.cp-hero-main { flex:1; min-width:220px; }
.cp-hero-name { font-size:22px; font-weight:800; color:var(--text-primary); display:flex; align-items:center; gap:8px; }
.cp-verified.lg { font-size:18px; }
.cp-hero-followers { text-align:right; display:flex; flex-direction:column; gap:2px; }
.cp-hero-followers b { font-size:20px; font-weight:800; color:var(--text-primary); }
.cp-hero-followers span { font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-tertiary); }
.cp-subscribe { display:flex; align-items:center; gap:16px; flex-wrap:wrap; border:1px solid rgba(59,130,246,.35); background:rgba(59,130,246,.07); border-radius:12px; padding:14px 18px; }
.cp-sub-info b { font-size:22px; font-weight:800; color:var(--text-primary); display:block; }
.cp-sub-info b span { font-size:13px; font-weight:500; color:var(--text-tertiary); }
.cp-sub-info > span { font-size:12.5px; color:var(--text-tertiary); }
.cp-subscribe form { margin-left:auto; }
.cp-sub-btn { background:var(--up); border-color:var(--up); color:#04121C; font-weight:700; }
.cp-sub-btn:hover { background:#26c792; border-color:#26c792; }
.cp-sub-active { display:flex; align-items:center; gap:12px; color:var(--up); }
.cp-sub-active i { font-size:26px; }
.cp-sub-active b { display:block; font-size:14.5px; color:var(--text-primary); }
.cp-sub-active span { font-size:12.5px; color:var(--text-tertiary); }
.cp-stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; }
.cp-stat { display:flex; flex-direction:column; gap:5px; padding:14px 16px; }
.cp-stat span { font-size:10px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-tertiary); font-weight:600; }
.cp-big { font-size:19px; font-weight:800; color:var(--text-primary); }
.cp-cols { display:grid; grid-template-columns:1fr 300px; gap:14px; align-items:start; }
.cp-main-col { display:flex; flex-direction:column; gap:14px; min-width:0; }
.cp-side-col { display:flex; flex-direction:column; gap:14px; }
.cp-panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:12px; }
.cp-panel-head h2 { margin-bottom:0; }
.cp-panel-tag { font-size:11px; color:var(--text-tertiary); display:inline-flex; align-items:center; gap:7px; }
.cp-live-dot { width:7px; height:7px; border-radius:50%; background:var(--up); box-shadow:0 0 6px var(--up); animation:cpulse 1.6s infinite; }
@keyframes cpulse { 0%,100%{opacity:1} 50%{opacity:.35} }
.cp-chart { width:100%; height:230px; display:block; }
.cp-chart-legend { display:flex; gap:18px; flex-wrap:wrap; font-size:11.5px; color:var(--text-tertiary); margin-top:8px; }
.cp-chart-legend b { color:var(--text-primary); }
.cp-chart-legend .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:5px; }
.cp-table-wrap { overflow-x:auto; }
.cp-table { min-width:640px; }
.cp-sym-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:7px; vertical-align:middle; }
.cp-side { font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:6px; letter-spacing:.04em; }
.cp-side.buy { background:rgba(47,227,172,.12); color:var(--up); }
.cp-side.sell { background:rgba(255,92,122,.12); color:#FF5C7A; }
.cp-tx { font-size:11px; }
.cp-bio { font-size:13px; color:var(--text-secondary); line-height:1.55; }
.cp-wallet { display:flex; align-items:center; gap:8px; margin-top:12px; color:var(--text-tertiary); font-size:12.5px; }
.cp-risk-block { margin-top:14px; }
.cp-risk-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px; color:var(--text-tertiary); }
.cp-risk-head b.risk-text.risk-low { color:var(--up); }
.cp-risk-head b.risk-text.risk-medium { color:#F5C542; }
.cp-risk-head b.risk-text.risk-high { color:#FF5C7A; }
.cp-risk-block .fx-bar span { background:linear-gradient(90deg,var(--up),#F5C542,#FF5C7A); }
.cp-chip { font-size:10.5px; padding:3px 8px; border-radius:6px; background:var(--bg-tertiary); color:var(--text-tertiary); font-weight:600; }
.cp-chip.risk-low { background:rgba(47,227,172,.12); color:var(--up); }
.cp-chip.risk-medium { background:rgba(245,197,66,.12); color:#F5C542; }
.cp-chip.risk-high { background:rgba(255,92,122,.12); color:#FF5C7A; }
.cp-chips { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
.cp-name-row { display:flex; align-items:center; gap:7px; min-width:0; }
.cp-verified { color:var(--blue); font-size:14px; flex:none; }
.cp-featured-pill { flex:none; width:22px; height:22px; border-radius:6px; background:rgba(245,197,66,.14); color:#F5C542; display:inline-flex; align-items:center; justify-content:center; font-size:11px; }
.cp-tagline { font-size:13px; color:var(--text-tertiary); margin-top:4px; }
@media (max-width:980px){
    .cp-cols{grid-template-columns:1fr}
    .cp-hero-followers{display:none}
}
</style>
@endsection
