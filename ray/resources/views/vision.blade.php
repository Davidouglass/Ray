@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-eye-line"></i></div>
        <div>
            <h1>Vision</h1>
            <div class="fx-sub">Onchain market intelligence — trending tokens, fresh pools and an instant token scanner.</div>
        </div>
    </div>

    <div class="fx-panel">
        <h2><i class="ri-scan-line"></i> Token scanner</h2>
        <div class="fx-row">
            <input id="scanInput" class="fx-input" style="flex:1 1 320px" placeholder="Paste any Solana token address…">
            <button id="scanBtn" class="fx-btn"><i class="ri-search-eye-line"></i>Scan</button>
        </div>
        <div id="scanOut" style="margin-top:14px"></div>
    </div>

    <div class="fx-two">
        <div class="fx-panel">
            <h2><i class="ri-fire-line"></i> Trending tokens</h2>
            @if (empty($trending))
                <div class="fx-empty"><i class="ri-signal-wifi-off-line"></i>No trending data.</div>
            @else
                <div style="overflow-x:auto">
                <table class="fx-table">
                    <thead><tr><th>Token</th><th>Price</th><th>24h</th><th>MCap</th><th>Liquidity</th><th>Vol 24h</th></tr></thead>
                    <tbody>
                    @foreach ($trending as $t)
                        <tr onclick="location.href='/token/{{ $t['address'] }}'" style="cursor:pointer">
                            <td>
                                <div class="fx-token" style="gap:9px">
                                    <img class="fx-token-logo" style="width:30px;height:30px;border-radius:8px" src="{{ $t['img'] }}" alt="">
                                    <div><div class="fx-sym">{{ $t['sym'] }}</div><div class="fx-token-tick">{{ strtoupper($t['chain']) }} · {{ $t['age'] }}</div></div>
                                </div>
                            </td>
                            <td>{{ $t['price'] }}</td>
                            <td class="{{ $t['up'] ? 'fx-up' : 'fx-down' }}">{{ $t['pct'] }}</td>
                            <td>{{ $t['mc'] }}</td>
                            <td>{{ $t['liq'] }}</td>
                            <td>{{ $t['vol'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>

        <div class="fx-panel">
            <h2><i class="ri-add-circle-line"></i> New pools</h2>
            <div class="fx-list">
                @forelse ($pools as $p)
                    <a class="fx-list-row" href="/token/{{ $p['address'] }}">
                        <img class="fx-token-logo" style="width:30px;height:30px;border-radius:8px" src="{{ $p['img'] }}" alt="">
                        <div style="flex:1;min-width:0">
                            <div class="fx-sym">{{ $p['sym'] }}</div>
                            <div class="fx-token-tick">{{ $p['age'] }} · {{ $p['mc'] }}</div>
                        </div>
                        <span class="fx-pct small {{ $p['up'] ? 'fx-up' : 'fx-down' }}">{{ $p['pct'] }}</span>
                    </a>
                @empty
                    <div class="fx-empty"><i class="ri-inbox-line"></i>No new pools.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const btn = document.getElementById('scanBtn');
const input = document.getElementById('scanInput');
const out = document.getElementById('scanOut');
const fmtUsd = v => { v=+v||0; if (v>=1e9) return '$'+(v/1e9).toFixed(2)+'B'; if (v>=1e6) return '$'+(v/1e6).toFixed(2)+'M'; if (v>=1e3) return '$'+(v/1e3).toFixed(1)+'K'; return '$'+v.toFixed(2); };

async function scan() {
    const address = input.value.trim();
    if (!address) return;
    out.innerHTML = '<div class="fx-empty"><i class="ri-loader-4-line"></i>Scanning…</div>';
    try {
        const r = await fetch('/api/vision/scan?address=' + encodeURIComponent(address), {headers: {'Accept':'application/json'}});
        const d = await r.json();
        if (!r.ok || d.error) { out.innerHTML = '<div class="fx-alert err">' + (d.error || 'Scan failed') + '</div>'; return; }
        const chg = d.chg && d.chg.h24 != null ? (+d.chg.h24).toFixed(2) : '0.00';
        out.innerHTML = `
            <div class="fx-card" style="cursor:default">
                <div class="fx-card-top">
                    ${d.img ? '<img class="fx-token-logo" src="'+d.img+'" alt="">' : '<div class="fx-ic"><i class="ri-coin-line"></i></div>'}
                    <div style="flex:1">
                        <div class="fx-token-name">${d.name} <span class="fx-token-tick">${d.symbol}</span></div>
                        <div class="fx-token-tick">${d.dex} · ${d.chain}</div>
                    </div>
                    <span class="fx-pct ${+chg>=0?'fx-up':'fx-down'}">${+chg>=0?'+':''}${chg}%</span>
                </div>
                <div class="fx-stat-row">
                    <div class="fx-stat"><b>$${d.price}</b><span>Price</span></div>
                    <div class="fx-stat"><b>${fmtUsd(d.mcap)}</b><span>MCap</span></div>
                    <div class="fx-stat"><b>${fmtUsd(d.liq)}</b><span>Liquidity</span></div>
                    <div class="fx-stat"><b>${fmtUsd(d.vol24)}</b><span>Vol 24h</span></div>
                </div>
                <div class="fx-row">
                    <a class="fx-btn sm" href="/token/${d.address}"><i class="ri-line-chart-line"></i>Open token</a>
                    ${d.url ? '<a class="fx-btn ghost sm" href="'+d.url+'" target="_blank" rel="noopener">Chart</a>' : ''}
                </div>
            </div>`;
    } catch (e) {
        out.innerHTML = '<div class="fx-alert err">Network error.</div>';
    }
}
btn.addEventListener('click', scan);
input.addEventListener('keydown', e => { if (e.key === 'Enter') scan(); });
</script>
@endpush
