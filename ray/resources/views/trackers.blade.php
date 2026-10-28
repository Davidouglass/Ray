@extends('layouts.app')

@section('content')
@php
    $usd = function ($v) { $v=(float)$v; if ($v>=1e6) return '$'.number_format($v/1e6,2).'M'; if ($v>=1e3) return '$'.number_format($v/1e3,2).'K'; return '$'.number_format($v,2); };
@endphp

<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-radar-line"></i></div>
        <div>
            <h1>Trackers</h1>
            <div class="fx-sub">Follow any Solana wallet and watch its live balance, holdings and activity.</div>
        </div>
    </div>

    @if (session('ok'))<div class="fx-alert ok">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="fx-alert err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="/trackers" class="fx-panel fx-row" style="align-items:flex-end">
        @csrf
        <div style="flex:2 1 320px;min-width:0">
            <div class="fx-sub" style="margin-bottom:6px">Wallet address</div>
            <input class="fx-input" style="width:100%" name="address" placeholder="e.g. 5Q544fKrFoe6tsEbD7S8EmxGTJYAKtTVhAW5Q5pge4j1" value="{{ old('address') }}" required>
        </div>
        <div style="flex:1 1 180px;min-width:0">
            <div class="fx-sub" style="margin-bottom:6px">Label</div>
            <input class="fx-input" style="width:100%" name="label" placeholder="Smart money" value="{{ old('label') }}">
        </div>
        <button class="fx-btn" type="submit"><i class="ri-add-line"></i>Add wallet</button>
    </form>

    <div class="fx-panel">
        <h2><i class="ri-group-line"></i> Tracked wallets</h2>
        @if ($rows->isEmpty())
            <div class="fx-empty"><i class="ri-radar-line"></i>No wallets yet. Add one above to start tracking.</div>
        @else
            <table class="fx-table">
                <thead>
                    <tr><th>Wallet</th><th>SOL</th><th>Tokens</th><th>Value</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td>
                                <div class="fx-sym">{{ $r['label'] ?: 'Wallet' }}</div>
                                <a class="fx-addr" href="https://solscan.io/account/{{ $r['address'] }}" target="_blank" rel="noopener">{{ substr($r['address'],0,6) }}…{{ substr($r['address'],-6) }}</a>
                            </td>
                            <td>{{ number_format($r['sol'], 4) }}</td>
                            <td>{{ $r['tokens'] }}</td>
                            <td><b class="fx-sym">{{ $usd($r['total']) }}</b></td>
                            <td>
                                <div class="fx-actions" style="justify-content:flex-end">
                                    <a class="fx-icon-btn" href="https://solscan.io/account/{{ $r['address'] }}" target="_blank" rel="noopener" title="Open on Solscan"><i class="ri-external-link-line"></i></a>
                                    <form method="POST" action="/trackers/{{ $r['id'] }}" onsubmit="return confirm('Remove this wallet?')">
                                        @csrf @method('DELETE')
                                        <button class="fx-icon-btn" title="Remove"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </div>
                            </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
@endsection
