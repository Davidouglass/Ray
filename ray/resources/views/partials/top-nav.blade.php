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
    $navQuery = '?chains=sol,robinhood,arc,bnb,ink,hype,base,eth&pulseChains=sol,robinhood,arc,bnb,ink,hype,base,eth&trackerChains=sol,robinhood,arc,bnb,ink,hype,base,eth&discoverChains=sol,robinhood,arc,bnb,ink,hype,base,eth';
    $active = $active ?? '';
    $chainNames = ['sol' => 'Solana', 'robinhood' => 'Robinhood', 'arc' => 'Arc', 'bnb' => 'BNB Chain', 'ink' => 'Ink', 'hype' => 'Hyperliquid', 'base' => 'Base', 'eth' => 'Ethereum'];
    $navWallet = Auth::check() ? Auth::user()->connectedWallets()->orderByDesc('is_primary')->latest()->first() : null;
    $tabs = [
        'Copy Trading' => 'copy-trading',
        'Discover'    => 'discover',
        'Pulse'       => 'pulse',
        'Perpetuals'  => 'perpetuals',
        'Predictions' => 'predictions',
        'Vision'      => 'vision',
        'Portfolio'   => 'portfolio',
        'Rewards'     => 'rewards',
    ];
    $tabFlags = ['copy-trading', 'perpetuals', 'predictions', 'vision'];
    $tabs = array_filter($tabs, fn ($slug) => ! in_array($slug, $tabFlags, true) || \App\Models\SiteSetting::flag($slug));
@endphp

