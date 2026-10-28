@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-twitter-x-line"></i></div>
        <div>
            <h1>Social</h1>
            <div class="fx-sub">Tokens making noise across X, Telegram and the web — right now.</div>
        </div>
        <div class="fx-spacer"></div>
        <div class="fx-row">
            <a class="fx-btn ghost sm" href="https://x.com/axiomexchange" target="_blank" rel="noopener"><i class="ri-twitter-x-line"></i>X</a>
            <a class="fx-btn ghost sm" href="https://discord.gg/axiomtrade" target="_blank" rel="noopener"><i class="ri-discord-line"></i>Discord</a>
        </div>
    </div>

    @if (empty($feed))
        <div class="fx-panel"><div class="fx-empty"><i class="ri-twitter-x-line"></i>Social signals unavailable right now.</div></div>
    @else
        <div class="fx-grid">
            @foreach ($feed as $t)
                <div class="fx-card">
                    <div class="fx-card-top">
                        <img class="fx-token-logo" src="{{ $t['img'] }}" alt="">
                        <div style="flex:1;min-width:0">
                            <div class="fx-token-name">{{ $t['sym'] }}</div>
                            <div class="fx-token-tick">{{ $t['venue'] }}</div>
                        </div>
                        <span class="{{ $t['up'] ? 'fx-up' : 'fx-down' }} fx-pct small">{{ $t['pct'] }}</span>
                    </div>

                    <div class="fx-row" style="justify-content:space-between">
                        <span class="fx-token-tick">MC {{ $t['mc'] }}</span>
                        <span class="fx-sym">{{ $t['price'] }}</span>
                    </div>

                    <div class="fx-actions">
                        <a class="fx-btn sm" href="/token/{{ $t['address'] }}"><i class="ri-flashlight-line"></i>Trade</a>
                        @if ($t['twitter'])<a class="fx-icon-btn" href="{{ $t['twitter'] }}" target="_blank" rel="noopener" title="X"><i class="ri-twitter-x-line"></i></a>@endif
                        @if ($t['telegram'])<a class="fx-icon-btn" href="{{ $t['telegram'] }}" target="_blank" rel="noopener" title="Telegram"><i class="ri-telegram-line"></i></a>@endif
                        @if ($t['web'])<a class="fx-icon-btn" href="{{ $t['web'] }}" target="_blank" rel="noopener" title="Website"><i class="ri-global-line"></i></a>@endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
