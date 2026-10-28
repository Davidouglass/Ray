@php
    $prog = (int) ($t['progress'] ?? 0);
    $img = $t['img'] ?? ($chainArt['sol'] ?? '');
    $handle = null;
    if (!empty($t['twitter'])) {
        $handle = '@' . preg_replace('~^.*/~', '', rtrim($t['twitter'], '/'));
    }
@endphp
@php $tokenUrl = !empty($t['address']) ? '/token/' . $t['address'] : null; @endphp
<article class="pm-card" data-pulse-chain="{{ $t['chain'] }}" data-pulse-token-address="{{ $t['address'] ?? '' }}"@if ($tokenUrl) data-href="{{ $tokenUrl }}"@endif>
    <div class="pm-card-top">
        <div class="pm-logo">
            <img src="{{ $img }}" alt="{{ $t['name'] }}" loading="lazy">
            <span class="pm-logo-badge"><img src="{{ $chainArt[$t['chain']] ?? $chainArt['sol'] }}" alt="" width="10" height="10"></span>
        </div>
        <div class="pm-info">
            <div class="pm-name-line">
                <b class="pm-name">{{ $t['sym'] }}</b>
                <span class="pm-sym">{{ $t['name'] }}</span>
                <i class="ri-file-copy-line pm-copy" data-copy="{{ $t['address'] ?? '' }}" title="Copy contract"></i>
                <div class="pm-prices"><small>V</small> {{ $t['vol'] }} <small>MC</small> <span class="pm-mc">{{ $t['mc'] }}</span></div>
            </div>
            <div class="pm-meta">
                <span class="pm-age">{{ $t['age'] }}</span>
                @if (!empty($t['twitter']))<a href="{{ $t['twitter'] }}" target="_blank" rel="noopener" class="pm-soc" title="X"><i class="ri-twitter-x-line"></i></a>@endif
                @if (!empty($t['telegram']))<a href="{{ $t['telegram'] }}" target="_blank" rel="noopener" class="pm-soc" title="Telegram"><i class="ri-telegram-line"></i></a>@endif
                @if (!empty($t['web']))<a href="{{ $t['web'] }}" target="_blank" rel="noopener" class="pm-soc" title="Website"><i class="ri-global-line"></i></a>@endif
                <span class="pm-h"><i class="ri-group-line"></i><span class="pm-holders-val">–</span></span>
                <span class="pm-tx">TX {{ $t['txns'] }}</span>
            </div>
            <div class="pm-addr">
                @if ($tokenUrl)
                    <a class="pm-addr-link" href="{{ $tokenUrl }}">{{ $t['addressShort'] ?? ($t['address'] ?? '') }}</a>
                @else
                    <span>{{ $t['addressShort'] ?? '' }}</span>
                @endif
                @if ($handle)<span class="pm-handle">{{ $handle }}</span>@endif
            </div>
        </div>
    </div>
    <div class="pm-scores" data-mint="{{ $t['address'] ?? '' }}">
        <span class="pm-score pm-holders"><i class="ri-group-line"></i> <span class="val">–</span></span>
        <span class="pm-score pm-score--prog"><i class="ri-line-chart-line"></i> {{ $prog }}%</span>
        <span class="pm-score pm-score--top"><i class="ri-pie-chart-line"></i> <span class="val">–</span></span>
        <span class="pm-score pm-score--safe"><i class="ri-shield-check-line"></i> <span class="val">–</span></span>
    </div>
</article>