{{-- ================= DESKTOP TOP NAV ================= --}}
<header class="platform-top desktop-only">
    <a class="top-brand" href="/discover" aria-label="Veridian home">
        <img src="{{ asset('images/veridian-mark.png') }}" alt="Veridian" class="brand-logo">
    </a>

    <div class="top-tabs-wrap">
        <button class="top-tabs-arrow top-tabs-arrow--left" type="button" aria-label="Scroll tabs left" tabindex="-1"><i class="ri-arrow-left-s-line"></i></button>
        <nav class="top-tabs">
            @foreach ($tabs as $label => $slug)
                <a href="/{{ $slug }}{{ $navQuery }}" class="top-tab {{ $active === $slug ? 'is-active' : '' }}" data-discover="true">
                    <span class="top-tab-bg"></span>
                    <span class="top-tab-label">{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <button class="top-tabs-arrow top-tabs-arrow--right" type="button" aria-label="Scroll tabs right" tabindex="-1"><i class="ri-arrow-right-s-line"></i></button>
    </div>

    <div class="top-actions">
        <div class="top-search" data-top-search>
            <i class="ri-search-2-line"></i>
            <input class="top-search-input" type="text" placeholder="Search by token or CA" autocomplete="off" spellcheck="false" aria-label="Search tokens">
            <span class="top-kbd">/</span>
            <div class="top-search-results" hidden></div>
        </div>

        <div class="top-chains" data-top-chains role="button" tabindex="0" aria-label="Filter chains">
            <span class="chain-stack">
                @foreach ($chainArt as $art)
                    <img src="{{ $art }}" alt="" width="15" height="15">
                @endforeach
            </span>
            <i class="ri-arrow-down-s-line top-chev"></i>
            <div class="top-chain-menu" hidden>
                @foreach ($chainArt as $key => $art)
                    <a class="top-chain-item" href="/chain/{{ $key }}">
                        <img src="{{ $art }}" alt=""><span>{{ $chainNames[$key] ?? ucfirst($key) }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <button class="top-deposit" type="button" data-wallet-deposit><i class="ri-download-2-line"></i><span>Deposit</span></button>

        @if ($navWallet)
            <button class="top-wallet-chip" type="button" data-wallet-connect title="Manage wallets">
                <span class="top-wallet-dot"></span>
                <code>{{ strlen($navWallet->address) > 12 ? substr($navWallet->address, 0, 4) . '…' . substr($navWallet->address, -4) : $navWallet->address }}</code>
                <i class="ri-arrow-down-s-line"></i>
            </button>
        @else
            <button class="top-connect" type="button" data-wallet-connect><i class="ri-wallet-3-line"></i><span>Connect Wallet</span></button>
        @endif

        <div class="shell-account">
            <button class="top-avatar" type="button" aria-label="Account">{{ Auth::user()->name ?? 'User' }}</button>
            <div class="shell-menu">
                <div class="shell-menu-head">
                    <span class="shell-menu-name">{{ Auth::user()->name ?? 'User' }}</span>
                    <span class="shell-menu-email">{{ Auth::user()->email ?? '' }}</span>
                </div>
                <a class="shell-menu-item" href="/portfolio">Portfolio</a>
                <a class="shell-menu-item" href="/rewards">Rewards</a>
                @if ($supportUrl = \App\Models\SiteSetting::supportUrl())
                    <a class="shell-menu-item" href="{{ $supportUrl }}" target="_blank" rel="noopener">Support</a>
                @endif
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="shell-menu-item">Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>

@include('partials.announcements')
@include('partials.impersonation-bar')

{{-- ================= MOBILE TOP BAR ================= --}}
<div class="platform-mobile">
    <a class="mob-brand" href="/discover" aria-label="Veridian home">
        <img src="{{ asset('images/veridian-mark.png') }}" alt="Veridian" class="brand-logo mob-brand-logo">
    </a>
    <span class="mob-spacer"></span>
    <a class="mob-ca" href="/discover"><i class="ri-file-copy-line"></i><span>Paste CA</span></a>
    <a class="mob-icon" href="/discover" aria-label="Search"><i class="ri-search-2-line"></i></a>
    <a class="mob-avatar" href="/portfolio" aria-label="Account">{{ Auth::user()->name ?? 'U' }}</a>
    <a class="mob-icon" href="/vision" aria-label="Menu"><i class="ri-menu-line"></i></a>
</div>

@include('partials.portfolio-wallet-modal')
@include('partials.deposit-modal', ['chainArt' => $chainArt, 'chainNames' => $chainNames])
<script src="{{ asset('js/axiom-wallet.js') }}?v={{ @filemtime(public_path('js/axiom-wallet.js')) }}" defer></script>

<script>
(function () {
    function init() {
        var wrap = document.querySelector('.top-tabs-wrap');
        if (!wrap) return;
        var strip = wrap.querySelector('.top-tabs');
        if (!strip) return;
        var left = wrap.querySelector('.top-tabs-arrow--left');
        var right = wrap.querySelector('.top-tabs-arrow--right');
        function update() {
            var max = strip.scrollWidth - strip.clientWidth;
            wrap.classList.toggle('is-scrollable', max > 1);
            wrap.classList.toggle('at-start', strip.scrollLeft <= 1);
            wrap.classList.toggle('at-end', strip.scrollLeft >= max - 1);
        }
        function nudge(d) { strip.scrollBy({ left: d * Math.round(strip.clientWidth * 0.6), behavior: 'smooth' }); }
        if (!wrap.dataset.navReady) {
            wrap.dataset.navReady = '1';
            if (left) left.addEventListener('click', function () { nudge(-1); });
            if (right) right.addEventListener('click', function () { nudge(1); });
            strip.addEventListener('scroll', update, { passive: true });
            strip.addEventListener('wheel', function (e) {
                if (e.deltaY !== 0 && e.deltaX === 0) { strip.scrollLeft += e.deltaY; e.preventDefault(); }
            }, { passive: false });
            window.addEventListener('resize', update);
            if (window.ResizeObserver) { new ResizeObserver(update).observe(strip); }
            var active = strip.querySelector('.top-tab.is-active');
            if (active) active.scrollIntoView({ inline: 'center', block: 'nearest' });
        }
        update();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(init);
    }
})();

(function () {
    var box = document.querySelector('[data-top-search]');
    if (!box) return;
    var input = box.querySelector('.top-search-input');
    var results = box.querySelector('.top-search-results');
    if (!input || !results) return;

    var timer, ctrl, items = [], activeIdx = -1;

    function close() { results.hidden = true; results.innerHTML = ''; items = []; activeIdx = -1; }
    function open() { results.hidden = false; }

    function fmtPrice(p) {
        p = Number(p) || 0;
        if (p >= 1000) return '$' + p.toLocaleString(undefined, { maximumFractionDigits: 2 });
        if (p >= 1) return '$' + p.toFixed(3);
        if (p >= 0.01) return '$' + p.toFixed(4);
        if (p > 0) return '$' + p.toPrecision(4);
        return '$0';
    }
    function render(list) {
        items = [];
        activeIdx = -1;
        if (!list.length) {
            results.innerHTML = '<div class="top-search-empty">No tokens found</div>';
            open();
            return;
        }
        results.innerHTML = list.map(function (t) {
            var chg = Number(t.chg) || 0;
            var art = t.img
                ? '<img class="ts-img" src="' + t.img + '" alt="">'
                : '<span class="ts-badge">' + String(t.symbol || '?').slice(0, 1).toUpperCase() + '</span>';
            return '<a class="top-search-item" href="/token/' + t.address + '">' + art +
                '<span class="ts-body"><span class="ts-sym">' + (t.symbol || '?') + '</span>' +
                '<span class="ts-name">' + (t.name || t.chain || '') + '</span></span>' +
                '<span class="ts-px"><span>' + fmtPrice(t.price) + '</span>' +
                '<span class="' + (chg >= 0 ? 'pos' : 'neg') + '">' + (chg >= 0 ? '+' : '') + chg.toFixed(2) + '%</span></span></a>';
        }).join('');
        items = Array.prototype.slice.call(results.querySelectorAll('.top-search-item'));
        open();
    }
    function setActive(n) {
        if (!items.length) return;
        activeIdx = (n + items.length) % items.length;
        items.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIdx); });
        items[activeIdx].scrollIntoView({ block: 'nearest' });
    }
    function run(q) {
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        fetch('/api/search?q=' + encodeURIComponent(q), { signal: ctrl.signal })
            .then(function (r) { return r.json(); })
            .then(function (d) { render((d && d.results) || []); })
            .catch(function () {});
    }
    input.addEventListener('input', function () {
        var q = input.value.trim();
        clearTimeout(timer);
        if (q.length < 2) { close(); return; }
        timer = setTimeout(function () { run(q); }, 220);
    });
    input.addEventListener('focus', function () { if (input.value.trim().length >= 2 && results.innerHTML) open(); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIdx + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIdx - 1); }
        else if (e.key === 'Enter') {
            var target = items[activeIdx >= 0 ? activeIdx : 0];
            if (target) { e.preventDefault(); window.location = target.getAttribute('href'); }
        } else if (e.key === 'Escape') { close(); input.blur(); }
    });
    document.addEventListener('click', function (e) { if (!box.contains(e.target)) close(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement !== input) {
            var tag = (document.activeElement && document.activeElement.tagName) || '';
            if (tag !== 'INPUT' && tag !== 'TEXTAREA') { e.preventDefault(); input.focus(); }
        }
    });
})();

(function () {
    var box = document.querySelector('[data-top-chains]');
    if (!box) return;
    var menu = box.querySelector('.top-chain-menu');
    if (!menu) return;
    box.addEventListener('click', function (e) {
        if (e.target.closest('.top-chain-item')) { return; }
        menu.hidden = !menu.hidden;
    });
    document.addEventListener('click', function (e) { if (!box.contains(e.target)) menu.hidden = true; });
})();
</script>
