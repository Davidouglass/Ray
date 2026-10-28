@extends('layouts.app')

@section('content')
@php
    $host = $stream->user->name ?? 'Trader';
@endphp

<div class="fx-page" data-stream-id="{{ $stream->id }}" data-live="{{ $stream->isLive() ? '1' : '0' }}" data-start="{{ optional($stream->started_at)->toIso8601String() }}">
    <div class="fx-head">
        <div class="fx-ic" style="{{ $stream->isLive() ? '' : 'color:var(--text-tertiary);background:var(--bg-tertiary);border-color:var(--primary-stroke)' }}"><i class="ri-{{ $stream->isLive() ? 'live' : 'film' }}-line"></i></div>
        <div>
            <h1>{{ $stream->title }}</h1>
            <div class="fx-sub">{{ $host }} · {{ number_format($stream->viewers) }} watching · <span id="srUptime">{{ $stream->uptime() }}</span></div>
        </div>
        <div class="fx-spacer"></div>
        @if ($stream->isLive())
            <span class="sr-live"><i></i>Live</span>
        @else
            <span class="sr-badge-ended">Ended</span>
        @endif
        <a class="fx-btn ghost sm" href="/streams"><i class="ri-arrow-left-line"></i>All streams</a>
    </div>

    @if (session('ok'))<div class="fx-alert ok">{{ session('ok') }}</div>@endif

    <div class="fx-two">
        <div>
            <div class="sr-player" id="srPlayer">
                @if ($stream->source === 'embed' && $stream->embed_url)
                    <iframe src="{{ $stream->embed_url }}" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                @elseif ($isOwner)
                    <video id="srVideo" autoplay muted playsinline></video>
                    <div id="srCamMsg" class="fx-token-tick" style="position:absolute;inset:auto 0 10px;text-align:center">Starting camera…</div>
                @else
                    <div style="text-align:center;color:var(--text-tertiary)">
                        <i class="ri-vidicon-line" style="font-size:40px;display:block;margin-bottom:8px"></i>
                        Camera stream by {{ $host }}
                        @if (! $stream->isLive()) <div class="fx-token-tick">This stream has ended.</div>@endif
                    </div>
                @endif
            </div>

            @if ($isOwner && $stream->source === 'camera')
                <div class="sr-controls fx-row" style="margin-top:12px">
                    <button class="sr-control" id="srCam" type="button"><i class="ri-camera-line"></i>Camera</button>
                    <button class="sr-control" id="srMic" type="button"><i class="ri-mic-line"></i>Mic</button>
                    <div class="fx-spacer"></div>
                    @if ($stream->isLive())
                        <form method="POST" action="/streams/{{ $stream->id }}/end" onsubmit="return confirm('End this stream?')">
                            @csrf
                            <button class="sr-control" type="submit" style="color:#FF5C7A;border-color:rgba(255,92,122,.4)"><i class="ri-stop-circle-line"></i>End stream</button>
                        </form>
                    @endif
                </div>
            @endif

            @if ($stream->description)
                <div class="fx-panel" style="margin-top:14px">
                    <h2><i class="ri-information-line"></i> About</h2>
                    <div class="fx-sub" style="color:var(--text-secondary);line-height:1.6">{{ $stream->description }}</div>
                    @if ($stream->token_symbol)
                        <div style="margin-top:10px">
                            <a class="fx-chip" href="/token/{{ $stream->token_address }}"><i class="ri-coin-line"></i>{{ $stream->token_symbol }}</a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="fx-panel">
            <h2><i class="ri-chat-3-line"></i> Chat</h2>
            <div class="sr-chat">
                <div class="sr-chat-log" id="srLog"></div>
                <form id="srChatForm" class="fx-row" style="gap:8px">
                    <input class="fx-input" style="flex:1" id="srChatInput" placeholder="Say something…" autocomplete="off" maxlength="200">
                    <button class="fx-btn sm" type="submit"><i class="ri-send-plane-2-line"></i></button>
                </form>
            </div>
            <div class="at-note"><i class="ri-information-line"></i> Chat syncs across tabs in this browser.</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.querySelector('.fx-page[data-stream-id]');
    var id = root.dataset.streamId;
    var live = root.dataset.live === '1';

    // uptime ticker
    var start = root.dataset.start;
    if (start && live) {
        var t0 = new Date(start).getTime();
        setInterval(function () {
            var m = Math.floor((Date.now() - t0) / 60000);
            var el = document.getElementById('srUptime');
            if (el) el.textContent = m >= 60 ? Math.floor(m / 60) + 'h ' + String(m % 60).padStart(2, '0') + 'm' : m + 'm';
        }, 30000);
    }

    // camera broadcast (owner)
    var video = document.getElementById('srVideo');
    if (video && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(function (ms) {
            video.srcObject = ms;
            window.__srMedia = ms;
            var msg = document.getElementById('srCamMsg'); if (msg) msg.textContent = 'You are live · camera on';
        }).catch(function () {
            var msg = document.getElementById('srCamMsg');
            if (msg) msg.textContent = 'Camera access blocked — allow it to broadcast.';
        });
        var camBtn = document.getElementById('srCam');
        var micBtn = document.getElementById('srMic');
        if (camBtn) camBtn.addEventListener('click', function () {
            var ms = window.__srMedia; if (!ms) return;
            var on = !ms.getVideoTracks()[0].enabled;
            ms.getVideoTracks().forEach(function (t) { t.enabled = on; });
            camBtn.classList.toggle('off', !on);
        });
        if (micBtn) micBtn.addEventListener('click', function () {
            var ms = window.__srMedia; if (!ms) return;
            var on = !ms.getAudioTracks()[0].enabled;
            ms.getAudioTracks().forEach(function (t) { t.enabled = on; });
            micBtn.classList.toggle('off', !on);
        });
    }

    // chat (cross-tab via localStorage)
    var key = 'axiom-stream-chat-' + id;
    var form = document.getElementById('srChatForm');
    var input = document.getElementById('srChatInput');
    var log = document.getElementById('srLog');
    var me = @json(optional(request()->user())->name ?? 'You');

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function load() { try { return JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) { return []; } }
    function render() {
        var msgs = JSON.parse(localStorage.getItem(key) || '[]');
        var log = document.getElementById('srLog');
        if (!log) return;
        if (!msgs.length) { log.innerHTML = '<div class="fx-token-tick">No messages yet — say hi.</div>'; return; }
        log.innerHTML = msgs.map(function (m) {
            return '<div class="sr-chat-msg"><b>' + esc(m.n) + '</b> <span>' + esc(m.t) + '</span></div>';
        }).join('');
        log.scrollTop = log.scrollHeight;
    }
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var text = document.getElementById('srChatInput').value.trim();
            if (!text) return;
            var msgs;
            try { msgs = JSON.parse(localStorage.getItem(key) || '[]'); } catch (err) { msgs = []; }
            msgs.push({ n: me, t: text });
            msgs = msgs.slice(-100);
            localStorage.setItem(key, JSON.stringify(msgs));
            document.getElementById('srChatInput').value = '';
            render();
        });
        window.addEventListener('storage', function (e) { if (e.key === key) render(); });
    }
    render();
})();
</script>
@endpush
