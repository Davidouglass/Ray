@php
    $active = $active ?? '';
    $items = [
        ['ri-fire-line',        'Trending',   'discover',    true],
        ['ri-pulse-line',       'Pulse',      'pulse',       true],
        ['ri-exchange-line',    'Perpetuals', 'perpetuals',  true],
        ['ri-user-follow-line', 'Copy',       'copy-trading', true],
        ['ri-folder-user-line', 'Account',    'portfolio',   true],
    ];
    $flagMap = ['perpetuals' => 'perpetuals', 'copy-trading' => 'copy-trading'];
    $items = array_values(array_filter($items, fn ($m) => ! isset($flagMap[$m[2]]) || \App\Models\SiteSetting::flag($flagMap[$m[2]])));
@endphp
{{-- ================= MOBILE NAVBAR ================= --}}
<div class="mobile-navbar">
    @foreach ($items as $m)
        <a href="/{{ $m[2] }}" class="mnb-item {{ $active === $m[2] ? 'is-active' : '' }}"><i class="{{ $m[0] }}"></i><span>{{ $m[1] }}</span></a>
    @endforeach
</div>