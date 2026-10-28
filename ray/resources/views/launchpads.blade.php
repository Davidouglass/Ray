@extends('layouts.app')

@section('content')
@php
    $stages = [
        'new'      => ['Pump.fun · New',            'ri-seedling-line'],
        'final'    => ['Pump.fun · Final Stretch',  'ri-fire-line'],
        'migrated' => ['Pumpswap · Migrated',       'ri-rocket-2-line'],
    ];
@endphp

<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-rocket-line"></i></div>
        <div>
            <h1>Launchpads</h1>
            <div class="fx-sub">Fresh launches from Pump.fun and graduates on Pumpswap.</div>
        </div>
        <div class="fx-spacer"></div>
        <a class="fx-btn ghost sm" href="https://pump.fun" target="_blank" rel="noopener"><i class="ri-external-link-line"></i>Pump.fun</a>
    </div>

    <div class="fx-tabs">
        @foreach ($stages as $key => $s)
            <button class="fx-tab {{ $loop->first ? 'active' : '' }}" data-stage="{{ $key }}"><i class="{{ $s[1] }}"></i> {{ $s[0] }}</button>
        @endforeach
    </div>

    @foreach ($stages as $key => $s)
        <div class="fx-panel" data-stage-panel="{{ $key }}" style="{{ $loop->first ? '' : 'display:none' }}">
            <h2><i class="{{ $s[1] }}"></i> {{ $s[0] }}</h2>
            @php $rows = $tabs[$key] ?? []; @endphp
            @if (empty($rows))
                <div class="fx-empty"><i class="ri-signal-wifi-off-line"></i>No launches in this stage right now.</div>
            @else
                <div style="overflow-x:auto">
                <table class="fx-table">
                    <thead><tr><th>Token</th><th>Price</th><th>5m</th><th>MC</th><th>Stage</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td>
                                    <a class="fx-token" href="/token/{{ $r['address'] }}">
                                        <img class="fx-token-logo" style="width:30px;height:30px;border-radius:8px" src="{{ $r['img'] }}" alt="">
                                        <span><span class="fx-sym">{{ $r['sym'] }}</span><br><span class="fx-token-tick">{{ $r['name'] }}</span></span>
                                    </a>
                                </td>
                                <td>{{ $r['price'] }}</td>
                                <td class="{{ $r['up'] ? 'fx-up' : 'fx-down' }} fx-pct small">{{ $r['pct'] }}</td>
                                <td>{{ $r['mc'] }}</td>
                                <td>
                                    <div class="fx-bar" style="width:80px"><span style="width:{{ (int) $r['progress'] }}%"></span></div>
                                    <span class="fx-token-tick">{{ $r['progress'] }}%</span>
                                </td>
                                <td><a class="fx-btn sm" href="/token/{{ $r['address'] }}">Trade</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-stage]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var key = btn.dataset.stage;
        document.querySelectorAll('[data-stage]').forEach(function (t) { b: {
            t.classList.toggle('active', t === document.querySelector('[data-stage="' + key + '"]'));
        }});
        document.querySelectorAll('[data-stage-panel]').forEach(function (p) {
            p.style.display = p.dataset.stagePanel === key ? '' : 'none';
        });
    });
});
</script>
@endpush
