@extends('layouts.app')

@section('content')
@php
    $tabsMeta = [
        'presets'       => ['Presets',       'ri-list-settings-line'],
        'appearance'    => ['Appearance',    'ri-palette-line'],
        'notifications' => ['Notifications', 'ri-notification-3-line'],
        'data'          => ['Data & Auto',   'ri-refresh-line'],
        'support'       => ['Support',       'ri-telegram-line'],
    ];
    $tab = $tab ?? 'presets';
@endphp

<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-settings-3-line"></i></div>
        <div>
            <h1>Settings</h1>
            <div class="fx-sub">Workspace presets, appearance, notifications, live data and support.</div>
        </div>
    </div>

    <div class="fx-tabs">
        @foreach ($tabsMeta as $key => $t)
            <button class="fx-tab {{ $tab === $key ? 'active' : '' }}" data-tab="{{ $key }}"><i class="{{ $t[1] }}"></i> {{ $t[0] }}</button>
        @endforeach
    </div>

    <div class="fx-panel" data-tab-panel="presets" style="{{ $tab === 'presets' ? '' : 'display:none' }}">
        <h2><i class="ri-list-settings-line"></i> Workspace presets</h2>
        <div class="fx-grid">
            @foreach ([1 => ['Default', 'ri-layout-grid-line'], 2 => ['Scalper', 'ri-layout-masonry-line'], 3 => ['Degen', 'ri-layout-masonry-line']] as $n => $meta)
                <button class="fx-card setting-preset" data-preset="{{ $n }}" style="cursor:pointer;text-align:left">
                    <div class="fx-card-top">
                        <div class="fx-ic" style="width:38px;height:38px;font-size:18px;border-radius:9px"><i class="{{ $meta[1] }}"></i></div>
                        <div><div class="fx-token-name">PRESET {{ $n }}</div><div class="fx-token-tick">{{ $meta[0] }} layout</div></div>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <div class="fx-panel" data-tab-panel="appearance" style="{{ $tab === 'appearance' ? '' : 'display:none' }}">
        <h2><i class="ri-palette-line"></i> Appearance</h2>
        <div class="fx-sub" style="margin-bottom:8px">Accent</div>
        <div class="fx-row" style="margin-bottom:16px">
            @foreach (['#526FFF' => 'Veridian Blue', '#14F195' => 'Solana', '#F0B90B' => 'BNB', '#BF0DF5' => 'Neon'] as $hex => $label)
                <button class="setting-accent" data-accent="{{ $hex }}" title="{{ $label }}"
                        style="width:34px;height:34px;border-radius:9px;border:2px solid var(--primary-stroke);background:{{ $hex }};cursor:pointer"></button>
            @endforeach
        </div>
        <h2><i class="ri-layout-2-line"></i> Density</h2>
        <label class="fx-chip" style="cursor:pointer"><input type="checkbox" class="setting-compact"> Compact rows</label>
    </div>

    <div class="fx-panel" data-tab-panel="notifications" style="{{ $tab === 'notifications' ? '' : 'display:none' }}">
        <h2><i class="ri-notification-3-line"></i> Notifications</h2>
        <div class="fx-list">
            <label class="fx-list-row" style="cursor:pointer;justify-content:space-between"><span>Token callouts</span><input type="checkbox" class="setting-notif" data-key="notif_callouts"></label>
            <label class="fx-list-row" style="cursor:pointer;justify-content:space-between"><span>Wallet activity (friends)</span><input type="checkbox" class="setting-notif" data-key="notif_wallets"></label>
            <label class="fx-list-row" style="cursor:pointer;justify-content:space-between"><span>New launches</span><input type="checkbox" class="setting-notif" data-key="notif_launches"></label>
            <label class="fx-list-row" style="cursor:pointer;justify-content:space-between"><span>Rewards &amp; points</span><input type="checkbox" class="setting-notif" data-key="notif_rewards"></label>
        </div>
    </div>

    <div class="fx-panel" data-tab-panel="data" style="{{ $tab === 'data' ? '' : 'display:none' }}">
        <h2><i class="ri-refresh-line"></i> Data &amp; auto-refresh</h2>
        <div class="fx-list">
            <label class="fx-list-row" style="cursor:pointer;justify-content:space-between"><span>Auto-refresh live pages</span><input type="checkbox" class="setting-auto"></label>
            <div class="fx-list-row" style="justify-content:space-between">
                <span>Refresh interval</span>
                <select class="fx-input setting-interval" style="height:34px">
                    <option value="5">5 seconds</option>
                    <option value="15" selected>15 seconds</option>
                    <option value="30">30 seconds</option>
                    <option value="60">60 seconds</option>
                </select>
            </div>
        </div>
        <div class="fx-token-tick" style="margin-top:10px">Saved locally in your browser. Applies to Streams and live feeds.</div>
    </div>

    <div class="fx-panel" data-tab-panel="support" style="{{ $tab === 'support' ? '' : 'display:none' }}">
        <h2><i class="ri-customer-service-2-line"></i> Support team</h2>
        @if ($supportUrl = \App\Models\SiteSetting::supportUrl())
            <div class="fx-sub" style="margin-bottom:14px">Talk to a real person from the Veridian support team — wallets, deposits, copy trading or anything else.</div>
            <div class="fx-list">
                <a class="fx-list-row" href="{{ $supportUrl }}" target="_blank" rel="noopener" style="cursor:pointer">
                    <span><i class="ri-telegram-line" style="color:var(--blue)"></i> Message the support team on Telegram</span>
                    <i class="ri-arrow-right-up-line"></i>
                </a>
            </div>
            <a class="fx-btn" style="margin-top:14px" href="{{ $supportUrl }}" target="_blank" rel="noopener"><i class="ri-telegram-line"></i> Open support chat</a>
        @else
            <div class="fx-sub">Support is currently unavailable. Please check back later.</div>
        @endif
    </div>

    <div class="fx-row"><button class="fx-btn" id="settingsSave"><i class="ri-save-line"></i>Save settings</button><span class="fx-alert ok" id="settingsSaved" style="display:none">Saved</span></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var KEY = 'axiom-settings';
    var defaults = { preset: 1, accent: '#526fff', compact: false, auto: true, interval: 15 };
    function load() { try { return Object.assign({}, defaults, JSON.parse(localStorage.getItem('axiom-settings') || '{}')); } catch (e) { return Object.assign({}, defaults); } }
    var s = load();

    function apply() {
        if (s.accent) {
            document.documentElement.style.setProperty('--blue', s.accent);
            document.documentElement.style.setProperty('--blue-hover', s.accent);
        }
        document.body.classList.toggle('is-compact', !!s.compact);
    }
    function save() { localStorage.setItem('axiom-settings', JSON.stringify(s)); apply(); }

    // tabs
    document.querySelectorAll('[data-tab]').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('[data-tab]').forEach(function (x) { x.classList.toggle('active', x === b); });
            document.querySelectorAll('[data-tab-panel]').forEach(function (p) { p.style.display = p.dataset.tabPanel === b.dataset.tab ? '' : 'none'; });
        });
    });

    // presets
    document.querySelectorAll('.setting-preset').forEach(function (b) {
        b.addEventListener('click', function () {
            s.preset = parseInt(b.dataset.preset, 10);
            document.querySelectorAll('.setting-preset').forEach(function (x) { x.style.borderColor = x === b ? 'var(--blue)' : 'var(--primary-stroke)'; });
            save();
            window.Axiom && window.Axiom.toast('Preset ' + s.preset + ' selected');
        });
    });
    document.querySelectorAll('[data-preset]').forEach(function (b) { if (+b.dataset.preset === s.preset) b.style.borderColor = 'var(--blue)'; });

    // accent
    document.querySelectorAll('[data-accent]').forEach(function (b) {
        b.addEventListener('click', function () { s.accent = b.dataset.accent; save(); });
    });

    // compact
    var compact = document.querySelector('.setting-compact');
    if (compact) { compact.checked = !!s.compact; compact.addEventListener('change', function () { s.compact = this.checked; save(); }); }

    // notifications
    document.querySelectorAll('.setting-notif').forEach(function (c) {
        c.checked = !!s[c.dataset.key];
        c.addEventListener('change', function () { s[c.dataset.key] = c.checked; save(); });
    });

    // data
    var autoEl = document.querySelector('.setting-auto');
    if (autoEl) { autoEl.checked = s.auto !== false; autoEl.addEventListener('change', function () { s.auto = autoEl.checked; save(); }); }
    var intEl = document.querySelector('.setting-interval');
    if (intEl) { intEl.value = String(s.interval || 15); intEl.addEventListener('change', function () { s.interval = parseInt(intEl.value, 10); save(); }); }

    var sb = document.getElementById('settingsSave');
    if (sb) sb.addEventListener('click', function () {
        save();
        window.Axiom && window.Axiom.toast('Settings saved');
    });

    apply();
})();
</script>
@endpush
