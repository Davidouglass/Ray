@extends('layouts.app')

@section('content')
@php
    $d = $detail;
    $has = empty($detail['error']);
    $usd = function ($v) {
        $v = (float) $v;
        if ($v <= 0) return '$0';
        if ($v >= 1e9) return '$' . number_format($v / 1e9, 2) . 'B';
        if ($v >= 1e6) return '$' . number_format($v / 1e6, 2) . 'M';
        if ($v >= 1e3) return '$' . number_format($v / 1e3, 2) . 'K';
        return '$' . number_format($v, 0);
    };
    $price = function ($v) {
        $v = (float) $v;
        if ($v >= 1000) return '$' . number_format($v, 2);
        if ($v >= 1) return '$' . number_format($v, 3);
        if ($v >= 0.01) return '$' . number_format($v, 5);
        return '$' . rtrim(rtrim(sprintf('%.10f', $v), '0'), '.');
    };
@endphp

<div class="fx-page">
    <div class="fx-head">
        <a href="/pulse" class="fx-icon-btn" title="Back"><i class="ri-arrow-left-line"></i></a>
        @if (!empty($detail['img']))
            <img src="{{ $detail['img'] }}" alt="" class="fx-token-logo" style="width:46px;height:46px">
        @else
            <div class="fx-ic"><i class="ri-coin-line"></i></div>
        @endif
        <div>
            <h1>{{ $detail['name'] ?? 'Unknown token' }} <span class="fx-token-tick">{{ $detail['symbol'] ?? '' }}</span></h1>
            <div class="fx-sub">
                <span class="fx-chip"><i class="ri-link"></i>{{ ucfirst($detail['chain'] ?? 'solana') }}</span>
                @if (!empty($detail['dex']))<span class="fx-chip">{{ $detail['dex'] }}</span>@endif
                @if (!empty($detail['createdAt']))<span class="fx-chip">Age {{ floor((time()-$detail['createdAt'])/86400) }}d</span>@endif
            </div>
        </div>
        <div class="fx-spacer"></div>
        <div style="text-align:right">
            <div style="font-size:24px;font-weight:750;color:var(--text-primary)">{{ $price($detail['price'] ?? 0) }}</div>
            @php $pc = (float)($detail['chg']['h24'] ?? 0); @endphp
            <div class="fx-pct {{ $pc >= 0 ? 'fx-up' : 'fx-down' }}">{{ $pc >= 0 ? '+' : '' }}{{ number_format($pc,2) }}% 24h</div>
        </div>
    </div>

    @if (!empty($detail['error']))
        <div class="fx-panel"><div class="fx-empty"><i class="ri-search-eye-line"></i>{{ $detail['error'] }}<br><span class="fx-addr">{{ $detail['address'] ?? '' }}</span></div></div>
    @else
        <div class="fx-panel">
            <div class="fx-stat-row">
                <div class="fx-stat"><b>{{ $usd($detail['mcap']) }}</b><span>Market cap</span></div>
                <div class="fx-stat"><b>{{ $usd($detail['liq']) }}</b><span>Liquidity</span></div>
                <div class="fx-stat"><b>{{ $usd($detail['vol24']) }}</b><span>Volume 24h</span></div>
                <div class="fx-stat"><b class="fx-up">{{ number_format($detail['buys24']) }}</b><span>Buys 24h</span></div>
                <div class="fx-stat"><b class="fx-down">{{ number_format($detail['sells24']) }}</b><span>Sells 24h</span></div>
                <div class="fx-stat"><b>{{ $usd($detail['fdv']) }}</b><span>FDV</span></div>
            </div>
        </div>

        <div class="fx-two">
            <div class="fx-panel fx-chart-panel">
                <div class="fx-chart-head">
                    <h2><i class="ri-line-chart-line"></i> Price</h2>
                    <div class="fx-tf" id="tvTf">
                        <button type="button" data-tf="minute" data-agg="1" data-limit="150">1m</button>
                        <button type="button" data-tf="minute" data-agg="5" data-limit="150">5m</button>
                        <button type="button" data-tf="minute" data-agg="15" data-limit="150">15m</button>
                        <button type="button" class="active" data-tf="hour" data-agg="1" data-limit="168">1H</button>
                        <button type="button" data-tf="hour" data-agg="4" data-limit="168">4H</button>
                        <button type="button" data-tf="day" data-agg="1" data-limit="120">1D</button>
                    </div>
                </div>
                <div class="fx-chart-wrap">
                    <div id="tvchart" class="fx-chart" data-mint="{{ $detail['address'] }}" data-symbol="{{ $detail['symbol'] ?? '' }}" data-pool="{{ $detail['pool'] ?? '' }}"></div>
                    <div class="fx-chart-status" id="tvchart-status"><i class="ri-loader-4-line"></i> Loading chart…</div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:14px">
                @include('partials.trade-panel', [
                    'mint' => $detail['address'],
                    'symbol' => $detail['symbol'] ?? 'token',
                    'price' => $detail['price'] ?? 0,
                ])

                <div class="fx-panel">
                    <h2><i class="ri-shield-check-line"></i> Safety &amp; holders</h2>
                    @php $safe = $detail['safety'] ?? []; @endphp
                    @if (!empty($safe))
                        <div class="fx-kv"><span>RugCheck score</span><b>{{ $safe['score'] ?? '—' }}/100</b></div>
                        <div class="fx-kv"><span>Holders</span><b>{{ number_format($safe['holders'] ?? 0) }}</b></div>
                        <div class="fx-kv"><span>Top holder</span><b>{{ $safe['top1'] ?? 0 }}%</b></div>
                        <div class="fx-kv"><span>Top 10</span><b>{{ $safe['top10'] ?? 0 }}%</b></div>
                        @if (!empty($safe['risks']))
                            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:6px">
                                @foreach ($safe['risks'] as $r)
                                    <span class="fx-chip"><i class="ri-alert-line"></i>{{ $r['name'] }}</span>
                                @endforeach
                            </div>
                        @else
                            <div class="fx-alert ok" style="margin-top:10px">No risks flagged.</div>
                        @endif
                    @else
                        <div class="fx-empty"><i class="ri-shield-line"></i>No safety report available.</div>
                    @endif
                </div>

                <div class="fx-panel">
                    <h2><i class="ri-link"></i> Contract</h2>
                    <div class="fx-kv"><span>Address</span>
                        <b><span class="fx-addr" id="tokaddr">{{ $detail['address'] }}</span></b>
                    </div>
                    <button class="fx-btn ghost sm" data-copy="{{ $detail['address'] }}"><i class="ri-file-copy-line"></i>Copy address</button>
                </div>

                @if (!empty($detail['socials']) || !empty($detail['websites']))
                <div class="fx-panel">
                    <h2><i class="ri-earth-line"></i> Community</h2>
                    <div class="fx-row">
                        @foreach ($detail['websites'] ?? [] as $w)
                            <a class="fx-chip" href="{{ is_array($w) ? ($w['url'] ?? '#') : $w }}" target="_blank" rel="noopener"><i class="ri-global-line"></i>{{ is_array($w) ? ($w['label'] ?? 'Website') : 'Website' }}</a>
                        @endforeach
                        @foreach ($detail['socials'] ?? [] as $s)
                            <a class="fx-chip" href="{{ $s['url'] ?? '#' }}" target="_blank" rel="noopener"><i class="ri-links-line"></i>{{ ucfirst($s['type'] ?? 'social') }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
    @endif
</div>
@endsection

@push('styles')
<style>
    .fx-chart-panel { display: flex; flex-direction: column; min-width: 0; }
    .fx-chart-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .fx-chart-head h2 { margin-bottom: 0; }
    .fx-tf { display: flex; gap: 2px; background: var(--bg-tertiary); border: 1px solid var(--primary-stroke); border-radius: 8px; padding: 2px; }
    .fx-tf button { appearance: none; border: 0; background: transparent; color: var(--text-tertiary); font: 600 12px/1 var(--font-geist-mono); padding: 6px 9px; border-radius: 6px; cursor: pointer; }
    .fx-tf button:hover { color: var(--text-primary); }
    .fx-tf button.active { background: var(--blue); color: #fff; }
    .fx-chart-wrap { position: relative; margin-top: 12px; height: 380px; min-width: 0; }
    .fx-chart { position: absolute; inset: 0; overflow: hidden; }
    .fx-chart-status { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 8px; color: var(--text-tertiary); font-size: 13px; pointer-events: none; }
    .fx-chart-status i { animation: fxspin 1s linear infinite; }
    .fx-chart-status.hidden { display: none; }
    @keyframes fxspin { to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/lightweight-charts@4.1.3/dist/lightweight-charts.standalone.production.js"></script>
<script src="{{ asset('js/token-chart.js') }}?v={{ @filemtime(public_path('js/token-chart.js')) }}" defer></script>
<script src="{{ asset('js/axiom-trade.js') }}" defer></script>
<script>
document.querySelectorAll('[data-copy]').forEach(el => el.addEventListener('click', () => {
    navigator.clipboard.writeText(el.dataset.copy);
    const t = el.innerHTML; el.innerHTML = 'Copied!';
    setTimeout(() => el.innerHTML = t, 1200);
}));
</script>
@endpush
