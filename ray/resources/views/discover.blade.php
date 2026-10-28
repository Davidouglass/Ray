<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veridian | Discover</title>
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
        'hype'      => "$asset/hype.svg",
        'base'      => "$asset/base.svg",
        'eth'       => "$asset/eth-fill.svg",
    ];
    $chainColor = [
        'sol' => '#526FFF', 'robinhood' => '#BF0DF5', 'arc' => '#2775CA', 'bnb' => '#F0B90B',
        'ink' => '#7132F5', 'hype' => '#97FCE4', 'base' => '#0052FF', 'eth' => '#627EEA',
    ];
    $pm = "$asset/protocol-marks/v1";

    $navQuery = '?chains=sol,robinhood,arc,bnb,ink,hype,base,eth&pulseChains=sol,robinhood,arc,bnb,ink,hype,base,eth&trackerChains=sol,robinhood,arc,bnb,ink,hype,base,eth&discoverChains=sol,robinhood,arc,bnb,ink,hype,base,eth';
    $chainName = [
        'sol' => 'Solana', 'robinhood' => 'Robinhood', 'arc' => 'Arc', 'bnb' => 'BNB',
        'ink' => 'Ink', 'hype' => 'HyperEVM', 'base' => 'Base', 'eth' => 'Ethereum',
    ];

    $tokens = [
        [
            'sym' => 'ONDEX', 'name' => 'Ondex', 'chain' => 'eth', 'dex' => 'Uniswap V4',
            'dexArt' => "$asset/evm/protocols/uniswap.svg", 'tax' => '1%', 'age' => '36m', 'views' => '199',
            'img' => "$asset/pfps/O_pfp.webp",
            'quote' => '1.798', 'quoteSym' => 'ETH', 'quoteArt' => "$asset/eth-fill.svg",
            'mc' => '$338K', 'pct' => '+23.84%', 'up' => true, 'liq' => '$26.4K', 'vol' => '$59.4K',
            'txns' => '184', 'buys' => '89', 'sells' => '95', 'paid' => false, 'safe' => false,
            'quill' => false, 'globe' => true, 'pump' => false, 'rocket' => null, 'uniswap' => true,
            'cols' => [
                [['ri-user-star-line','23.2%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0%','green']],
                [['ri-ghost-line','0%','green'],['ri-stack-line','26.62%','red']],
                [['ri-user-line','272','sec'],['ri-leaf-line','13.9%','red'],['ri-vip-crown-line','40.95%','green']],
                [['ri-money-dollar-circle-line','Unpaid','red'],['ri-shield-line','Risk','red']],
            ],
        ],
        [
            'sym' => 'MÄui', 'name' => 'MÄui Dolphin', 'chain' => 'sol', 'dex' => 'Raydium',
            'dexArt' => "$pm/pump-grad-amm-temp.webp", 'tax' => '1.8%', 'age' => '4m', 'views' => '276',
            'img' => 'https://axiomtrading-v2.axiom-cdn.io/EdjizRrYc56fvP4gvgpNQttVcX9Jm1xfUaFEwQUEpump.webp',
            'quote' => null, 'quoteSym' => null, 'quoteArt' => null,
            'mc' => '$48.3K', 'pct' => '+34.48%', 'up' => true, 'liq' => '$19.7K', 'vol' => '$54K',
            'txns' => '702', 'buys' => '397', 'sells' => '305', 'paid' => true, 'safe' => false,
            'quill' => true, 'globe' => true, 'pump' => false, 'rocket' => null, 'uniswap' => false,
            'cols' => [
                [['ri-user-star-line','30.05%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0%','green']],
                [['ri-ghost-line','2.57%','green'],['ri-stack-line','0%','green']],
                [['ri-user-line','360','sec'],['ri-vip-crown-line','195','sec']],
                [['ri-money-dollar-circle-line','Paid','green']],
            ],
        ],
        [
            'sym' => 'qpcsbdef', 'name' => 'asocwojbdfepa', 'chain' => 'sol', 'dex' => 'Raydium',
            'dexArt' => "$pm/pump-grad-amm-temp.webp", 'tax' => '1.2%', 'age' => '24m', 'views' => '393',
            'img' => 'https://axiomtrading-v2.axiom-cdn.io/BmF6dwpuuA67FXBDxZpsf2xP6hk39QLsoPbH4juQpump.webp',
            'quote' => null, 'quoteSym' => null, 'quoteArt' => null,
            'mc' => '$90.6K', 'pct' => '+15.21%', 'up' => true, 'liq' => '$27.4K', 'vol' => '$54.2K',
            'txns' => '639', 'buys' => '347', 'sells' => '292', 'paid' => true, 'safe' => false,
            'quill' => true, 'globe' => false, 'pump' => false, 'rocket' => null, 'uniswap' => false,
            'cols' => [
                [['ri-user-star-line','23.27%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0%','green']],
                [['ri-ghost-line','1.16%','green'],['ri-stack-line','1.16%','green']],
                [['ri-user-line','603','sec'],['ri-vip-crown-line','379','sec']],
                [['ri-money-dollar-circle-line','Paid','green']],
            ],
        ],
        [
            'sym' => 'Comms', 'name' => 'Comms', 'chain' => 'sol', 'dex' => 'Raydium',
            'dexArt' => "$pm/pump.webp", 'tax' => '1.25%', 'age' => '19m', 'views' => '220',
            'img' => 'https://axiomtrading-v2.axiom-cdn.io/HoF34xUnnY7p3dBu2gsw4k4CJK28MBKi61G7QDNipump.webp',
            'quote' => null, 'quoteSym' => null, 'quoteArt' => null,
            'mc' => '$42.8K', 'pct' => '+72.09%', 'up' => true, 'liq' => '$24.5K', 'vol' => '$25.6K',
            'txns' => '392', 'buys' => '239', 'sells' => '153', 'paid' => true, 'safe' => false,
            'quill' => true, 'globe' => true, 'pump' => true, 'rocket' => null, 'uniswap' => false,
            'cols' => [
                [['ri-user-star-line','26.06%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','2.03%','green']],
                [['ri-ghost-line','2.67%','green'],['ri-stack-line','11.06%','red']],
                [['ri-user-line','330','sec'],['ri-vip-crown-line','190','sec']],
                [['ri-money-dollar-circle-line','Paid','green']],
            ],
        ],
        [
            'sym' => 'Mouse', 'name' => 'Theater Mouse', 'chain' => 'robinhood', 'dex' => 'Uniswap V3',
            'dexArt' => "$pm/evm-uniswap-purple.webp", 'tax' => '0.01%', 'age' => '7m', 'views' => '8',
            'img' => 'https://axiom-rh-v2.axiom-cdn.io/0x9dfadba0d09bfe859944fe8ea3403f11f61706ae.webp',
            'quote' => '0.022', 'quoteSym' => 'ETH', 'quoteArt' => "$asset/eth-robinhood-v2.svg",
            'mc' => '$251K', 'pct' => '-21.8%', 'up' => false, 'liq' => '$22.5K', 'vol' => '$418K',
            'txns' => '319', 'buys' => '227', 'sells' => '92', 'paid' => false, 'safe' => true,
            'quill' => true, 'globe' => false, 'pump' => false, 'rocket' => null, 'uniswap' => false,
            'imgCount' => '10',
            'cols' => [
                [['ri-user-star-line','14.73%','green'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','10.01%','red']],
                [['ri-ghost-line','0%','green'],['ri-stack-line','0%','green'],['ri-fire-line','???','red']],
                [['ri-user-line','5986','sec'],['ri-leaf-line','0%','green'],['ri-vip-crown-line','0.02%','red']],
                [['ri-money-dollar-circle-line','Unpaid','red'],['ri-shield-line','Safe','green']],
            ],
        ],
        [
            'sym' => 'Starblo', 'name' => 'Starblo', 'chain' => 'sol', 'dex' => 'Raydium',
            'dexArt' => "$pm/pump-grad-amm-temp.webp", 'tax' => '1.25%', 'age' => '1m', 'views' => '398',
            'img' => 'https://axiomtrading-v2.axiom-cdn.io/32oavsRr4YuBkpFNgXK5atCYaCEKkbWhotsLSLCnpump.webp',
            'quote' => null, 'quoteSym' => null, 'quoteArt' => null,
            'mc' => '$42.4K', 'pct' => '+168.5%', 'up' => true, 'liq' => '$18.1K', 'vol' => '$50.5K',
            'txns' => '856', 'buys' => '504', 'sells' => '380', 'paid' => false, 'safe' => false,
            'quill' => true, 'globe' => true, 'pump' => true, 'rocket' => '3:21', 'uniswap' => false,
            'cols' => [
                [['ri-user-star-line','19.91%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0%','green']],
                [['ri-ghost-line','9.87%','red'],['ri-stack-line','26.62%','red']],
                [['ri-user-line','431','sec'],['ri-vip-crown-line','162','sec']],
                [['ri-money-dollar-circle-line','Unpaid','red']],
            ],
        ],
        [
            'sym' => 'QI', 'name' => 'Quantum Inu', 'chain' => 'sol', 'dex' => 'Raydium',
            'dexArt' => "$pm/pump.webp", 'tax' => '0.7%', 'age' => '19h', 'views' => '171',
            'img' => 'https://axiomtrading-v2.axiom-cdn.io/8TiMkgvsrat9tM2esko8zVTt99LZLpefUM4SnZaziXaQ.webp',
            'quote' => null, 'quoteSym' => null, 'quoteArt' => null,
            'mc' => '$4.19M', 'pct' => '-2.13%', 'up' => false, 'liq' => '$222K', 'vol' => '$67.5K',
            'txns' => '884', 'buys' => '504', 'sells' => '380', 'paid' => true, 'safe' => false,
            'quill' => true, 'globe' => true, 'pump' => false, 'rocket' => null, 'uniswap' => false,
            'cols' => [
                [['ri-user-star-line','17.98%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0.04%','green']],
                [['ri-ghost-line','8.77%','red'],['ri-stack-line','26.62%','green']],
                [['ri-user-line','10037','sec'],['ri-vip-crown-line','5030','sec']],
                [['ri-money-dollar-circle-line','Paid','green']],
            ],
        ],
        [
            'sym' => 'quant', 'name' => 'qunat', 'chain' => 'sol', 'dex' => 'Raydium',
            'dexArt' => "$pm/pump.webp", 'tax' => '1.25%', 'age' => '2m', 'views' => '164',
            'img' => 'https://axiomtrading-v2.axiom-cdn.io/81mLv1THKHMWW3NrH3WX1RjZvWFr1QxgGLeaHzEipump.webp',
            'quote' => null, 'quoteSym' => null, 'quoteArt' => null,
            'mc' => '$9.84K', 'pct' => '+37.34%', 'up' => true, 'liq' => '$11.8K', 'vol' => '$22.6K',
            'txns' => '430', 'buys' => '251', 'sells' => '179', 'paid' => false, 'safe' => false,
            'quill' => true, 'globe' => true, 'pump' => true, 'rocket' => null, 'uniswap' => false,
            'cols' => [
                [['ri-user-star-line','21.01%','red'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0.54%','green']],
                [['ri-ghost-line','0.03%','green'],['ri-stack-line','32.52%','red']],
                [['ri-user-line','92','sec'],['ri-vip-crown-line','46','sec']],
                [['ri-money-dollar-circle-line','Unpaid','red']],
            ],
        ],
        [
            'sym' => 'QI', 'name' => 'Quantum Inu', 'chain' => 'robinhood', 'dex' => 'Uniswap V3',
            'dexArt' => "$pm/evm-uniswap-purple.webp", 'tax' => '0.01%', 'age' => '12h', 'views' => '6',
            'img' => 'https://axiom-rh-v2.axiom-cdn.io/0xb862389bf1ad271c70edbc2249bf1b0a6cd8783b.webp',
            'quote' => '0.198', 'quoteSym' => 'ETH', 'quoteArt' => "$asset/eth-robinhood-v2.svg",
            'mc' => '$15M', 'pct' => '-3.11%', 'up' => false, 'liq' => '$191K', 'vol' => '$71.9K',
            'txns' => '268', 'buys' => '176', 'sells' => '92', 'paid' => false, 'safe' => true,
            'quill' => false, 'globe' => true, 'pump' => false, 'rocket' => null, 'uniswap' => true,
            'imgCount' => '237',
            'cols' => [
                [['ri-user-star-line','0.65%','green'],['ri-restaurant-2-line','0%','green'],['ri-crosshair-2-line','0%','green']],
                [['ri-ghost-line','0%','green'],['ri-stack-line','0%','green'],['ri-fire-line','???','red']],
                [['ri-user-line','5986','sec'],['ri-leaf-line','0%','green'],['ri-vip-crown-line','0.02%','red']],
                [['ri-money-dollar-circle-line','Unpaid','red'],['ri-shield-line','Safe','green']],
            ],
        ],
    ];

    $sparkUp    = 'M2,38 C14,30 22,34 32,22 C42,14 50,26 60,16 C70,8 80,12 98,4 L98,39.2 L2,39.2 Z';
    $sparkUpL   = 'M2,38 C14,30 22,34 32,22 C42,14 50,26 60,16 C70,8 80,12 98,4';
    $sparkDown  = 'M2,8 C14,10 22,18 32,16 C42,22 50,20 60,28 C70,32 80,30 98,36 L98,39.2 L2,39.2 Z';
    $sparkDownL = 'M2,8 C14,10 22,18 32,16 C42,22 50,20 60,28 C70,32 80,30 98,36';

    // Live rows from DexScreener; falls back to the sample set above.
    if (! empty($liveTokens ?? [])) {
        $tokens = $liveTokens;
    }
@endphp
<body class="platform-body">
    <div class="platform-app-shell mg-content">
        <div class="platform-shell-row">
            <div class="platform-col">

                @include('partials.top-nav', ['active' => 'discover'])

                @include('partials.ticker', ['tokens' => []])

                {{-- ================= MAIN ================= --}}
                <main class="platform-main-content">
                    <div class="platform-layout-container">

                        {{-- ---------- DESKTOP DISCOVER ---------- --}}
                        <section class="discover-page desktop-only" data-perf-id="discover-trending">
                            <div class="discover-inner">
                                <div class="discover-bar">
                                    <div class="disc-tabs">
                                        @foreach ([['Top','top'],['Trending','trending'],['Radar','radar'],['New','new'],['Callouts','callouts'],['Streamers','streamers']] as $t)
                                            <button class="disc-tab {{ $t[1] === 'trending' ? 'is-active' : '' }}" type="button" data-tab="{{ $t[1] }}">
                                                <span class="disc-tab-label">{{ $t[0] }}</span>
                                                @if (in_array($t[1], ['radar','new','streamers']))
                                                    <span class="disc-new">New</span>
                                                @endif
                                                @if ($t[1] === 'trending')
                                                    <i class="ri-arrow-down-s-line disc-tab-caret"></i>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="disc-right">
                                        <div class="disc-times">
                                            @foreach (['new' => 'New', '1m' => '1m', '5m' => '5m', '30m' => '30m', '1h' => '1h', '24h' => '24h'] as $v => $lbl)
                                                <button class="disc-time {{ $v === '24h' ? 'is-active' : '' }}" type="button" data-tf="{{ $v }}">{{ $lbl }}</button>
                                            @endforeach
                                        </div>
                                        <button class="disc-icon" type="button" aria-label="Settings"><i class="ri-settings-3-line"></i></button>
                                        <button class="disc-icon" type="button" aria-label="Hide"><i class="ri-eye-off-line"></i></button>
                                        <button class="disc-timer" type="button" aria-label="Buy delay"><i class="ri-timer-line"></i><i class="ri-arrow-down-s-line"></i></button>
                                        <button class="qb-wallet" type="button">
                                            <i class="ri-wallet-line"></i>
                                            <span class="qb-wallet-num">1</span>
                                            <img src="{{ $chainArt['sol'] }}" alt="SOL" width="16" height="16">
                                            <span>0</span>
                                            <i class="ri-arrow-down-s-line"></i>
                                        </button>
                                        <div class="qb-field">
                                            <span class="qb-label">Quick Buy</span>
                                            <input type="text" value="0" placeholder="0.0" aria-label="Quick buy amount">
                                            <button class="qb-chain" type="button"><img src="{{ $chainArt['sol'] }}" alt="SOL" width="16" height="16"><i class="ri-arrow-down-s-line"></i></button>
                                            <div class="qb-presets">
                                                <button class="qb-p is-active" type="button" data-amount="0.1">P1</button>
                                                <button class="qb-p" type="button" data-amount="0.5">P2</button>
                                                <button class="qb-p" type="button" data-amount="1">P3</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                                $line   = $spark['line']   ?? ($t['up'] ? $sparkUpL : $sparkDownL);
                                                $area   = $spark['area']   ?? ($t['up'] ? $sparkUp : $sparkDown);
                                                $stroke = $spark['stroke'] ?? ($t['up'] ? '#2FE3AC' : '#EC397A');
                                                $fill   = $spark['fill']   ?? ($t['up'] ? '#2FE3AC1A' : '#EC397A1A');
                                                $cc     = $chainColor[$t['chain']];
                                            @endphp
                                            <div class="disc-row" data-chain="{{ $t['chain'] }}" data-age="{{ $t['age'] ?? '' }}" data-address="{{ $t['address'] ?? '' }}">
                                                <div class="c-pair">
                                                    <div class="pair-avatar-wrap">
                                                        @if (!empty($t['imgCount']))
                                                            <span class="pa-count"><span>{{ $t['imgCount'] }}</span><i class="ri-image-circle-line"></i></span>
                                                        @endif
                                                        <div class="pair-avatar">
                                                            <span class="pa-badge" style="background:{{ $cc }}">
                                                                <span class="pa-badge-inner">
                                                                    <img src="{{ $chainArt[$t['chain']] }}" alt="" width="10" height="10">
                                                                    <img src="{{ $t['dexArt'] }}" alt="" width="10" height="10">
                                                                </span>
                                                            </span>
                                                            <span class="pa-frame" style="background:{{ $cc }}">
                                                                <span class="pa-frame-inner"><img class="pa-img" src="{{ $t['img'] }}" alt="{{ $t['name'] }}" loading="lazy"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="pair-text">
                                                        <div class="pair-name-row">
                                                            @if (!empty($t['address']))<a class="pair-tick pair-tick-link" href="/token/{{ $t['address'] }}">{{ $t['sym'] }}</a>@else<span class="pair-tick">{{ $t['sym'] }}</span>@endif
                                                            <span class="pair-full">{{ $t['name'] }}<i class="ri-file-copy-line"></i></span>
                                                            <span class="pair-tax"><i class="ri-percent-line"></i>{{ $t['tax'] }}</span>
                                                        </div>
                                                        <div class="pair-meta">
                                                            <span class="pair-age">{{ $t['age'] }}</span>
                                                            @if ($t['uniswap'])<a href="https://app.uniswap.org" target="_blank" rel="noopener" class="pair-ico"><i class="ri-exchange-line"></i></a>@endif
                                                            @if ($t['quill'])<a href="https://x.com/axiomexchange" target="_blank" rel="noopener" class="pair-ico"><i class="ri-quill-pen-line ico-quill"></i></a>@endif
                                                            @if ($t['globe'])<a href="https://axiom.trade" target="_blank" rel="noopener" class="pair-ico"><i class="ri-global-line"></i></a>@endif
                                                            @if ($t['pump'])<a href="https://pump.fun" target="_blank" rel="noopener" class="pair-ico"><i class="ri-capsule-line"></i></a>@endif
                                                            @if (!empty($t['rocket']))<span class="pair-rocket"><i class="ri-rocket-2-line"></i>{{ $t['rocket'] }}</span>@endif
                                                            <a href="/pulse" class="pair-ico"><i class="ri-search-line"></i></a>
                                                            @if (!empty($t['quote']))
                                                                <span class="quote-chip" style="color:{{ $cc }};background:{{ $cc }}13">
                                                                    <img src="{{ $t['quoteArt'] }}" alt="{{ $t['quoteSym'] }}" width="16" height="16">
                                                                    <span class="quote-verified"><i class="ri-verified-badge-fill"></i></span>
                                                                </span>
                                                            @endif
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
                                                    <button class="buy-btn" type="button" data-native="{{ $t['native'] ?? ($t['quoteSym'] ?? 'SOL') }}" data-mint="{{ $t['address'] ?? '' }}" data-chain="{{ $t['chain'] ?? 'sol' }}" data-symbol="{{ $t['sym'] }}"><span>Buy</span><span class="buy-amt">0 {{ $t['native'] ?? ($t['quoteSym'] ?? 'SOL') }}</span></button>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="chain-rail">
                                    <button class="cr-all is-active" type="button" data-chain-all>All</button>
                                    <span class="cr-sep"></span>
                                    @foreach ($chainArt as $key => $art)
                                        <button class="cr-item" type="button" data-chain="{{ $key }}" aria-label="Toggle {{ $chainName[$key] }}" style="background:{{ $chainColor[$key] }}26">
                                            <img src="{{ $art }}" alt="{{ $chainName[$key] }}" width="18" height="18">
                                        </button>
                                    @endforeach
                                </div>

                                <div class="disc-panel disc-panel-block" data-panel="callouts" hidden>
                                    @include('partials.callouts')
                                </div>

                                <div class="disc-panel disc-panel-block" data-panel="streamers" hidden>
                                    @include('partials.streamers')
                                </div>

                                @php
                                $venues = [
                                    ['addr'=>'0xa45c…a60e','hash'=>'0xa45c07d183337a7cb8cc6c2038ea9636c045a60e','tag'=>'Launcher','status'=>'New','seen'=>'6d','tokens'=>'16','traders'=>'104','chain'=>'ETH','vol'=>'11.9K','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'SUSD','name'=>'Stable USD','dex'=>'Uniswap V4','age'=>'6d','chg'=>'+0.011%','dir'=>'up','vol'=>'$50.3M'],
                                        ['sym'=>'PONS','name'=>'Pons','dex'=>'Uniswap V4','age'=>'6d','chg'=>'—','dir'=>'','vol'=>'—'],
                                        ['sym'=>'HOOKR','name'=>'Hookr.fun','dex'=>'Uniswap V4','age'=>'5d','chg'=>'-2.09%','dir'=>'down','vol'=>'—'],
                                    ]],
                                    ['addr'=>'0x1cba…a149','hash'=>'0x1cbaf24d53fe930fce8eff149fa797d2611da149','tag'=>'Curve','status'=>'New','seen'=>'1mo','tokens'=>'1.08K','traders'=>'1.08K','chain'=>'ETH','vol'=>'3.11K','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'🅿️','name'=>"Pushin' 🅿️",'dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$3.77K'],
                                        ['sym'=>'🅿️','name'=>"Pushin' 🅿️",'dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$3.77K'],
                                        ['sym'=>'🅿️','name'=>"Pushin' 🅿️",'dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$3.77K'],
                                    ]],
                                    ['addr'=>'0x3194…c827','hash'=>'0x3194e32622c5d860a0572c74edda99dcfd8fc827','tag'=>'Launcher','status'=>'New','seen'=>'2mo','tokens'=>'59','traders'=>'82','chain'=>'ETH','vol'=>'474.9','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'VORTA','name'=>'Vorta Markets','dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$1.18K'],
                                        ['sym'=>'FUNDED','name'=>'FUNDED Protocol','dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$1.23K'],
                                        ['sym'=>'PINK','name'=>'Pink Sheets','dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$1.2K'],
                                    ]],
                                    ['addr'=>'0xadb2…5f49','tag'=>'Cloned token','status'=>'New','seen'=>'12d','tokens'=>'7','traders'=>'2.17K','chain'=>'ETH','vol'=>'332.1','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'HAPPY','name'=>'Happiness','dex'=>'Uniswap V4','age'=>'18d','chg'=>'—','dir'=>'','vol'=>'$2.46K'],
                                        ['sym'=>'KS','name'=>'Kill Switch','dex'=>'Uniswap V4','age'=>'27d','chg'=>'—','dir'=>'','vol'=>'$350K'],
                                        ['sym'=>'KS','name'=>'Kill Switch','dex'=>'Uniswap V4','age'=>'12d','chg'=>'—','dir'=>'','vol'=>'$350K'],
                                    ]],
                                    ['addr'=>'0x24d9…00cc','hash'=>'0x24d9308561c322a603370a0c3bead39e05da00cc','tag'=>'Pool hook','status'=>'New','seen'=>'20d','tokens'=>'6','traders'=>'102','chain'=>'ETH','vol'=>'193.3','up'=>true,'verified'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'HOOD1X','name'=>'HOOD1X','dex'=>'Uniswap V4','age'=>'7d','chg'=>'-0.06%','dir'=>'down','vol'=>'$22.8K'],
                                        ['sym'=>'OPENAI','name'=>'OPENAI','dex'=>'Uniswap V4','age'=>'6d','chg'=>'-0.15%','dir'=>'down','vol'=>'$24.6K'],
                                        ['sym'=>'ANTHROPIC','name'=>'ANTHROPIC','dex'=>'Uniswap V4','age'=>'6d','chg'=>'—','dir'=>'','vol'=>'$23.9K'],
                                    ]],
                                    ['addr'=>'0xc3cd…e34b','tag'=>'Cloned token','status'=>'New','seen'=>'5d','tokens'=>'5','traders'=>'79','chain'=>'ETH','vol'=>'179.9','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'REALM','name'=>'REALM','dex'=>'Uniswap V4','age'=>'5d','chg'=>'—','dir'=>'','vol'=>'$2.87K'],
                                        ['sym'=>'REALM','name'=>'REALM','dex'=>'Uniswap V4','age'=>'5d','chg'=>'—','dir'=>'','vol'=>'$2.84K'],
                                        ['sym'=>'RCAT','name'=>'Realm cat','dex'=>'Uniswap V4','age'=>'5d','chg'=>'—','dir'=>'','vol'=>'$3.05K'],
                                    ]],
                                    ['addr'=>'0xd7d4…fb4c','tag'=>'Cloned token','status'=>'New','seen'=>'1mo','tokens'=>'7','traders'=>'355','chain'=>'ETH','vol'=>'82.28','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'zqNVDA','name'=>'Zorpha NVDA Long/Flat','dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'—'],
                                        ['sym'=>'cNVDA','name'=>'Catch NVIDIA','dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$42.3K'],
                                        ['sym'=>'cEWY','name'=>'Catch SK Fund','dex'=>'Uniswap V4','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$38.3K'],
                                    ]],
                                    ['addr'=>'Torch','hash'=>'0x7891ea889899928afe637e4cca8106188427776a','tag'=>'Launcher','status'=>'New','seen'=>'1d','tokens'=>'13','traders'=>'319','chain'=>'ETH','vol'=>'78.97','up'=>true,'verified'=>true,'website'=>'https://torchpad.fun','twitter'=>'https://x.com/Torchpaddotfun','more'=>2,'samples'=>[
                                        ['sym'=>'TORCH','name'=>'Torch','dex'=>'Uniswap V4','age'=>'1d','chg'=>'+10.08%','dir'=>'up','vol'=>'$2.78M'],
                                        ['sym'=>'TORCHCAT','name'=>'Torch Cat','dex'=>'Uniswap V4','age'=>'1d','chg'=>'-25.4%','dir'=>'down','vol'=>'$16.6K'],
                                        ['sym'=>'TINU','name'=>'TORCH INU','dex'=>'Uniswap V4','age'=>'1d','chg'=>'-36.8%','dir'=>'down','vol'=>'$8.01K'],
                                    ]],
                                    ['addr'=>'Synth','hash'=>'0xdbebc6a7b2a38197932b6e01fb0e57e71d726aec','tag'=>'Pool hook','status'=>'New','seen'=>'3d','tokens'=>'94','traders'=>'69','chain'=>'ETH','vol'=>'52.35','up'=>true,'verified'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'RUNR','name'=>'Runner','dex'=>'Uniswap V4','age'=>'2d','chg'=>'—','dir'=>'','vol'=>'$3.36K'],
                                        ['sym'=>'R1ST','name'=>'Reason First','dex'=>'Uniswap V4','age'=>'2d','chg'=>'—','dir'=>'','vol'=>'$3.36K'],
                                        ['sym'=>'LAMP','name'=>'The Reason Lamp','dex'=>'Uniswap V4','age'=>'2d','chg'=>'—','dir'=>'','vol'=>'$3.37K'],
                                    ]],
                                    ['addr'=>'0x5987…7da5','tag'=>'Cloned token','status'=>'New','seen'=>'1d','tokens'=>'63','traders'=>'70','chain'=>'ETH','vol'=>'48.53','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'MOVR','name'=>'Bid Me Mover','dex'=>'Uniswap V4','age'=>'1d','chg'=>'+0.36%','dir'=>'up','vol'=>'$3.42K'],
                                        ['sym'=>'NOCLK','name'=>'No Clock','dex'=>'Uniswap V4','age'=>'1d','chg'=>'-6.93%','dir'=>'down','vol'=>'$3.44K'],
                                        ['sym'=>'SLOWF','name'=>'Slow Fill','dex'=>'Uniswap V4','age'=>'1d','chg'=>'-6.09%','dir'=>'down','vol'=>'$3.46K'],
                                    ]],
                                    ['addr'=>'0xa37e…ad8f','hash'=>'0xa37e4343d82f4987502c02604edc24b53283ad8f','tag'=>'Launcher','status'=>'New','seen'=>'4d','tokens'=>'21','traders'=>'377','chain'=>'ETH','vol'=>'32.77','up'=>true,'verified'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'UPONLY','name'=>'UPONLY','dex'=>'Uniswap V4','age'=>'3d','chg'=>'—','dir'=>'','vol'=>'$4.14K'],
                                        ['sym'=>'BAIT','name'=>'Rage Bait','dex'=>'Uniswap V4','age'=>'2d','chg'=>'—','dir'=>'','vol'=>'$3.81K'],
                                        ['sym'=>'CRY','name'=>'Funds NOT SAFU','dex'=>'Uniswap V4','age'=>'1d','chg'=>'—','dir'=>'','vol'=>'$3.83K'],
                                    ]],
                                    ['addr'=>'0x58a6…94c0','hash'=>'0x58a6ef29ddcdb064a72b550a78d84a8e065994c0','tag'=>'Pool hook','status'=>'New','seen'=>'8d','tokens'=>'7','traders'=>'664','chain'=>'ETH','vol'=>'32.71','up'=>true,'website'=>'https://orbio.so','twitter'=>'https://x.com/orbiodotso','more'=>2,'samples'=>[
                                        ['sym'=>'SZR','name'=>'Sazare','dex'=>'Uniswap V4','age'=>'2mo','chg'=>'+23.99%','dir'=>'up','vol'=>'$946K'],
                                        ['sym'=>'VIRTUAL','name'=>'Virtuals Protocol','dex'=>'Uniswap V4','age'=>'3mo','chg'=>'-3.79%','dir'=>'down','vol'=>'$5.25M'],
                                    ]],
                                    ['addr'=>'0xf07d…8a57','tag'=>'Cloned token','status'=>'New','seen'=>'13d','tokens'=>'55','traders'=>'57','chain'=>'ETH','vol'=>'25','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'1','name'=>'1','dex'=>'Uniswap V4','age'=>'1d','chg'=>'—','dir'=>'','vol'=>'$0.0232'],
                                        ['sym'=>'1','name'=>'1','dex'=>'Uniswap V4','age'=>'1d','chg'=>'—','dir'=>'','vol'=>'$2.5K'],
                                        ['sym'=>'1','name'=>'1','dex'=>'Uniswap V4','age'=>'1d','chg'=>'—','dir'=>'','vol'=>'$0.0237'],
                                    ]],
                                    ['addr'=>'0x465b…e566','tag'=>'Launcher','status'=>'New','seen'=>'23h','tokens'=>'10','traders'=>'54','chain'=>'ETH','vol'=>'19.05','up'=>true,'verified'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'PAYME','name'=>'Paying Myself','dex'=>'Uniswap V4','age'=>'23h','chg'=>'+0%','dir'=>'up','vol'=>'$3.45K'],
                                        ['sym'=>'BORING','name'=>'Boring Is Fine','dex'=>'Uniswap V4','age'=>'13h','chg'=>'+0%','dir'=>'up','vol'=>'$3.46K'],
                                        ['sym'=>'RAKE','name'=>'The Rake Never Lies','dex'=>'Uniswap V4','age'=>'12h','chg'=>'+12.53%','dir'=>'up','vol'=>'$3.88K'],
                                    ]],
                                    ['addr'=>'0x2c66…2acc','hash'=>'0x2c668601193e4a081dd82604785e436aff6a2acc','tag'=>'Pool hook','status'=>'New','seen'=>'12d','tokens'=>'170','traders'=>'77','chain'=>'ETH','vol'=>'17.63','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'EARTH','name'=>'EARTH','dex'=>'Uniswap V4','age'=>'12d','chg'=>'—','dir'=>'','vol'=>'$1.65K'],
                                        ['sym'=>'BRUH','name'=>'BRUH','dex'=>'Uniswap V4','age'=>'6d','chg'=>'+0%','dir'=>'up','vol'=>'$3.34K'],
                                        ['sym'=>'WCAT','name'=>'WAR CAT','dex'=>'Uniswap V4','age'=>'6d','chg'=>'—','dir'=>'','vol'=>'$1.66K'],
                                    ]],
                                    ['addr'=>'0xf90d…a60a','hash'=>'0xf90d0624d0d3db9897ad774d56b03d82e6f1a60a','tag'=>'Launcher','status'=>'New','seen'=>'1d','tokens'=>'22','traders'=>'111','chain'=>'ETH','vol'=>'13.7','up'=>true,'verified'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'HOLD','name'=>'Still Holding','dex'=>'Uniswap V4','age'=>'22h','chg'=>'-0.12%','dir'=>'down','vol'=>'$3.45K'],
                                        ['sym'=>'HOLD','name'=>'Still Holding','dex'=>'Uniswap V4','age'=>'15h','chg'=>'+0.429%','dir'=>'up','vol'=>'$3.44K'],
                                        ['sym'=>'HOLD1','name'=>'Still Holding 1','dex'=>'Uniswap V4','age'=>'15h','chg'=>'+0%','dir'=>'up','vol'=>'$3.44K'],
                                    ]],
                                    ['addr'=>'0xcab3…3f9c','hash'=>'0xcab3a66d0e12fe3d6cc1268a831c89b0eb693f9c','tag'=>'Launcher','status'=>'New','seen'=>'11h','tokens'=>'11','traders'=>'96','chain'=>'ETH','vol'=>'5.608','up'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'ROLLCALL','name'=>'Roll Call','dex'=>'Uniswap V4','age'=>'11h','chg'=>'+10.51%','dir'=>'up','vol'=>'$3.84K'],
                                        ['sym'=>'HERE','name'=>'Present','dex'=>'Uniswap V4','age'=>'10h','chg'=>'+9.774%','dir'=>'up','vol'=>'$3.83K'],
                                        ['sym'=>'DON7','name'=>'Don Acted Seven Times','dex'=>'Uniswap V4','age'=>'10h','chg'=>'+5.052%','dir'=>'up','vol'=>'$3.65K'],
                                    ]],
                                    ['addr'=>'Pons V2','hash'=>'0xd395e1f4ca56716bd688682ed27ed493b9985788','tag'=>'Curve','status'=>'Integrated','seen'=>'2mo','tokens'=>'120','traders'=>'142','chain'=>'ETH','vol'=>'3.661','up'=>true,'website'=>'https://iimd.world','twitter'=>'https://x.com/Orbitals404','more'=>2,'samples'=>[
                                        ['sym'=>'USELESS','name'=>'USELESS COIN','dex'=>'Pons V2','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$4.24K'],
                                        ['sym'=>'404','name'=>'Orbitals','dex'=>'Pons V2','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$4.17K'],
                                        ['sym'=>'$OUTAGE','name'=>'OUTAGE','dex'=>'Pons V2','age'=>'1mo','chg'=>'—','dir'=>'','vol'=>'$4.17K'],
                                    ]],
                                    ['addr'=>'Programmable Plan Atomic','hash'=>'0xe21827818fea1f14d59929cae36a93c0b44e0ae1','tag'=>'Launcher','status'=>'New','seen'=>'3d','tokens'=>'8','traders'=>'18','chain'=>'ETH','vol'=>'2.166','up'=>true,'verified'=>true,'more'=>2,'samples'=>[
                                        ['sym'=>'N30C','name'=>'Programmable Native30 Canary','dex'=>'Uniswap V4','age'=>'3d','chg'=>'—','dir'=>'','vol'=>'$5.13K'],
                                        ['sym'=>'N30C','name'=>'Programmable Native30 Canary','dex'=>'Uniswap V4','age'=>'2d','chg'=>'—','dir'=>'','vol'=>'$5.11K'],
                                        ['sym'=>'N30C','name'=>'Programmable Native30 Canary','dex'=>'Uniswap V4','age'=>'2d','chg'=>'—','dir'=>'','vol'=>'$5.13K'],
                                    ]],
                                    ['addr'=>'Duel','hash'=>'0x5f746b7b1722e9bb3849eea97061df50f715aaec','tag'=>'Pool hook','status'=>'New','seen'=>'7d','tokens'=>'8','traders'=>'19','chain'=>'ETH','vol'=>'1.561','up'=>true,'verified'=>true,'website'=>'https://divine.sh/pair/1','twitter'=>'https://x.com/divinescreener','more'=>2,'samples'=>[
                                        ['sym'=>'JESUS','name'=>'Jesus','dex'=>'Uniswap V4','age'=>'7d','chg'=>'+6.328%','dir'=>'up','vol'=>'$48.9K'],
                                        ['sym'=>'LUCIFER','name'=>'Lucifer','dex'=>'Uniswap V4','age'=>'7d','chg'=>'+14.88%','dir'=>'up','vol'=>'$59.5K'],
                                        ['sym'=>'HITLER','name'=>'Hitler','dex'=>'Uniswap V4','age'=>'6d','chg'=>'-30.8%','dir'=>'down','vol'=>'$7.52K'],
                                    ]],
                                    ['addr'=>'Gomo Static Fee','hash'=>'0x09cbc918829d582599f3346cb063869bb4cbeacc','tag'=>'Pool hook','status'=>'New','seen'=>'8d','tokens'=>'5','traders'=>'44','chain'=>'ETH','vol'=>'1.102','up'=>true,'verified'=>true,'website'=>'https://gomofamily.life','more'=>2,'samples'=>[
                                        ['sym'=>'HORSE','name'=>'Gomo Horse','dex'=>'Uniswap V4','age'=>'4d','chg'=>'+9.338%','dir'=>'up','vol'=>'$10.4K'],
                                        ['sym'=>'TEST','name'=>'test','dex'=>'Uniswap V4','age'=>'4d','chg'=>'—','dir'=>'','vol'=>'$2.49K'],
                                        ['sym'=>'GCASHCAT','name'=>'GOMO CASHCAT','dex'=>'Uniswap V4','age'=>'4d','chg'=>'—','dir'=>'','vol'=>'$2.9K'],
                                    ]],
                                    ['addr'=>'0x4fc4…a305','hash'=>'0x4fc43c6324bde006db7db230e2166acbfe8fa305','tag'=>'Launcher','status'=>'New','seen'=>'6d','tokens'=>'27','traders'=>'84','chain'=>'ETH','vol'=>'0.091','up'=>true,'website'=>'https://chow.ar.io','twitter'=>'https://x.com/amemecoinrh','more'=>1,'samples'=>[
                                        ['sym'=>'ORBIT','name'=>'Orbit','dex'=>'Uniswap V4','age'=>'6d','chg'=>'-9.1%','dir'=>'down','vol'=>'$2.21K'],
                                        ['sym'=>'WISH','name'=>'Little Wish','dex'=>'Uniswap V4','age'=>'6d','chg'=>'-13.4%','dir'=>'down','vol'=>'$2.29K'],
                                        ['sym'=>'SNAX','name'=>'SNAX','dex'=>'Uniswap V4','age'=>'6d','chg'=>'—','dir'=>'','vol'=>'$2.21K'],
                                    ]],
                                    ['addr'=>'Pools Instant','hash'=>'0x3d6845fbe59fa865232259c46e09ac4818a4cf75','tag'=>'Launcher','status'=>'Integrated','seen'=>'1mo','tokens'=>'6','traders'=>'50','chain'=>'ETH','vol'=>'0.021','up'=>true,'more'=>1,'samples'=>[
                                        ['sym'=>'FKING','name'=>'Frog King','dex'=>'Pools Instant','age'=>'27d','chg'=>'—','dir'=>'','vol'=>'$6.36K'],
                                        ['sym'=>'FKING','name'=>'Frog King','dex'=>'Pools Instant','age'=>'27d','chg'=>'—','dir'=>'','vol'=>'$27.1K'],
                                        ['sym'=>'WET','name'=>'WET','dex'=>'Pools Instant','age'=>'27d','chg'=>'—','dir'=>'','vol'=>'$6.28K'],
                                    ]],
                                ];
                                @endphp
                                <div class="radar-scroll" data-panel="radar" hidden>
                                    @include('partials.radar-table', ['venues' => $venues])
                                </div>
                            </div>
                        </section>

                        {{-- ---------- MOBILE DISCOVER ---------- --}}
                        <section class="discover-page-mobile mobile-only" data-perf-id="discover-trending">
                            <div class="dm-bar">
                                <div class="disc-tabs">
                                    @foreach ([['Top','top'],['Trending','trending'],['Radar','radar'],['New','new'],['Callouts','callouts'],['Streamers','streamers']] as $t)
                                        <button class="disc-tab {{ $t[1] === 'trending' ? 'is-active' : '' }}" type="button" data-tab="{{ $t[1] }}">
                                            <span class="disc-tab-label">{{ $t[0] }}</span>
                                            @if (in_array($t[1], ['radar','new','streamers']))<span class="disc-new">New</span>@endif
                                        </button>
                                    @endforeach
                                </div>
                                <div class="disc-times dm-times">
                                    @foreach (['new' => 'New', '1m' => '1m', '5m' => '5m', '30m' => '30m', '1h' => '1h', '24h' => '24h'] as $v => $lbl)
                                        <button class="disc-time {{ $v === '24h' ? 'is-active' : '' }}" type="button" data-tf="{{ $v }}">{{ $lbl }}</button>
                                    @endforeach
                                </div>
                                <div class="qb-field dm-qb">
                                    <span class="qb-label">Quick Buy</span>
                                    <input type="text" value="0" placeholder="0.0" aria-label="Quick buy amount">
                                    <button class="qb-chain" type="button"><img src="{{ $chainArt['sol'] }}" alt="SOL" width="16" height="16"><i class="ri-arrow-down-s-line"></i></button>
                                    <div class="qb-presets">
                                        <button class="qb-p is-active" type="button" data-amount="0.1">P1</button>
                                        <button class="qb-p" type="button" data-amount="0.5">P2</button>
                                        <button class="qb-p" type="button" data-amount="1">P3</button>
                                    </div>
                                </div>
                            </div>
                            <div class="discover-table-scroll" data-panel="tokens">
                                <div class="discover-table">
                                    <div class="disc-thead">
                                        <div class="c-pair"><span>Pair Info</span></div>
                                        <div class="c-mc"><span>MC</span></div>
                                        <div class="c-txns"><span>TXNS</span></div>
                                        <div class="c-action"><span>Action</span></div>
                                    </div>
                                    <div class="disc-body">
                                        @foreach ($tokens as $t)
                                        <div class="disc-row" data-chain="{{ $t['chain'] }}" data-age="{{ $t['age'] ?? '' }}" data-address="{{ $t['address'] ?? '' }}">
                                            <div class="c-pair">
                                                <div class="pair-avatar-wrap">
                                                    <div class="pair-avatar">
                                                        <span class="pa-frame" style="background:{{ $chainColor[$t['chain']] }}">
                                                            <span class="pa-frame-inner"><img class="pa-img" src="{{ $t['img'] }}" alt="{{ $t['name'] }}"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="pair-text">
                                                    <div class="pair-name-row">@if (!empty($t['address']))<a class="pair-tick pair-tick-link" href="/token/{{ $t['address'] }}">{{ $t['sym'] }}</a>@else<span class="pair-tick">{{ $t['sym'] }}</span>@endif</div>
                                                    <div class="pair-meta"><span class="pair-age">{{ $t['age'] }}</span></div>
                                                </div>
                                            </div>
                                            <div class="c-mc"><span class="val">{{ $t['mc'] }}</span><span class="chg {{ $t['up'] ? 'up' : 'down' }}">{{ $t['pct'] }}</span></div>
                                            <div class="c-txns"><span class="val">{{ $t['txns'] }}</span></div>
                                            <div class="c-action"><button class="buy-btn" type="button" data-native="{{ $t['native'] ?? ($t['quoteSym'] ?? 'SOL') }}" data-mint="{{ $t['address'] ?? '' }}" data-chain="{{ $t['chain'] ?? 'sol' }}" data-symbol="{{ $t['sym'] }}"><span>Buy</span><span class="buy-amt">0 {{ $t['native'] ?? ($t['quoteSym'] ?? 'SOL') }}</span></button></div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="disc-panel disc-panel-mobile" data-panel="callouts" hidden>
                                @include('partials.callouts')
                            </div>

                            <div class="disc-panel disc-panel-mobile" data-panel="streamers" hidden>
                                @include('partials.streamers')
                            </div>

                            <div class="disc-panel disc-panel-mobile" data-panel="radar" hidden>
                                <div class="disc-panel-head">
                                    <h3><i class="ri-radar-line"></i> Venue radar</h3>
                                </div>
                                @include('partials.radar-table', ['venues' => $venues])
                            </div>
                        </section>

                    </div>
                </main>

                @include('partials.status-bar', ['spot' => $spot ?? null])

                @include('partials.mobile-navbar', ['active' => 'discover'])

            </div>
        </div>
    </div>
    <script>
    (function () {
        var rail = document.querySelector('.chain-rail');
        var chainItems = rail ? [].slice.call(rail.querySelectorAll('.cr-item')) : [];
        var allBtn = rail ? rail.querySelector('.cr-all') : null;
        var rows = [].slice.call(document.querySelectorAll('.disc-row[data-chain]'));

        function readAgeMin(el) {
            var t = (el.dataset.age || '').trim().toLowerCase();
            var m = t.match(/^(\d+(?:\.\d+)?)\s*(s|m|h|d|mo)$/);
            if (!m) return Infinity;
            var n = parseFloat(m[1]);
            if (m[2] === 's') return n / 60;
            if (m[2] === 'm') return n;
            if (m[2] === 'h') return n * 60;
            if (m[2] === 'd') return n * 1440;
            return n * 43200;
        }

        function applyVisibility() {
            rows.forEach(function (r) {
                var hide = r.dataset.chainHide === '1' || r.dataset.tfHide === '1';
                r.style.display = hide ? 'none' : '';
            });
        }

        // ---- chain filter ----
        if (chainItems.length && allBtn) {
            var allOn = function () { return chainItems.every(function (it) { return it.dataset.chainHide !== '1'; }); };
            chainItems.forEach(function (it) {
                it.addEventListener('click', function () {
                    var off = it.dataset.chainHide === '1';
                    it.dataset.chainHide = off ? '' : '1';
                    it.classList.toggle('is-off', !off);
                    allBtn.classList.toggle('is-active', allOn());
                    applyVisibility();
                });
            });
            allBtn.addEventListener('click', function () {
                var turnOff = allOn();
                chainItems.forEach(function (it) {
                    it.dataset.chainHide = turnOff ? '1' : '';
                    it.classList.toggle('is-off', turnOff);
                });
                allBtn.classList.toggle('is-active', !turnOff);
                applyVisibility();
            });
        }

        // ---- tabs ----
        var tabs = [].slice.call(document.querySelectorAll('.disc-tab[data-tab]'));
        var leadTabs = ['top', 'trending', 'new'];
        var sortKey = 'trending';

        function rowVal(row, sel) { var el = row.querySelector(sel); return el ? el.textContent : ''; }
        function num(str) {
            var s = String(str == null ? '' : str);
            var mult = /K/i.test(s) ? 1e3 : /M/i.test(s) ? 1e6 : /B/i.test(s) ? 1e9 : 1;
            return (parseFloat(s.replace(/[^0-9.\-]/g, '')) || 0) * mult;
        }
        function sortRows() {
            [].slice.call(document.querySelectorAll('.disc-body')).forEach(function (body) {
                var list = [].slice.call(body.querySelectorAll('.disc-row'));
                list.sort(function (a, b) {
                    if (sortKey === 'top') return num(rowVal(b, '.c-mc .val')) - num(rowVal(a, '.c-mc .val'));
                    if (sortKey === 'trending') return (parseFloat(rowVal(b, '.c-mc .chg')) || 0) - (parseFloat(rowVal(a, '.c-mc .chg')) || 0);
                    if (sortKey === 'radar') return num(rowVal(b, '.c-txns .val')) - num(rowVal(a, '.c-txns .val'));
                    if (sortKey === 'new') return readAgeMin(a) - readAgeMin(b);
                    return 0;
                });
                list.forEach(function (r) { body.appendChild(r); });
            });
        }
        function showPanel(name) {
            var kind = leadTabs.indexOf(name) >= 0 ? 'tokens' : name;
            [].slice.call(document.querySelectorAll('[data-panel]')).forEach(function (p) {
                p.hidden = p.dataset.panel !== kind;
                p.style.display = p.dataset.panel === kind ? '' : 'none';
            });
            if (kind === 'tokens') { sortKey = name; sortRows(); }
            applyVisibility();
        }
        tabs.forEach(function (t) {
            t.addEventListener('click', function () {
                tabs.forEach(function (x) { x.classList.toggle('is-active', x === t); });
                showPanel(t.dataset.tab);
            });
        });

        // ---- time filter ----
        var times = [].slice.call(document.querySelectorAll('.disc-time[data-tf]'));
        times.forEach(function (t) {
            t.addEventListener('click', function () {
                times.forEach(function (x) { x.classList.toggle('is-active', x === t); });
                var v = t.dataset.tf;
                var max = v === 'new' ? 5 : v === '1m' ? 1 : v === '5m' ? 5 : v === '30m' ? 30 : v === '1h' ? 60 : 1440;
                rows.forEach(function (r) { r.dataset.tfHide = readAgeMin(r) > max ? '1' : ''; });
                applyVisibility();
            });
        });

        // ---- clickable rows ----
        rows.forEach(function (r) {
            r.addEventListener('click', function (e) {
                if (e.target.closest('a, button, input')) return;
                if (r.dataset.address) window.location.href = '/token/' + r.dataset.address;
            });
        });

        // ---- radar venues (sort + range + copy) ----
        (function () {
            var lists = [].slice.call(document.querySelectorAll('.radar-list'));
            if (!lists.length) return;
            function num(str) {
                var s = String(str == null ? '' : str);
                var mult = /K/i.test(s) ? 1e3 : /M/i.test(s) ? 1e6 : /B/i.test(s) ? 1e9 : 1;
                return (parseFloat(s.replace(/[^0-9.\-]/g, '')) || 0) * mult;
            }
            function ageMin(t) {
                t = String(t || '').trim().toLowerCase();
                var m = t.match(/^(\d+(?:\.\d+)?)\s*(s|m|h|d|mo)$/);
                if (!m) return Infinity;
                var n = parseFloat(m[1]);
                if (m[2] === 's') return n / 60;
                if (m[2] === 'm') return n;
                if (m[2] === 'h') return n * 60;
                if (m[2] === 'd') return n * 1440;
                return n * 43200;
            }
            function cmp(key) {
                return function (a, b) {
                    if (key === 'traders') return num(b.dataset.traders) - num(a.dataset.traders);
                    if (key === 'tokens') return num(b.dataset.tokens) - num(a.dataset.tokens);
                    if (key === 'seen') return ageMin(a.dataset.seen) - ageMin(b.dataset.seen);
                    return num(b.dataset.vol) - num(a.dataset.vol);
                };
            }
            [].slice.call(document.querySelectorAll('.radar-sort-btn')).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var key = btn.dataset.rsort;
                    [].slice.call(document.querySelectorAll('.radar-sort-btn')).forEach(function (x) {
                        x.classList.toggle('is-active', x.dataset.rsort === key);
                    });
                    lists.forEach(function (list) {
                        [].slice.call(list.querySelectorAll('.radar-card')).sort(cmp(key)).forEach(function (c) {
                            list.appendChild(c);
                        });
                    });
                });
            });
            [].slice.call(document.querySelectorAll('.radar-range button')).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    [].slice.call(document.querySelectorAll('.radar-range button')).forEach(function (x) {
                        x.classList.toggle('is-active', x.dataset.rrange === btn.dataset.rrange);
                    });
                });
            });
            [].slice.call(document.querySelectorAll('.radar-copy')).forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var t = btn.dataset.copy || '';
                    if (navigator.clipboard && t) navigator.clipboard.writeText(t).catch(function () {});
                });
            });
        })();
    })();
    </script>
    <script>
    (function () {
        var inputs = Array.prototype.slice.call(document.querySelectorAll('.qb-field input'));
        var amts = document.querySelectorAll('.buy-btn .buy-amt');
        var presets = document.querySelectorAll('.qb-p');
        if (!inputs.length) return;

        function apply(value) {
            var v = (value == null ? '0' : String(value)).trim() || '0';
            inputs.forEach(function (i) { if (i.value !== v) i.value = v; });
            amts.forEach(function (el) {
                var btn = el.closest('.buy-btn');
                var native = btn ? (btn.getAttribute('data-native') || 'SOL') : 'SOL';
                el.textContent = v + ' ' + native;
            });
            presets.forEach(function (p) {
                p.classList.toggle('is-active', (p.getAttribute('data-amount') || '') === v);
            });
        }

        inputs.forEach(function (i) {
            i.addEventListener('input', function () { apply(i.value); });
        });
        presets.forEach(function (p) {
            p.addEventListener('click', function () { apply(p.getAttribute('data-amount')); });
        });

        apply(inputs[0].value);
    })();
    </script>
    <script src="{{ asset('js/axiom-magic.js') }}" defer></script>
    <script src="{{ asset('js/axiom-trade.js') }}?v={{ @filemtime(public_path('js/axiom-trade.js')) }}" defer></script>
    <script>
    (function () {
        [].slice.call(document.querySelectorAll('.radar-frame .more-button')).forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var tr = btn.closest('tr');
                if (!tr) return;
                var open = tr.classList.toggle('expanded');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
        [].slice.call(document.querySelectorAll('.radar-frame [data-sort="volume"]')).forEach(function (header) {
            var desc = true;
            header.addEventListener('click', function () {
                var frame = header.closest('.radar-frame');
                var body = frame && frame.querySelector('.radar-rows');
                if (!body) return;
                desc = !desc;
                [].slice.call(body.querySelectorAll('tr')).sort(function (a, b) {
                    return (Number(a.dataset.volume) - Number(b.dataset.volume)) * (desc ? -1 : 1);
                }).forEach(function (row) { body.appendChild(row); });
                var arrow = header.querySelector('.sort-arrow');
                if (arrow) arrow.textContent = desc ? '↓' : '↑';
            });
        });
        [].slice.call(document.querySelectorAll('.radar-refresh')).forEach(function (ind) {
            var num = ind.querySelector('.radar-refresh-num');
            if (!num) return;
            var remaining = 5;
            window.setInterval(function () {
                remaining -= 1;
                if (remaining <= 0) {
                    ind.classList.add('is-refreshing');
                    num.textContent = '0';
                    window.setTimeout(function () {
                        ind.classList.remove('is-refreshing');
                        remaining = 5;
                        num.textContent = '5';
                    }, 650);
                } else {
                    num.textContent = String(remaining);
                }
            }, 1000);
        });
    })();
    </script>
    <script>
    (function () {
        [].slice.call(document.querySelectorAll('.callouts-embed .feed-filter, .callouts-embed .source-tabs button')).forEach(function (btn) {
            btn.addEventListener('click', function () { btn.classList.toggle('active'); });
        });
        [].slice.call(document.querySelectorAll('.callouts-embed .reactions button')).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var count = btn.querySelector('span');
                var selected = btn.classList.toggle('liked');
                if (count) count.textContent = selected ? '1' : '0';
            });
        });
        [].slice.call(document.querySelectorAll('.callouts-embed .block-link, .callouts-embed .history .block-button')).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var blocked = btn.dataset.blocked === 'true';
                btn.dataset.blocked = String(!blocked);
                btn.textContent = blocked ? '⊘ Block' : 'Blocked';
            });
        });
    })();
    </script>
    <script>
    (function () {
        [].slice.call(document.querySelectorAll('.streamers-embed')).forEach(function (embed) {
            [].slice.call(embed.querySelectorAll('.filters [data-category]')).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    [].slice.call(embed.querySelectorAll('.filters [data-category]')).forEach(function (x) { x.classList.remove('active'); });
                    btn.classList.add('active');
                    var category = btn.dataset.category;
                    var showAll = category === 'Home' || category === 'All' || category === 'Live' || category === 'Favorites';
                    [].slice.call(embed.querySelectorAll('.streamer-card, .featured')).forEach(function (card) {
                        card.style.opacity = showAll ? '1' : '.82';
                    });
                });
            });
            [].slice.call(embed.querySelectorAll('.favorite')).forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var active = btn.classList.toggle('active');
                    btn.textContent = active ? '★' : '☆';
                });
            });
            [].slice.call(embed.querySelectorAll('.notice')).forEach(function (btn) {
                btn.addEventListener('click', function () { btn.classList.toggle('active'); });
            });
            [].slice.call(embed.querySelectorAll('.timer')).forEach(function (timer) {
                var seconds = 3;
                window.setInterval(function () {
                    seconds -= 1;
                    if (seconds < 0) seconds = 3;
                    timer.innerHTML = '<span class="clock">◴</span> ' + seconds + 's';
                }, 1000);
            });
        });
    })();
    </script>
</body>
</html>
