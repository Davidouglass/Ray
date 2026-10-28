<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veridian | {{ $meta[$key]['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}?v={{ @filemtime(public_path('css/app-shell.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/axiom-magic.css') }}?v={{ @filemtime(public_path('css/axiom-magic.css')) }}">
    <style>
        .chain-hero { display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; padding: 22px 24px 4px; }
        .chain-hero-left { display: flex; align-items: center; gap: 14px; }
        .chain-hero-badge { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; background: color-mix(in srgb, var(--cc) 16%, transparent); border: 1px solid color-mix(in srgb, var(--cc) 40%, transparent); }
        .chain-hero-badge img { width: 28px; height: 28px; object-fit: contain; border-radius: 50%; }
        .chain-hero h1 { font-size: 22px; font-weight: 700; color: var(--text-primary); margin: 0; letter-spacing: -0.2px; }
        .chain-hero-sub { font-size: 12.5px; color: var(--text-tertiary); margin-top: 3px; display: flex; align-items: center; gap: 8px; }
        .chain-hero-sub .dot { width: 3px; height: 3px; border-radius: 50%; background: var(--text-tertiary); opacity: .6; }
        .chain-switch { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .chain-pill { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 12px 0 8px; border-radius: 999px; border: 1px solid var(--primary-stroke); color: var(--text-secondary); font-size: 13px; text-decoration: none; transition: border-color 120ms ease, background 120ms ease, color 120ms ease; }
        .chain-pill img { width: 18px; height: 18px; border-radius: 50%; object-fit: contain; }
        .chain-pill:hover { border-color: var(--text-tertiary); color: var(--text-primary); }
        .chain-pill.is-active { background: var(--bg-tertiary); border-color: var(--text-tertiary); color: var(--text-primary); font-weight: 600; }
        .chain-empty { padding: 60px 24px; text-align: center; color: var(--text-tertiary); }
        .chain-empty i { font-size: 40px; display: block; margin-bottom: 12px; opacity: .5; }
        .chain-empty h2 { font-size: 16px; color: var(--text-secondary); margin: 0 0 6px; font-weight: 600; }
        .chain-empty p { font-size: 13px; margin: 0 0 16px; }
        .chain-empty a { color: var(--blue); text-decoration: none; font-size: 13px; }
    </style>
</head>
<body class="platform-body">
    <div class="platform-app-shell mg-content">
        <div class="platform-shell-row">
            <div class="platform-col">

                @include('partials.top-nav', ['active' => 'discover'])

                <main class="platform-main-content">
                    <div class="platform-layout-container">
                        <section class="discover-page" style="--cc: {{ $meta[$key]['color'] }}">
                            <div class="chain-hero">
                                <div class="chain-hero-left">
                                    <span class="chain-hero-badge"><img src="{{ $meta[$key]['art'] }}" alt="{{ $meta[$key]['name'] }}"></span>
                                    <div>
                                        <h1>{{ $meta[$key]['name'] }}</h1>
                                        <div class="chain-hero-sub">
                                            <span>{{ count($tokens) }} tokens tracked</span>
                                            <span class="dot"></span>
                                            <span>Native {{ $meta[$key]['native'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <nav class="chain-switch">
                                    @foreach ($meta as $ck => $cm)
                                        <a class="chain-pill {{ $ck === $key ? 'is-active' : '' }}" href="/chain/{{ $ck }}">
                                            <img src="{{ $cm['art'] }}" alt="{{ $cm['name'] }}"><span>{{ $cm['name'] }}</span>
                                        </a>
                                    @endforeach
                                </nav>
                            </div>

                            @if (count($tokens) > 0)
                                <div class="discover-inner">
                                    <div class="discover-table-scroll" data-panel="tokens">
                                        <div class="discover-table">
                                            <div class="disc-thead">
                                                <div class="c-pair"><span>Pair Info</span></div>
                                                <div class="c-graph"></div>
                                                <div class="c-mc th-sort"><span>Market Cap</span></div>
                                                <div class="c-liq th-sort"><span>Liquidity</span></div>
                                                <div class="c-vol th-sort"><span>Volume</span></div>
                                                <div class="c-txns th-sort"><span>TXNS</span></div>
                                                <div class="c-info"><span>Token Info</span></div>
                                                <div class="c-action"><span>Action</span></div>
                                            </div>

                                            <div class="disc-body">
                                                @foreach ($tokens as $t)
                                                @php
                                                    $spark  = $t['spark'] ?? null;
                                                    $line   = $spark['line']   ?? '';
                                                    $area   = $spark['area']   ?? '';
                                                    $stroke = $spark['stroke'] ?? ($t['up'] ? '#2FE3AC' : '#EC397A');
                                                    $fill   = $spark['fill']   ?? ($t['up'] ? '#2FE3AC1A' : '#EC397A1A');
                                                    $cc     = $meta[$t['chain']]['color'] ?? '#526FFF';
                                                @endphp
                                                <div class="disc-row" data-chain="{{ $t['chain'] }}" data-address="{{ $t['address'] ?? '' }}">
                                                    <div class="c-pair">
                                                        <div class="pair-avatar-wrap">
                                                            <div class="pair-avatar">
                                                                <span class="pa-badge" style="background:{{ $cc }}">
                                                                    <span class="pa-badge-inner">
                                                                        <img src="{{ $meta[$t['chain']]['art'] }}" alt="" width="10" height="10">
                                                                        <img src="{{ $t['dexArt'] }}" alt="" width="10" height="10">
                                                                    </span>
                                                                </span>
                                                                <span class="pa-frame" style="background:{{ $cc }}">
                                                                    <span class="pa-frame-inner"><img class="pa-img" src="{{ $t['img'] ?: $meta[$t['chain']]['art'] }}" alt="{{ $t['name'] }}" loading="lazy"></span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="pair-text">
                                                            <div class="pair-name-row">
                                                                @if (!empty($t['address']))<a class="pair-tick pair-tick-link" href="/token/{{ $t['address'] }}">{{ $t['sym'] }}</a>@else<span class="pair-tick">{{ $t['sym'] }}</span>@endif
                                                                <span class="pair-full">{{ $t['name'] }}</span>
                                                                <span class="pair-tax"><i class="ri-percent-line"></i>{{ $t['tax'] }}</span>
                                                            </div>
                                                            <div class="pair-meta">
                                                                <span class="pair-age">{{ $t['age'] }}</span>
                                                                @if ($t['uniswap'])<a href="https://app.uniswap.org" target="_blank" rel="noopener" class="pair-ico"><i class="ri-exchange-line"></i></a>@endif
                                                                @if ($t['quill'])<a href="https://x.com/axiomexchange" target="_blank" rel="noopener" class="pair-ico"><i class="ri-quill-pen-line ico-quill"></i></a>@endif
                                                                @if ($t['globe'])<a href="https://axiom.trade" target="_blank" rel="noopener" class="pair-ico"><i class="ri-global-line"></i></a>@endif
                                                                @if ($t['pump'])<a href="https://pump.fun" target="_blank" rel="noopener" class="pair-ico"><i class="ri-capsule-line"></i></a>@endif
                                                                <a href="/pulse" class="pair-ico"><i class="ri-search-line"></i></a>
                                                                <span class="pair-views"><i class="ri-eye-line"></i>{{ $t['views'] }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="c-graph">
                                                        <div class="spark-wrap">
                                                            <svg class="spark" viewBox="0 0 100 40" preserveAspectRatio="none" aria-hidden="true">
                                                                <path d="{{ $area }}" fill="{{ $fill }}"></path>
                                                                <path d="{{ $line }}" fill="none" stroke="{{ $stroke }}" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"></path>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    <div class="c-mc"><span class="val">{{ $t['mc'] }}</span><span class="chg {{ $t['up'] ? 'up' : 'down' }}">{{ $t['pct'] }}</span></div>
                                                    <div class="c-liq"><span class="val">{{ $t['liq'] }}</span></div>
                                                    <div class="c-vol"><span class="val">{{ $t['vol'] }}</span></div>
                                                    <div class="c-txns">
                                                        <span class="val">{{ $t['txns'] }}</span>
                                                        <span class="chg"><span class="up">{{ $t['buys'] }}</span><span class="slash">/</span><span class="down">{{ $t['sells'] }}</span></span>
                                                    </div>

                                                    <div class="c-info">
                                                        <div class="ti-group">
                                                            @foreach (array_slice($t['cols'], 0, 3) as $col)
                                                                <div class="ti-col">
                                                                    @foreach ($col as $pill)
                                                                        <span class="info-pill is-{{ $pill[2] }}"><i class="{{ $pill[0] }}"></i><span>{{ $pill[1] }}</span></span>
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="ti-col">
                                                            @foreach ($t['cols'][3] as $pill)
                                                                <span class="info-pill is-{{ $pill[2] }}"><i class="{{ $pill[0] }}"></i><span>{{ $pill[1] }}</span></span>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="c-action">
                                                        <button class="buy-btn" type="button" data-native="{{ $t['native'] ?? 'SOL' }}" data-mint="{{ $t['address'] }}" data-chain="{{ $key }}" data-symbol="{{ $t['sym'] }}"><span>Buy</span><span class="buy-amt">0 {{ $t['native'] ?? 'SOL' }}</span></button>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="chain-empty">
                                    <i class="ri-radar-line"></i>
                                    <h2>No live markets for {{ $meta[$key]['name'] }} yet</h2>
                                    <p>We couldn't find any trending pairs on {{ $meta[$key]['name'] }} right now.</p>
                                    <a href="/discover">Browse all chains on Discover &rarr;</a>
                                </div>
                            @endif
                        </section>
                    </div>
                </main>

                @include('partials.status-bar', ['spot' => $spot ?? null])
                @include('partials.mobile-navbar', ['active' => 'discover'])
            </div>
        </div>
    </div>
    <script src="{{ asset('js/axiom-trade.js') }}?v={{ @filemtime(public_path('js/axiom-trade.js')) }}" defer></script>
</body>
</html>
