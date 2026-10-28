<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veridian | Pulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}?v={{ @filemtime(public_path('css/app-shell.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/axiom-magic.css') }}?v={{ @filemtime(public_path('css/axiom-magic.css')) }}">
</head>
@php
    $asset  = 'https://axiom-assets-v2.axiom-cdn.io/images';
    $chainArt = [
        'sol'       => "$asset/sol-fill.svg",
        'robinhood' => "$asset/robinhood-logo.svg",
        'arc'       => "$asset/arc.svg",
        'bnb'       => "$asset/bnb-fill.svg",
        'ink'       => "$asset/ink.png",
        'hype'      => "$asset/hype-fill.svg",
        'base'      => "$asset/base.svg",
        'eth'       => "$asset/eth-fill.svg",
    ];
    $chainColor = [
        'sol' => '#526FFF', 'robinhood' => '#BF0DF5', 'arc' => '#2775CA', 'bnb' => '#F0B90B',
        'ink' => '#7132F5', 'hype' => '#97FCE4', 'base' => '#0052FF', 'eth' => '#627EEA',
    ];
    $chainName = [
        'sol' => 'Solana', 'robinhood' => 'Robinhood', 'arc' => 'Arc', 'bnb' => 'BNB',
        'ink' => 'Ink', 'hype' => 'HyperEVM', 'base' => 'Base', 'eth' => 'Ethereum',
    ];
    $pm = "$asset/protocol-marks/v1";

    $navQuery = '?chains=sol,robinhood,arc,bnb,ink,hype,base,eth&pulseChains=sol,robinhood,arc,bnb,ink,hype,base,eth&trackerChains=sol,robinhood,arc,bnb,ink,hype,base,eth&discoverChains=sol,robinhood,arc,bnb,ink,hype,base,eth';

    $sample = [
        [
            'sym' => 'CHESS', 'name' => 'Chess', 'chain' => 'sol', 'dex' => 'Pump.fun', 'badge' => 'pump',
            'age' => '6s', 'img' => "$asset/pfps/O_pfp.webp", 'native' => 'SOL',
            'address' => '9DVsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAApump', 'addressShort' => '9DVs…pump',
            'mc' => '$8.4K', 'pct' => '+0.00%', 'up' => true, 'vol' => '$1.2K', 'liq' => '$4.1K',
            'txns' => '42', 'buys' => '31', 'sells' => '11', 'price' => '$0.0000084',
            'progress' => 4, 'twitter' => true, 'web' => false,
        ],
        [
            'sym' => 'wind', 'name' => 'Wind', 'chain' => 'sol', 'dex' => 'Pump.fun', 'badge' => 'pump',
            'age' => '2s', 'img' => "$asset/pfps/O_pfp.webp", 'native' => 'SOL',
            'address' => 'BmF6dwpuAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA9pump', 'addressShort' => 'BmF6…pump',
            'mc' => '$6.1K', 'pct' => '+12.70%', 'up' => true, 'vol' => '$840', 'liq' => '$2.8K',
            'txns' => '18', 'buys' => '14', 'sells' => '4', 'price' => '$0.0000061',
            'progress' => 2, 'twitter' => true, 'web' => true,
        ],
        [
            'sym' => 'TOAD', 'name' => 'Toad', 'chain' => 'sol', 'dex' => 'Pump.fun', 'badge' => 'pump',
            'age' => '48s', 'img' => "$asset/pfps/O_pfp.webp", 'native' => 'SOL',
            'address' => 'HoF34xUnAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQDNpump', 'addressShort' => 'HoF3…pump',
            'mc' => '$21.7K', 'pct' => '-3.41%', 'up' => false, 'vol' => '$9.4K', 'liq' => '$8.2K',
            'txns' => '176', 'buys' => '102', 'sells' => '74', 'price' => '$0.0000217',
            'progress' => 11, 'twitter' => true, 'web' => false,
        ],
    ];

    $pulse = $pulse ?? [];
    $tabs = [
        'new'      => ! empty($pulse['new'])      ? $pulse['new']      : $sample,
        'final'    => ! empty($pulse['final'])    ? $pulse['final']    : $sample,
        'migrated' => ! empty($pulse['migrated']) ? $pulse['migrated'] : $sample,
    ];
    $tokens = $tabs['new'];
@endphp
<body class="platform-body">
    <div class="platform-app-shell mg-content">
        <div class="platform-shell-row">
            <div class="platform-col">

                @include('partials.top-nav', ['active' => 'pulse'])
                @include('partials.ticker', ['tokens' => $tokens])

                {{-- ================= MAIN ================= --}}
                <main class="platform-main-content">
                    <div class="platform-layout-container pulse-layout">
                        <section class="pulse-multi">
                            {{-- Pulse control bar --}}
                            <div class="pm-bar">
                                <strong class="pm-title">Pulse</strong>
                                <a class="pm-networks" href="/discover{{ $navQuery }}" aria-label="Networks">
                                    @foreach ($chainArt as $key => $art)
                                        <img src="{{ $art }}" alt="{{ $chainName[$key] }}" width="27" height="27">
                                    @endforeach
                                </a>
                                <a class="pm-walletgroup" href="/portfolio"><i class="ri-add-line"></i><span>Create a wallet group</span></a>
                                <div class="pm-spacer"></div>
                                <a class="pm-tool" href="https://docs.axiom.trade" target="_blank" rel="noopener" aria-label="Help"><i class="ri-question-line"></i></a>
                                <button class="pm-display" type="button" data-density-toggle><i class="ri-layout-grid-fill"></i><span>Display</span></button>
                                <a class="pm-tool" href="/vision" aria-label="Settings"><i class="ri-settings-3-line"></i></a>
                                <a class="pm-tool" href="/trackers" aria-label="Hide tokens"><i class="ri-eye-off-line"></i></a>
                                <a class="pm-tool" href="/portfolio" aria-label="History"><i class="ri-history-line"></i></a>
                                <a class="pm-wallet" href="/portfolio">
                                    <i class="ri-wallet-line"></i><span>1</span>
                                    <img src="{{ $chainArt['sol'] }}" alt="SOL" width="14" height="14"><span>0</span>
                                    <i class="ri-arrow-down-s-line"></i>
                                </a>
                            </div>

                            {{-- Three feeds side by side --}}
                            <div class="pm-feeds">
                                @foreach (['new' => 'New Pairs', 'final' => 'Final Stretch', 'migrated' => 'Migrated'] as $key => $label)
                                    <section class="pm-feed" data-feed="{{ $key }}">
                                        <div class="pm-feed-toolbar">
                                            <strong class="pm-feed-title">{{ $label }}</strong>
                                            <label class="pm-search">
                                                <i class="ri-search-2-line"></i>
                                                <input type="text" placeholder="Search by ticker" aria-label="Search {{ $label }}">
                                            </label>
                                            <div class="pm-qb" data-quickbuy>
                                                <i class="ri-flashlight-fill flash"></i>
                                                <input type="text" value="0" placeholder="0.0" aria-label="Quick buy amount">
                                                <a class="pm-qb-chain" href="/discover" aria-label="Trade chain"><img class="native" src="{{ $chainArt['sol'] }}" alt="SOL" width="14" height="14"></a>
                                                <button type="button" class="pso" data-amt="0.1">P1</button>
                                                <button type="button" class="pso" data-amt="0.5">P2</button>
                                                <button type="button" class="pso" data-amt="1">P3</button>
                                            </div>
                                            <button class="iconbtn" type="button" data-feed-hide aria-label="Hide tokens"><i class="ri-eye-off-line"></i></button>
                                            <a class="iconbtn" href="/vision" aria-label="Display"><i class="ri-layout-grid-line"></i></a>
                                        </div>
                                        <div class="pm-feed-scroll">
                                            @forelse ($tabs[$key] as $t)
                                                @include('partials.pulse-row', ['t' => $t])
                                            @empty
                                                <div class="pm-empty">No pairs right now — retrying shortly.</div>
                                            @endforelse
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </section>
                    </div>
                </main>

                @include('partials.status-bar', ['spot' => $spot ?? null])
                @include('partials.mobile-navbar', ['active' => 'pulse'])

            </div>
        </div>
    </div>

    <script>
        (function () {
            // Per-feed search filtering.
            document.querySelectorAll('.pm-feed').forEach(function (feed) {
                var input = feed.querySelector('.pm-search input');
                if (!input) return;
                input.addEventListener('input', function () {
                    var q = input.value.trim().toLowerCase();
                    feed.querySelectorAll('.pm-card').forEach(function (card) {
                        var show = !q || card.textContent.toLowerCase().indexOf(q) !== -1;
                        card.style.display = show ? '' : 'none';
                    });
                });
            });

            // Quick-buy preset buttons fill the amount.
            document.querySelectorAll('.pm-qb').forEach(function (qb) {
                var input = qb.querySelector('input');
                qb.querySelectorAll('.pso').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (!input) return;
                        input.value = btn.getAttribute('data-amt');
                        qb.querySelectorAll('.pso').forEach(function (b) { b.classList.remove('is-active'); });
                        btn.classList.add('is-active');
                    });
                });
            });

            // Hide a feed's rows.
            document.querySelectorAll('[data-feed-hide]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var scroll = btn.closest('.pm-feed').querySelector('.pm-feed-scroll');
                    if (!scroll) return;
                    var hidden = scroll.style.display === 'none';
                    scroll.style.display = hidden ? '' : 'none';
                    btn.querySelector('i').className = hidden ? 'ri-eye-off-line' : 'ri-eye-line';
                });
            });

            // Display density toggle (3 columns <-> stacked rows).
            var display = document.querySelector('.pm-display');
            var feeds = document.querySelector('.pm-feeds');
            if (display && feeds) {
                display.addEventListener('click', function () {
                    feeds.classList.toggle('is-stack');
                });
            }

            // Whole token card is clickable.
            document.querySelectorAll('.pm-card[data-href]').forEach(function (card) {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function (e) {
                    if (e.target.closest('a, button, input')) return;
                    window.location.href = card.getAttribute('data-href');
                });
            });

            // Copy contract address.
            document.querySelectorAll('[data-copy]').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var v = el.getAttribute('data-copy');
                    if (!v || !navigator.clipboard) return;
                    navigator.clipboard.writeText(v).then(function () {
                        var old = el.className;
                        el.className = 'ri-check-line pm-copy';
                        setTimeout(function () { el.className = old; }, 1200);
                    });
                });
            });

            // Lazy holder / safety enrichment (one request per mint, cached 5m server-side).
            var cards = [].slice.call(document.querySelectorAll('.pm-scores[data-mint]'))
                .filter(function (el) { return el.getAttribute('data-mint'); });

            function loadReport(el) {
                var mint = el.getAttribute('data-mint');
                return fetch('/api/pulse/token/' + mint, { headers: { 'Accept': 'application/json' } })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (d) {
                        if (!d) return;
                        function set(sel, txt) { var n = el.querySelector(sel); if (n) n.textContent = txt; }
                        var holders = el.querySelector('.pm-holders');
                        var top10   = el.querySelector('.pm-score--top');
                        var safe    = el.querySelector('.pm-score--safe');
                        if (typeof d.holders === 'number' && holders) set('.pm-holders .val', d.holders.toLocaleString());
                        if (typeof d.top10 === 'number' && d.top10 > 0 && top10) {
                            set('.pm-score--top .val', d.top10 + '%');
                            top10.classList.add(d.top10 >= 50 ? 'bad' : (d.top10 >= 30 ? 'mid' : 'ok'));
                        }
                        if (typeof d.score === 'number' && d.score > 0 && safe) {
                            set('.pm-score--safe .val', d.score);
                            safe.classList.add(d.score >= 60 ? 'ok' : (d.score >= 30 ? 'mid' : 'bad'));
                        }
                        if (Array.isArray(d.risks) && d.risks.length) {
                            var card = el.closest('.pm-card');
                            var logo = card && card.querySelector('.pm-logo');
                            if (logo) logo.style.borderColor = '#ff4b60';
                        }
                    })
                    .catch(function () {});
            }

            (function run(i) {
                if (i >= cards.length) return;
                Promise.all(cards.slice(i, i + 4).map(loadReport)).then(function () { run(i + 4); });
            })(0);
        })();
    </script>
    <script src="{{ asset('js/axiom-magic.js') }}" defer></script>
</body>
</html>
