@php
    $tokens = $tokens ?? [];
    if (empty($tokens)) {
        $tokens = [
            ['sym' => 'SOL',  'price' => '$109.72', 'pct' => '+1.24%', 'up' => true],
            ['sym' => 'BNB',  'price' => '$745.10', 'pct' => '+0.42%', 'up' => true],
            ['sym' => 'HYPE', 'price' => '$84.45',  'pct' => '-0.88%', 'up' => false],
            ['sym' => 'ETH',  'price' => '$3,420',  'pct' => '+2.10%', 'up' => true],
        ];
    }
@endphp
{{-- ================= TICKER ================= --}}
<div class="platform-ticker desktop-only">
    <a class="tk-btn" href="/vision" aria-label="Settings"><i class="ri-settings-3-line"></i></a>
    <span class="tk-sep"></span>
    <a class="tk-btn" href="/portfolio" aria-label="Chart"><i class="ri-line-chart-line"></i></a>
    <a class="tk-btn" href="/portfolio" aria-label="History"><i class="ri-history-line"></i></a>
    <span class="tk-sep"></span>
    <div class="tk-strip" data-ticker-strip="true">
        <div class="tk-track">
            @foreach ($tokens as $t)
                <span class="tk-item"><span class="tk-sym">{{ $t['sym'] }}</span><span class="tk-px">{{ $t['price'] ?? '' }}</span><span class="tk-chg {{ !empty($t['up']) ? 'up' : 'down' }}">{{ $t['pct'] ?? '' }}</span></span>
            @endforeach
            @foreach ($tokens as $t)
                <span class="tk-item"><span class="tk-sym">{{ $t['sym'] }}</span><span class="tk-px">{{ $t['price'] ?? '' }}</span><span class="tk-chg {{ !empty($t['up']) ? 'up' : 'down' }}">{{ $t['pct'] ?? '' }}</span></span>
            @endforeach
        </div>
    </div>
</div>
