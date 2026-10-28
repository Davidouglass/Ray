@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-live-line"></i></div>
        <div>
            <h1>Streams</h1>
            <div class="fx-sub">Go live, talk through trades and watch other traders in real time.</div>
        </div>
        <div class="fx-spacer"></div>
        <a class="fx-btn" href="/streams/create"><i class="ri-vidicon-line"></i>Go live</a>
    </div>

    @if (session('ok'))<div class="fx-alert ok">{{ session('ok') }}</div>@endif

    <div class="fx-panel">
        <div class="fx-stat-row">
            <div class="fx-stat"><b class="fx-up">{{ $liveCount }}</b><span>Live now</span></div>
            <div class="fx-stat"><b>{{ number_format($viewers) }}</b><span>Watching</span></div>
            <div class="fx-stat"><b>{{ $mine->count() }}</b><span>Your streams</span></div>
        </div>
    </div>

    <div class="fx-panel">
        <h2><i class="ri-live-line"></i> Live now</h2>
        @if ($live->isEmpty())
            <div class="fx-empty">
                <i class="ri-vidicon-line"></i>
                Nobody is live right now. <a href="/streams/create" style="color:var(--blue)">Be the first to go live</a>.
            </div>
        @else
            <div class="fx-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
                @foreach ($live as $s)
                    <a class="fx-card" href="/streams/{{ $s->id }}">
                        <div class="sr-thumb"><i class="ri-live-line"></i></div>
                        <div class="fx-card-top">
                            <div class="sr-avatar">{{ strtoupper(substr($s->user->name ?? 'U', 0, 1)) }}</div>
                            <div style="flex:1;min-width:0">
                                <div class="fx-token-name">{{ $s->user->name ?? 'Trader' }}</div>
                                <div class="fx-token-tick">{{ $s->uptime() }} ago · {{ number_format($s->viewers) }} watching</div>
                            </div>
                            <span class="sr-live"><i></i>Live</span>
                        </div>
                        <div class="fx-sym" style="font-size:13.5px">{{ $s->title }}</div>
                        @if ($s->description)<div class="fx-token-tick" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{{ $s->description }}</div>@endif
                        @if ($s->token_symbol)<span class="fx-chip"><i class="ri-coin-line"></i>{{ $s->token_symbol }}</span>@endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="fx-panel">
        <h2><i class="ri-history-line"></i> Your streams</h2>
        @if ($mine->isEmpty())
            <div class="fx-empty"><i class="ri-vidicon-line"></i>You have not streamed yet. <a href="/streams/create" style="color:var(--blue)">Start your first stream</a>.</div>
        @else
            <div class="fx-list">
                @foreach ($mine as $s)
                    <div class="fx-list-row">
                        @if ($s->isLive())
                            <span class="sr-live"><i></i>Live</span>
                        @else
                            <span class="sr-badge-ended">Ended</span>
                        @endif
                        <div style="flex:1;min-width:0">
                            <div class="fx-sym">{{ $s->title }}</div>
                            <div class="fx-token-tick">{{ $s->started_at ? $s->started_at->diffForHumans() : '' }} · {{ number_format($s->viewers) }} views</div>
                        </div>
                        <a class="fx-btn sm ghost" href="/streams/{{ $s->id }}"><i class="ri-play-line"></i>Open</a>
                        @if ($s->isLive())
                            <form method="POST" action="/streams/{{ $s->id }}/end">
                                @csrf
                                <button class="fx-btn sm" type="submit" style="background:#EC397A"><i class="ri-stop-circle-line"></i>End</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
