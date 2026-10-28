@extends('layouts.app')

@section('content')
@php
    $usd = function ($v) { $v=(float)$v; if ($v>=1e6) return '$'.number_format($v/1e6,2).'M'; if ($v>=1e3) return '$'.number_format($v/1e3,2).'K'; return '$'.number_format($v,2); };
@endphp

<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-group-3-line"></i></div>
        <div>
            <h1>Friends</h1>
            <div class="fx-sub">Follow wallets you trust and keep an eye on what they hold.</div>
        </div>
    </div>

    @if (session('ok'))<div class="fx-alert ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="fx-alert err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="/friends" class="fx-panel fx-row" style="align-items:flex-end">
        @csrf
        <div style="flex:2 1 320px;min-width:0">
            <div class="fx-sub" style="margin-bottom:6px">Wallet address</div>
            <input class="fx-input" style="width:100%" name="address" placeholder="e.g. 5Q544fKrFoe6tsEbD7S8EmxGTJYAKtTVhAW5Q5pge4j1" value="{{ old('address') }}" required>
        </div>
        <div style="flex:1 1 180px;min-width:0">
            <div class="fx-sub" style="margin-bottom:6px">Name</div>
            <input class="fx-input" style="width:100%" name="label" placeholder="Smart money" value="{{ old('label') }}">
        </div>
        <button class="fx-btn" type="submit"><i class="ri-user-add-line"></i>Add friend</button>
    </form>

    @if ($friends->isEmpty())
        <div class="fx-panel">
            <div class="fx-empty"><i class="ri-group-3-line"></i>No friends yet. Add a wallet above to start following it.</div>
        </div>
    @else
        <div class="fx-grid">
            @foreach ($friends as $f)
                <div class="fx-card">
                    <div class="fx-card-top">
                        <div class="fx-ic" style="width:38px;height:38px;font-size:18px;border-radius:9px"><i class="ri-user-3-line"></i></div>
                        <div style="flex:1;min-width:0">
                            <div class="fx-token-name">{{ $f['label'] ?: 'Wallet' }}</div>
                            <a class="fx-addr" href="https://solscan.io/account/{{ $f['address'] }}" target="_blank" rel="noopener">{{ substr($f['address'],0,6) }}…{{ substr($f['address'],-6) }}</a>
                        </div>
                    </div>

                    @if ($f['error'])
                        <div class="fx-token-tick">Could not load balances.</div>
                    @else
                        <div class="fx-kv"><span>SOL</span><b>{{ number_format($f['sol'], 4) }}</b></div>
                        <div class="fx-kv"><span>Holdings</span><b>{{ count($f['tokens']) }}</b></div>
                        <div class="fx-kv"><span>Value</span><b class="fx-sym">{{ $usd($f['total']) }}</b></div>
                    @endif

                    <div class="fx-actions" style="justify-content:flex-end">
                        <a class="fx-icon-btn" href="https://solscan.io/account/{{ $f['address'] }}" target="_blank" rel="noopener" title="Solscan"><i class="ri-external-link-line"></i></a>
                        <form method="POST" action="/friends/{{ $f['id'] }}" onsubmit="return confirm('Remove this friend?')">
                            @csrf @method('DELETE')
                            <button class="fx-icon-btn" title="Remove"><i class="ri-user-unfollow-line"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
