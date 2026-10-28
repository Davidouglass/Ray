@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-megaphone-line"></i></div>
        <div>
            <h1>Callouts</h1>
            <div class="fx-sub">Post and track token calls — yours and the community's.</div>
        </div>
    </div>

    @if (session('ok'))<div class="fx-alert ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="fx-alert err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="/callouts" class="fx-panel fx-row" style="align-items:flex-end">
        @csrf
        <div style="flex:2 1 320px;min-width:0">
            <div class="fx-sub" style="margin-bottom:6px">Token address</div>
            <input class="fx-input" style="width:100%" name="address" placeholder="Solana token mint address" value="{{ old('address') }}" required>
        </div>
        <div style="flex:1 1 180px;min-width:0">
            <div class="fx-sub" style="margin-bottom:6px">Ticker</div>
            <input class="fx-input" style="width:100%" name="symbol" placeholder="BONK" value="{{ old('symbol') }}">
        </div>
        <button class="fx-btn" type="submit"><i class="ri-megaphone-line"></i>Post callout</button>
    </form>

    <div class="fx-two">
        <div class="fx-panel">
            <h2><i class="ri-megaphone-line"></i> Your callouts</h2>
            @if ($callouts->isEmpty())
                <div class="fx-empty"><i class="ri-megaphone-line"></i>No callouts yet. Post one above.</div>
            @else
                <div class="fx-list">
                    @foreach ($callouts as $c)
                        <a class="fx-list-row" href="/token/{{ $c['address'] }}">
                            @if ($c['img'])<img class="fx-token-logo" style="width:28px;height:28px;border-radius:8px" src="{{ $c['img'] }}" alt="">@endif
                            <div style="flex:1;min-width:0">
                                <div class="fx-sym">{{ $c['symbol'] }}</div>
                                <div class="fx-token-tick">{{ $c['name'] ?: 'Solana token' }}</div>
                            </div>
                            <span class="{{ $c['change'] >= 0 ? 'fx-up' : 'fx-down' }} fx-pct small">{{ $c['change'] >= 0 ? '+' : '' }}{{ number_format($c['change'], 2) }}%</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="fx-panel">
            <h2><i class="ri-fire-line"></i> Community callouts</h2>
            <div class="fx-list">
                @foreach ($community as $t)
                    <a class="fx-list-row" href="/token/{{ $t['address'] }}">
                        <img class="fx-token-logo" style="width:28px;height:28px;border-radius:8px" src="{{ $t['img'] }}" alt="">
                        <div style="flex:1;min-width:0">
                            <div class="fx-sym">{{ $t['sym'] }}</div>
                            <div class="fx-token-tick">{{ $t['name'] }}</div>
                        </div>
                        <span class="{{ $t['up'] ? 'fx-up' : 'fx-down' }} fx-pct small">{{ $t['pct'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
