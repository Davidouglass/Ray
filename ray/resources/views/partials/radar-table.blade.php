@php
    $radarArtClass = function ($sym) {
        $map = [
            'SUSD' => 'art-letter', 'PONS' => 'art-p', 'GEM' => 'art-gem',
            'HAPPY' => 'art-happy', 'KS' => 'art-ks', 'VORTA' => 'art-vorta',
            'FUNDED' => 'art-funded',
        ];
        $k = strtoupper(trim((string) $sym));
        return $map[$k] ?? 'art-letter';
    };
    $radarArtText = function ($sym) {
        $k = strtoupper(trim((string) $sym));
        if ($k === 'KS') return 'KILL<br>SWITCH';
        if ($k === 'FUNDED') return 'FUNDED';
        return htmlspecialchars(mb_substr((string) $sym, 0, 1));
    };
    $radarNum = function ($v) {
        $v = str_replace([',', '$', ' '], '', (string) $v);
        $last = strtoupper(substr($v, -1));
        $mult = 1;
        if ($last === 'K') { $mult = 1000; $v = substr($v, 0, -1); }
        elseif ($last === 'M') { $mult = 1000000; $v = substr($v, 0, -1); }
        elseif ($last === 'B') { $mult = 1000000000; $v = substr($v, 0, -1); }
        return (float) $v * $mult;
    };
@endphp
<div class="radar-frame">
    <div class="radar-table-scroll">
        <table class="venue-table" aria-label="Venue token activity">
            <colgroup>
                <col class="venue"><col class="status"><col class="first-seen">
                <col class="tokens"><col class="traders"><col class="volume"><col class="samples">
            </colgroup>
            <thead>
                <tr>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>First seen</th>
                    <th>Tokens 24h</th>
                    <th>Traders 24h</th>
                    <th class="sortable sorted" data-sort="volume">Volume 24h<span class="sort-arrow">&darr;</span></th>
                    <th>Sample tokens</th>
                </tr>
            </thead>
            <tbody class="radar-rows">
                @foreach ($venues as $v)
                @php $samples = $v['samples']; $total = count($samples); @endphp
                <tr data-volume="{{ $radarNum($v['vol']) }}" data-traders="{{ $radarNum($v['traders']) }}" data-tokens="{{ $radarNum($v['tokens']) }}">
                    <td>
                        <div class="venue-cell">
                            <div class="venue-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18a9 9 0 1 1 15.2-6.5M12 12l4-5M12 12l-1 5"/><circle cx="12" cy="12" r="1.4"/><path d="M6 20h12"/></svg>
                            </div>
                            <div class="venue-info">
                                <div class="address-line">
                                    <span class="address">{{ $v['name'] ?? $v['addr'] }}</span>
                                    <span class="venue-type">{{ $v['tag'] }}</span>
                                </div>
                                <div class="address-meta">
                                    <span>{{ $v['addr'] }}</span>
                                    <button type="button" class="copy-address radar-copy" data-copy="{{ $v['hash'] ?? $v['addr'] }}" title="Copy address">&#9634;</button>
                                    @if (!empty($v['website']))<a class="radar-ext" href="{{ $v['website'] }}" target="_blank" rel="noopener" title="Website"><i class="ri-global-line"></i></a>@endif
                                    @if (!empty($v['twitter']))<a class="radar-ext" href="{{ $v['twitter'] }}" target="_blank" rel="noopener" title="X"><i class="ri-twitter-x-line"></i></a>@endif
                                    @if (!empty($v['hash']))<a class="radar-ext" href="https://robin.etherscan.io/address/{{ $v['hash'] }}" target="_blank" rel="noopener" title="Etherscan"><i class="ri-external-link-line"></i></a>@endif
                                    <span class="chain-mark">&#9678;</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><span class="status-pill">{{ $v['status'] }}</span></td>
                    <td><span class="age">{{ $v['seen'] }}</span></td>
                    <td><span class="number">{{ $v['tokens'] }}</span></td>
                    <td><span class="number">{{ $v['traders'] }}</span></td>
                    <td><span class="number volume-value"><span class="solana-mark">&#9830;</span>{{ $v['vol'] }}</span></td>
                    <td class="samples-cell">
                        <div class="samples">
                            @foreach ($samples as $i => $s)
                            @php
                                $isDash = ($s['chg'] === '—' || $s['chg'] === '-' || $s['chg'] === '');
                                $chgClass = $isDash ? 'neutral' : ($s['dir'] === 'up' ? 'positive' : '');
                                $chgText = $isDash ? '-' : $s['chg'];
                            @endphp
                            @if ($i === 2)
                            <button class="more-button" type="button" aria-expanded="false">+{{ $total - 2 }}</button>
                            <div class="extra-samples">
                            @endif
                                <div class="sample-card">
                                    <div class="token-art {{ $radarArtClass($s['sym']) }}">{!! $radarArtText($s['sym']) !!}</div>
                                    <div class="token-data">
                                        <span class="token-name">{{ $s['sym'] }}</span>
                                        <span class="token-age">{{ $s['age'] }}</span>
                                        <span class="token-change {{ $chgClass }}">{{ $chgText }}</span>
                                        <span class="token-cap">{{ $s['vol'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                            @if ($total > 2)
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="radar-refresh" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 3v6h-6"/></svg>
        <span class="radar-refresh-num">5</span>s
    </div>
</div>
