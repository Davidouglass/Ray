@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-vidicon-line"></i></div>
        <div>
            <h1>Go live</h1>
            <div class="fx-sub">Set up your stream. You will be broadcasting in a moment.</div>
        </div>
        <div class="fx-spacer"></div>
        <a class="fx-btn ghost" href="/streams"><i class="ri-arrow-left-line"></i>Back</a>
    </div>

    @if ($errors->any())<div class="fx-alert err">{{ $errors->first() }}</div>@endif

    <form method="POST" action="/streams" class="fx-two">
        @csrf
        <div class="fx-panel">
            <h2><i class="ri-edit-line"></i> Stream details</h2>

            <div class="fx-sub" style="margin-bottom:6px">Title</div>
            <input class="fx-input" style="width:100%;margin-bottom:14px" name="title" maxlength="140" placeholder="e.g. Scanning Solana runners live" value="{{ old('title') }}" required>

            <div class="fx-sub" style="margin-bottom:6px">Description</div>
            <textarea class="fx-input" style="width:100%;height:90px;padding:12px;margin-bottom:14px" name="description" maxlength="500" placeholder="What are you covering?">{{ old('description') }}</textarea>

            <div class="fx-sub" style="margin-bottom:6px">Token (optional)</div>
            <div class="fx-row">
                <input class="fx-input" style="flex:1 1 260px" name="token_address" placeholder="Solana token mint" value="{{ old('token_address') }}">
                <input class="fx-input" style="flex:0 0 130px" name="token_symbol" placeholder="Ticker" maxlength="40" value="{{ old('token_symbol') }}">
            </div>
        </div>

        <div class="fx-panel">
            <h2><i class="ri-broadcast-line"></i> Source</h2>

            <label class="fx-card" style="cursor:pointer;margin-bottom:10px">
                <div class="fx-card-top">
                    <input type="radio" name="source" value="camera" {{ old('source', 'camera') === 'camera' ? 'checked' : '' }}>
                    <div>
                        <div class="fx-token-name">Use my camera</div>
                        <div class="fx-token-tick">Broadcast with your webcam &amp; mic.</div>
                    </div>
                </div>
            </label>

            <label class="fx-card" style="cursor:pointer;margin-bottom:12px">
                <div class="fx-card-top">
                    <input type="radio" name="source" value="embed" {{ old('source') === 'embed' ? 'checked' : '' }}>
                    <div>
                        <div class="fx-token-name">Embed a player</div>
                        <div class="fx-token-tick">Paste a YouTube / Twitch / HLS embed URL.</div>
                    </div>
                </div>
            </label>

            <div id="embedField" style="display:{{ old('source') === 'embed' ? '' : 'none' }}">
                <div class="fx-sub" style="margin-bottom:6px">Embed URL</div>
                <input class="fx-input" style="width:100%" name="embed_url" placeholder="https://…" value="{{ old('embed_url') }}">
            </div>

            <button class="fx-btn" type="submit" style="width:100%;justify-content:center;height:46px;margin-top:16px"><i class="ri-live-line"></i>Start streaming</button>
            <div class="at-note"><i class="ri-lock-line"></i> Your camera never leaves your device unless you embed an external player.</div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="source"]').forEach(function (r) {
    r.addEventListener('change', function () {
        var f = document.getElementById('embedField');
        if (f) f.style.display = this.value === 'embed' ? '' : 'none';
    });
});
</script>
@endpush
