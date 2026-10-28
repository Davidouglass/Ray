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
    $pm    = "$asset/protocol-marks/v1";
    $spot  = $spot ?? [];
    $nav = [
        ['ri-wallet-3-line',  'Wallet',   '/portfolio', null],
        ['ri-twitter-x-line', 'Social',   '/social',    'social'],
        ['ri-compass-3-line', 'Discover', '/discover',  null],
        ['ri-pulse-line',     'Pulse',    '/pulse',     null],
        ['ri-bar-chart-line', 'PnL',      '/pnl',       'pnl'],
        ['ri-group-3-line',   'Friends',  '/friends',   'friends'],
        ['ri-live-line',      'Streams',  '/streams',   'streams'],
        ['ri-megaphone-line', 'Callouts', '/callouts',  'callouts'],
    ];
    $nav = array_values(array_filter($nav, fn ($n) => $n[3] === null || \App\Models\SiteSetting::flag($n[3])));
@endphp
{{-- ================= STATUS BAR ================= --}}
<footer class="platform-status desktop-only">
    <div class="status-scroll">
        <div class="status-left">
            <a class="st-preset" href="/settings?tab=presets"><i class="ri-list-settings-line"></i><span>PRESET 1</span></a>

            <a class="st-wallets" href="/portfolio">
                <i class="ri-wallet-line"></i><span>1</span>
                <span class="sep-dot"></span>
                <img src="{{ $chainArt['sol'] }}" alt="SOL" width="14" height="14"><span>0</span>
                <i class="ri-arrow-down-s-line"></i>
            </a>
            <a class="st-icon" href="/settings" aria-label="Status settings"><i class="ri-settings-3-line"></i></a>
            <span class="st-sep"></span>

            <div class="st-nav">
                @foreach ($nav as $n)
                    <a class="st-navbtn" href="{{ $n[2] }}"><i class="{{ $n[0] }}"></i><span>{{ $n[1] }}</span></a>
                @endforeach
            </div>
            <span class="st-sep lg-only"></span>

            <a class="st-lighthouse lg-only" href="/launchpads" aria-label="Launchpads">
                <span class="lh-bar"><span class="lh-inner"><img src="{{ $pm }}/pump.webp" alt="Pump" width="11" height="11"><img src="{{ $pm }}/flap.webp" alt="Flap" width="11" height="11"><img src="{{ $pm }}/stonkfun-logo.webp" alt="Stonkfun" width="11" height="11"></span></span>
            </a>
            <a class="st-lighthouse lg-only" href="/markets" aria-label="Cross-Chain Markets">
                <span class="lh-bar lh-bar--chains"><span class="lh-inner"><img src="{{ $chainArt['sol'] }}" alt="Solana" width="11" height="11"><img src="{{ $chainArt['bnb'] }}" alt="BNB" width="11" height="11"><img src="{{ $chainArt['robinhood'] }}" alt="Robinhood" width="11" height="11"></span></span>
            </a>
            <span class="st-sep lg-only"></span>

            <div class="st-prices">
                <a class="st-price" href="/markets" style="color:#14F195"><img src="{{ $chainArt['sol'] }}" alt="SOL" width="16" height="16">{{ $spot['SOL'] ?? '$—' }}</a>
                <a class="st-price" href="/markets" style="color:#F0B90B"><img src="{{ $chainArt['bnb'] }}" alt="BNB" width="16" height="16">{{ $spot['BNB'] ?? '$—' }}</a>
                <a class="st-price" href="/markets" style="color:#97FCE4"><img src="{{ $chainArt['hype'] }}" alt="HYPE" width="16" height="16">{{ $spot['HYPE'] ?? '$—' }}</a>
            </div>
        </div>

        <div class="status-right">
            <a class="st-pillval" href="/rewards"><i class="ri-capsule-line"></i><span>$44.9K</span></a>
            <span class="st-sep"></span>
            <a class="st-auto" href="/settings?tab=data"><span class="st-dot"></span><span>Auto</span><i class="ri-arrow-down-s-line"></i></a>
            <span class="st-sep"></span>
            <div class="st-tools">
                <a class="st-icon" href="/settings?tab=presets" aria-label="Layout"><i class="ri-layout-top-line"></i></a>
                <a class="st-icon" href="/settings?tab=notifications" aria-label="Notifications"><i class="ri-notification-3-line"></i></a>
                <a class="st-icon" href="/settings?tab=appearance" aria-label="Appearance"><i class="ri-palette-line"></i></a>
            </div>
            <span class="st-sep md-only"></span>
            <div class="st-social">
                @php
                    $socialDiscord = \App\Models\SiteSetting::get('social_discord', 'https://discord.gg/axiomtrade');
                    $socialX       = \App\Models\SiteSetting::get('social_x', 'https://x.com/axiomexchange');
                    $socialDocs    = \App\Models\SiteSetting::get('social_docs', 'https://docs.axiom.trade');
                @endphp
                @if ($socialDiscord)
                    <a href="{{ $socialDiscord }}" target="_blank" rel="noopener" class="st-icon"><i class="ri-discord-fill"></i></a>
                @endif
                @if ($socialX)
                    <a href="{{ $socialX }}" target="_blank" rel="noopener" class="st-icon"><i class="ri-twitter-x-line"></i></a>
                @endif
                @if ($socialDocs)
                    <a href="{{ $socialDocs }}" target="_blank" rel="noopener" class="st-docs"><i class="ri-article-line"></i><span>Docs</span></a>
                @endif
                @if ($supportUrl = \App\Models\SiteSetting::supportUrl())
                    <a href="{{ $supportUrl }}" target="_blank" rel="noopener" class="st-docs"><i class="ri-telegram-line"></i><span>Support</span></a>
                @endif
                @if ($socialDiscord)
                    <a class="st-icon" href="{{ $socialDiscord }}" target="_blank" rel="noopener" aria-label="Report a bug"><i class="ri-bug-line"></i></a>
                @endif
            </div>
        </div>
    </div>
</footer>
