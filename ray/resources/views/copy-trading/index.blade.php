@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="cp-hero">
        <div class="cp-hero-glow"></div>
        <div class="cp-hero-inner">
            <div class="cp-hero-copy">
                <div class="cp-eyebrow"><span class="cp-live-dot"></span>{{ number_format($stats['traders']) }} verified traders · live execution</div>
                <h1>Copy the best traders<br>on Solana <span class="cp-gradient-text">automatically</span></h1>
                <p>Subscribe once. Every position they open is mirrored to your wallet in real time — sized to your balance, exited when they exit.</p>
                <div class="cp-hero-ctas">
                    <a href="#cp-browse" class="fx-btn cp-hero-btn"><i class="ri-compass-3-line"></i>Browse Traders</a>
                    @guest
                        <a href="/login" class="fx-btn ghost">Log in to start</a>
                    @endguest
                </div>
            </div>
            <div class="cp-hero-stats">
                <div class="cp-hstat"><b>{{ number_format($stats['traders']) }}</b><span>Elite traders</span></div>
                <div class="cp-hstat"><b>{{ number_format($stats['followers']) }}</b><span>Active copiers</span></div>
                <div class="cp-hstat"><b>${{ rtrim(rtrim(number_format($stats['volume'], 0), '0'), ',') }}</b><span>24h copied volume</span></div>
                <div class="cp-hstat"><b>{{ number_format($stats['winRate'], 1) }}%</b><span>Avg win rate</span></div>
            </div>
        </div>
    </div>

    @if (session('ok'))
        <div class="fx-alert ok">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="fx-alert err">{{ $errors->first() }}</div>
    @endif

    @if ($mySubscription)
        <div class="fx-panel" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
            <div class="fx-ic" style="background:rgba(47,227,172,.12);color:var(--up)"><i class="ri-checkbox-circle-line"></i></div>
            <div style="flex:1;min-width:200px">
                <div style="font-weight:650;color:var(--text-primary)">Copy trading active</div>
                <div class="fx-sub">Subscribed to <b style="color:var(--text-secondary)">{{ $mySubscription->copyTrader->display_name ?? 'a trader' }}</b> · $100/month · renews {{ $mySubscription->expires_at?->format('M j, Y') ?? '—' }}</div>
            </div>
        </div>
    @endif

    <div class="cp-ticker" aria-label="Recent trades across copied traders">
        <div class="cp-ticker-label"><i class="ri-flashlight-line"></i>Live copies</div>
        <div class="cp-ticker-track">
            <div class="cp-ticker-row">
                @foreach ($liveTrades as $lt)
                    <span class="cp-tick {{ $lt->action }}">
                        <b>{{ $lt->action === 'buy' ? 'BUY' : 'SELL' }}</b>
                        {{ $lt->symbol }}
                        <i>${{ rtrim(rtrim(number_format((float) $lt->total_usd, 0), '0'), ',') }}</i>
                        <em>{{ $lt->trader_name }}</em>
                        <u>{{ $lt->executed_at?->diffForHumans(short: true) }}</u>
                    </span>
                @endforeach
                @foreach ($liveTrades as $lt)
                    <span class="cp-tick {{ $lt->action }}" aria-hidden="true">
                        <b>{{ $lt->action === 'buy' ? 'BUY' : 'SELL' }}</b>
                        {{ $lt->symbol }}
                        <i>${{ rtrim(rtrim(number_format((float) $lt->total_usd, 0), '0'), ',') }}</i>
                        <em>{{ $lt->trader_name }}</em>
                        <u>{{ $lt->executed_at?->diffForHumans(short: true) }}</u>
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    @if ($featured->isNotEmpty() && $filter === 'all' && $search === '')
        <div class="cp-sec-title" id="cp-browse"><i class="ri-star-line"></i>Featured Traders</div>
        <div class="cp-featured">
            @foreach ($featured as $t)
                <a href="/copy-trading/{{ $t->slug }}" class="fx-card cp-fcard" style="--cp-hue:{{ ($t->id * 47) % 360 }}">
                    <div class="cp-banner">
                        <div class="cp-banner-spark">
                            @php $h = $t->stats_history ?? []; @endphp
                            @if (count($h) >= 2)
                                @php
                                    $mn = min($h); $mx = max($h); $sp = max($mx - $mn, 0.01);
                                    $fp = [];
                                    foreach ($h as $i => $v) { $fp[] = round(($i / (count($h) - 1)) * 120, 1) . ',' . round(34 - (($v - $mn) / $sp) * 30 - 2, 1); }
                                @endphp
                                <svg viewBox="0 0 120 34" preserveAspectRatio="none"><polyline points="{{ implode(' ', $fp) }}" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="1.6"/></svg>
                            @endif
                        </div>
                    </div>
                    <div class="cp-fbody">
                        <div class="cp-avatar"><img src="{{ asset($t->avatar_url) }}" alt="" onerror="this.style.display='none'"></div>
                        <div class="cp-name-row">
                            <span class="cp-name">{{ $t->display_name }}</span>
                            @if ($t->is_verified)<i class="ri-verified-badge-fill cp-verified"></i>@endif
                        </div>
                        <div class="cp-tagline">{{ $t->tagline }}</div>
                        <div class="cp-fstats">
                            <div><b class="{{ $t->pnl_color }}">{{ $t->total_pnl_usd >= 0 ? '+' : '' }}${{ rtrim(rtrim(number_format(abs((float) $t->total_pnl_usd), 0), '0'), ',') }}</b><span>30d PnL</span></div>
                            <div><b class="{{ $t->roi_30d >= 0 ? 'fx-up' : 'fx-down' }}">{{ number_format((float) $t->roi_30d, 1) }}%</b><span>ROI</span></div>
                            <div><b>{{ number_format((float) $t->win_rate, 1) }}%</b><span>Win</span></div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="cp-controls">
        <div class="cp-tabs">
            @foreach ([
                'all' => 'All', 'featured' => 'Featured', 'verified' => 'Verified',
                'top-pnl' => 'Top PnL', 'top-roi' => 'Top ROI', 'winrate' => 'Win Rate',
                'low-risk' => 'Low Risk', 'high-risk' => 'High Risk', 'new' => 'New',
            ] as $f => $label)
                <a href="{{ route('copy-trading.index', array_filter(['filter' => $f === 'all' ? null : $f, 'q' => $search ?: null])) }}" class="cp-tab {{ $filter === $f ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="cp-tools">
            <form method="GET" action="/copy-trading" class="cp-search">
                @if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <i class="ri-search-line"></i>
                <input class="fx-input" type="text" name="q" value="{{ $search }}" placeholder="Search traders…">
            </form>
            <form method="GET" action="/copy-trading" class="cp-sort">
                @if ($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
                @if ($search !== '')<input type="hidden" name="q" value="{{ $search }}">@endif
                <select class="fx-input" name="sort" onchange="this.form.submit()">
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Most Followed</option>
                    <option value="pnl" {{ $sort === 'pnl' ? 'selected' : '' }}>Highest PnL</option>
                    <option value="roi" {{ $sort === 'roi' ? 'selected' : '' }}>Highest ROI</option>
                    <option value="winrate" {{ $sort === 'winrate' ? 'selected' : '' }}>Win Rate</option>
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                </select>
            </form>
        </div>
    </div>

    @if ($traders->isEmpty())
        <div class="fx-panel">
            <div class="fx-empty"><i class="ri-user-unfollow-line"></i>No traders match this view.</div>
        </div>
    @else
        <div class="cp-grid">
            @foreach ($traders as $t)
                <a href="/copy-trading/{{ $t->slug }}" class="fx-card cp-card" style="--cp-hue:{{ ($t->id * 47) % 360 }}">
                    <div class="cp-card-head">
                        <div class="cp-avatar sm"><img src="{{ asset($t->avatar_url) }}" alt="" onerror="this.style.display='none'"></div>
                        <div style="flex:1;min-width:0">
                            <div class="cp-name-row">
                                <span class="cp-name">{{ $t->display_name }}</span>
                                @if ($t->is_verified)<i class="ri-verified-badge-fill cp-verified"></i>@endif
                            </div>
                            <div class="cp-tagline clamp-1">{{ $t->tagline }}</div>
                        </div>
                        @if ($t->is_featured)<span class="cp-featured-pill"><i class="ri-star-fill"></i></span>@endif
                    </div>

                    <div class="cp-card-spark">
                        @php $ch = $t->stats_history ?? []; @endphp
                        @if (count($ch) >= 2)
                            @php
                                $cmn = min($ch); $cmx = max($ch); $csp = max($cmx - $cmn, 0.01);
                                $cp = [];
                                foreach ($ch as $ci => $cv) { $cp[] = round(($ci / (count($ch) - 1)) * 240, 1) . ',' . round(40 - (($cv - $cmn) / $csp) * 34 - 3, 1); }
                                $cup = $ch[count($ch) - 1] >= $ch[0];
                            @endphp
                            <svg viewBox="0 0 240 40" preserveAspectRatio="none">
                                <polyline points="{{ implode(' ', $cp) }}" fill="none" stroke="{{ $cup ? '#2FE3AC' : '#FF5C7A' }}" stroke-width="1.6" stroke-linejoin="round"/>
                            </svg>
                        @endif
                        <span class="cp-spark-label {{ $t->roi_30d >= 0 ? 'fx-up' : 'fx-down' }}">{{ $t->roi_30d >= 0 ? '▲' : '▼' }} {{ number_format((float) $t->roi_30d, 1) }}% 30d</span>
                    </div>

                    <div class="cp-metrics">
                        <div class="cp-metric"><span>30d PnL</span><b class="{{ $t->pnl_color }}">{{ $t->total_pnl_usd >= 0 ? '+' : '-' }}${{ rtrim(rtrim(number_format(abs((float) $t->total_pnl_usd), 0), '0'), ',') }}</b></div>
                        <div class="cp-metric"><span>Win Rate</span><b>{{ number_format((float) $t->win_rate, 1) }}%</b></div>
                        <div class="cp-metric"><span>Trades</span><b>{{ number_format($t->trades_count) }}</b></div>
                    </div>

                    <div class="cp-risk">
                        <div class="cp-risk-lbl"><span class="cp-chip risk-{{ $t->risk_level }}">{{ ucfirst($t->risk_level) }} risk</span><span class="cp-followers"><i class="ri-group-line"></i>{{ number_format($t->followers_count) }} copying</span></div>
                        <div class="fx-bar"><span style="width:{{ $t->risk_score }}%"></span></div>
                    </div>

                    <div class="cp-foot">
                        <span class="cp-cta">View profile &amp; trades <i class="ri-arrow-right-line"></i></span>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($traders->hasPages())
            <div class="cp-pager">
                @if ($traders->onFirstPage())
                    <span class="cp-page is-disabled"><i class="ri-arrow-left-s-line"></i></span>
                @else
                    <a class="cp-page" href="{{ $traders->previousPageUrl() }}"><i class="ri-arrow-left-s-line"></i></a>
                @endif
                <span class="cp-page-info">Page {{ $traders->currentPage() }} of {{ $traders->lastPage() }}</span>
                @if ($traders->hasMorePages())
                    <a class="cp-page" href="{{ $traders->nextPageUrl() }}"><i class="ri-arrow-right-line"></i></a>
                @else
                    <span class="cp-page is-disabled"><i class="ri-arrow-right-line"></i></span>
                @endif
            </div>
        @endif
    @endif
</div>

<style>
.cp-hero { position:relative; border:1px solid var(--primary-stroke); border-radius:18px; overflow:hidden; background:var(--bg-secondary); }
.cp-hero-glow { position:absolute; inset:0; background:
    radial-gradient(700px 200px at 15% -30%, rgba(59,130,246,.28), transparent 65%),
    radial-gradient(600px 220px at 85% 120%, rgba(47,227,172,.16), transparent 65%); }
.cp-hero-inner { position:relative; display:flex; gap:28px; padding:34px 30px; flex-wrap:wrap; align-items:center; }
.cp-hero-copy { flex:1 1 380px; min-width:280px; }
.cp-eyebrow { display:inline-flex; align-items:center; gap:8px; font-size:11.5px; font-weight:650; text-transform:uppercase; letter-spacing:.07em; color:var(--blue); background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.3); padding:5px 11px; border-radius:999px; }
.cp-live-dot { width:7px; height:7px; border-radius:50%; background:var(--up); box-shadow:0 0 6px var(--up); animation:cpulse 1.6s infinite; }
@keyframes cpulse { 0%,100%{opacity:1} 50%{opacity:.35} }
.cp-hero-copy h1 { font-size:34px; line-height:1.12; font-weight:800; color:var(--text-primary); margin:16px 0 12px; letter-spacing:-.02em; }
.cp-gradient-text { background:linear-gradient(90deg,#5AA9FF,#2FE3AC); -webkit-background-clip:text; background-clip:text; color:transparent; }
.cp-hero-copy p { font-size:14.5px; color:var(--text-secondary); line-height:1.6; max-width:520px; }
.cp-hero-ctas { display:flex; gap:10px; margin-top:20px; flex-wrap:wrap; }
.cp-hero-btn { background:var(--blue); border-color:var(--blue); font-weight:650; }
.cp-hero-stats { flex:0 1 340px; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.cp-hstat { background:rgba(255,255,255,.035); border:1px solid var(--primary-stroke); border-radius:12px; padding:16px 16px; display:flex; flex-direction:column; gap:4px; }
.cp-hstat b { font-size:22px; font-weight:800; color:var(--text-primary); letter-spacing:-.01em; }
.cp-hstat span { font-size:10px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-tertiary); }
.cp-ticker { display:flex; align-items:stretch; gap:0; border:1px solid var(--primary-stroke); border-radius:12px; background:var(--bg-secondary); overflow:hidden; }
.cp-ticker-label { flex:none; display:flex; align-items:center; gap:7px; padding:0 15px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--up); background:rgba(47,227,172,.07); border-right:1px solid var(--primary-stroke); white-space:nowrap; }
.cp-ticker-track { flex:1; overflow:hidden; padding:11px 0; min-width:0; -webkit-mask-image:linear-gradient(90deg,transparent,#000 4%,#000 96%,transparent); mask-image:linear-gradient(90deg,transparent,#000 4%,#000 96%,transparent); }
.cp-ticker-row { display:flex; gap:26px; width:max-content; animation:cscroll 42s linear infinite; padding-left:26px; }
.cp-ticker-row:hover { animation-play-state:paused; }
@keyframes cscroll { from{transform:translateX(0)} to{transform:translateX(-50%)} }
.cp-tick { display:inline-flex; align-items:center; gap:7px; font-size:12px; color:var(--text-secondary); white-space:nowrap; }
.cp-tick b { font-size:10px; font-weight:800; padding:2px 7px; border-radius:5px; letter-spacing:.05em; }
.cp-tick.buy b { background:rgba(47,227,172,.13); color:var(--up); }
.cp-tick.sell b { background:rgba(255,92,122,.13); color:#FF5C7A; }
.cp-tick i { font-style:normal; color:var(--text-primary); font-weight:650; }
.cp-tick em { font-style:normal; color:var(--blue); }
.cp-tick u { text-decoration:none; color:var(--text-tertiary); font-size:11px; }
.cp-sec-title { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:650; color:var(--text-primary); }
.cp-sec-title i { color:#F5C542; }
.cp-featured { display:grid; gap:12px; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); }
.cp-fcard { padding:0; overflow:hidden; position:relative; display:block; color:inherit; text-decoration:none; transition:transform .12s ease,border-color .12s ease; }
.cp-fcard:hover { transform:translateY(-3px); border-color:rgba(59,130,246,.45); }
.cp-banner { height:64px; background:linear-gradient(120deg,hsl(var(--cp-hue) 70% 45%),hsl(calc(var(--cp-hue) + 60) 70% 28%)); position:relative; }
.cp-banner-spark { position:absolute; inset:6px 8px; opacity:.7; }
.cp-banner-spark svg { width:100%; height:100%; }
.cp-fbody { padding:0 14px 14px; }
.cp-avatar { width:46px; height:46px; border-radius:12px; margin-top:-23px; margin-bottom:8px; background:linear-gradient(135deg,hsl(var(--cp-hue) 75% 52%),hsl(calc(var(--cp-hue) + 50) 75% 38%)); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:#fff; border:2px solid var(--bg-secondary); overflow:hidden; position:relative; z-index:1; }
.cp-avatar img { width:100%; height:100%; object-fit:cover; }
.cp-avatar.sm { width:38px; height:38px; border-radius:10px; margin:0; font-size:15px; border:none; flex:none; }
.cp-name-row { display:flex; align-items:center; gap:6px; min-width:0; }
.cp-name { font-size:14px; font-weight:700; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cp-verified { color:var(--blue); font-size:14px; flex:none; }
.cp-tagline { font-size:12px; color:var(--text-tertiary); margin-top:2px; line-height:1.35; }
.clamp-1 { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cp-fstats { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:12px; }
.cp-fstats div { display:flex; flex-direction:column; gap:2px; }
.cp-fstats b { font-size:14px; font-weight:700; color:var(--text-primary); }
.cp-fstats span { font-size:9.5px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-tertiary); }
.cp-controls { display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between; }
.cp-tabs { display:flex; gap:6px; flex-wrap:wrap; }
.cp-tab { font-size:12.5px; padding:7px 12px; border-radius:8px; border:1px solid var(--primary-stroke); color:var(--text-secondary); text-decoration:none; background:var(--bg-secondary); white-space:nowrap; }
.cp-tab:hover { color:var(--text-primary); border-color:var(--secondary-stroke); }
.cp-tab.is-active { background:rgba(59,130,246,.12); border-color:rgba(59,130,246,.45); color:var(--blue); font-weight:650; }
.cp-tools { display:flex; gap:8px; flex-wrap:wrap; }
.cp-search { position:relative; display:flex; align-items:center; }
.cp-search i { position:absolute; left:11px; color:var(--text-tertiary); font-size:14px; pointer-events:none; }
.cp-search .fx-input { padding-left:32px; width:210px; }
.cp-sort .fx-input { width:160px; }
.cp-grid { display:grid; gap:12px; grid-template-columns:repeat(auto-fill,minmax(272px,1fr)); }
.cp-card { display:flex; flex-direction:column; gap:11px; text-decoration:none; color:inherit; transition:transform .12s ease,border-color .12s ease; }
.cp-card:hover { transform:translateY(-2px); border-color:rgba(59,130,246,.4); }
.cp-card-head { display:flex; gap:10px; align-items:flex-start; }
.cp-card-spark { position:relative; background:rgba(255,255,255,.03); border:1px solid var(--bg-tertiary); border-radius:9px; padding:8px 10px 6px; }
.cp-card-spark svg { width:100%; height:40px; display:block; }
.cp-spark-label { position:absolute; top:7px; right:9px; font-size:11px; font-weight:700; }
.cp-featured-pill { flex:none; width:24px; height:24px; border-radius:7px; background:rgba(245,197,66,.14); color:#F5C542; display:flex; align-items:center; justify-content:center; font-size:12px; }
.cp-chip { font-size:10.5px; padding:3px 8px; border-radius:6px; background:var(--bg-tertiary); color:var(--text-tertiary); font-weight:600; }
.cp-chip.risk-low { background:rgba(47,227,172,.12); color:var(--up); }
.cp-chip.risk-medium { background:rgba(245,197,66,.12); color:#F5C542; }
.cp-chip.risk-high { background:rgba(255,92,122,.12); color:#FF5C7A; }
.cp-metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.cp-metric { display:flex; flex-direction:column; gap:2px; }
.cp-metric span { font-size:9.5px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-tertiary); }
.cp-metric b { font-size:14px; font-weight:700; color:var(--text-primary); }
.cp-risk { display:flex; flex-direction:column; gap:7px; }
.cp-risk-lbl { display:flex; align-items:center; justify-content:space-between; gap:8px; }
.cp-followers { font-size:11px; color:var(--text-tertiary); }
.cp-followers i { margin-right:4px; }
.cp-risk .fx-bar span { background:linear-gradient(90deg,var(--up),#F5C542,#FF5C7A); }
.cp-foot { margin-top:auto; }
.cp-cta { color:var(--blue); font-weight:650; font-size:12.5px; }
.cp-pager { display:flex; align-items:center; justify-content:center; gap:14px; }
.cp-page { width:34px; height:34px; border-radius:9px; border:1px solid var(--primary-stroke); background:var(--bg-secondary); color:var(--text-secondary); display:flex; align-items:center; justify-content:center; text-decoration:none; }
.cp-page:hover { color:var(--text-primary); border-color:var(--blue); }
.cp-page.is-disabled { opacity:.4; pointer-events:none; }
.cp-page-info { font-size:12.5px; color:var(--text-tertiary); }
@media (max-width:640px){
    .cp-hero-inner{padding:24px 18px}
    .cp-hero-copy h1{font-size:26px}
    .cp-hero-stats{grid-template-columns:1fr 1fr;flex:1 1 100%}
    .cp-search .fx-input{width:150px}
    .cp-sort .fx-input{width:130px}
}
</style>
@endsection
