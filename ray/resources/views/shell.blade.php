<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veridian | {{ $title ?? 'App' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}?v={{ @filemtime(public_path('css/app-shell.css')) }}">
</head>
@php
    $spot   = $spot ?? (function () { try { return app(\App\Services\MarketFeed::class)->spot(); } catch (\Throwable $e) { return []; } })();
    $tokens = $tokens ?? [];
    $active = $active ?? '';
    $links = [
        ['Discover',    'ri-compass-3-line',    '/discover',    'Trending, new pairs and market movers.'],
        ['Pulse',       'ri-pulse-line',        '/pulse',       'New Pairs, Final Stretch and Migrated feeds.'],
        ['Perpetuals',  'ri-exchange-line',     '/perpetuals',  'Leveraged perpetuals on Hyperliquid.'],
        ['Predictions', 'ri-trophy-line',       '/predictions', 'Decentralised prediction markets.'],
        ['Vision',      'ri-eye-line',          '/vision',      'Onchain analytics and insight.'],
        ['Portfolio',   'ri-folder-user-line',  '/portfolio',   'Balances, positions and PnL.'],
        ['Rewards',     'ri-gift-line',         '/rewards',     'Points, referrals and trading rewards.'],
    ];
@endphp
<body class="platform-body">
    <div class="platform-app-shell">
        <div class="platform-shell-row">
            <div class="platform-col">

                @include('partials.top-nav', ['active' => $active])
                @include('partials.ticker', ['tokens' => $tokens])

                {{-- ================= MAIN ================= --}}
                <main class="platform-main-content">
                    <div class="platform-layout-container">
                        <section class="shell-page">
                            <div class="shell-hero">
                                <div class="shell-hero-ic"><i class="{{ $icon ?? 'ri-apps-2-line' }}"></i></div>
                                <div class="shell-hero-text">
                                    <h1 class="shell-hero-title">{{ $title ?? 'Veridian' }}</h1>
                                    <p class="shell-hero-blurb">{{ $blurb ?? '' }}</p>
                                </div>
                            </div>

                            @if (!empty($address))
                                <div class="shell-addr">
                                    <i class="ri-file-copy-line"></i>
                                    <span class="shell-addr-label">Token contract</span>
                                    <code class="shell-addr-code">{{ $address }}</code>
                                    <a class="shell-cta" href="/pulse">View in Pulse</a>
                                </div>
                            @endif

                            <div class="shell-grid">
                                @foreach ($links as $l)
                                    <a class="shell-card {{ $active === ltrim($l[2], '/') ? 'is-active' : '' }}" href="{{ $l[2] }}">
                                        <span class="shell-card-ic"><i class="{{ $l[1] }}"></i></span>
                                        <span class="shell-card-title">{{ $l[0] }}</span>
                                        <span class="shell-card-blurb">{{ $l[3] }}</span>
                                        <i class="ri-arrow-right-line shell-card-go"></i>
                                    </a>
                                @endforeach
                            </div>

                            <div class="shell-ext">
                                <a href="https://docs.axiom.trade" target="_blank" rel="noopener" class="shell-ext-link"><i class="ri-article-line"></i>Docs</a>
                                <a href="https://discord.gg/axiomtrade" target="_blank" rel="noopener" class="shell-ext-link"><i class="ri-discord-fill"></i>Discord</a>
                                <a href="https://x.com/axiomexchange" target="_blank" rel="noopener" class="shell-ext-link"><i class="ri-twitter-x-line"></i>X</a>
                                <a href="https://axiom.trade" target="_blank" rel="noopener" class="shell-ext-link"><i class="ri-global-line"></i>axiom.trade</a>
                            </div>
                        </section>
                    </div>
                </main>

                @include('partials.status-bar', ['spot' => $spot])
                @include('partials.mobile-navbar', ['active' => $active])

            </div>
        </div>
    </div>
</body>
</html>
