@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Analytics</div>
            <div class="adm-subtitle">Growth, engagement and revenue.</div>
        </div>
    </div>

    <div class="adm-stats">
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-user-add-line"></i> Signups · 30d</div>
            <div class="adm-stat-value">{{ number_format($stats['signups30']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-flashlight-line"></i> Active today</div>
            <div class="adm-stat-value">{{ number_format($stats['activeToday']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-wallet-3-line"></i> Connected wallets</div>
            <div class="adm-stat-value">{{ number_format($stats['wallets']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-anchor-line"></i> Deposit addresses</div>
            <div class="adm-stat-value">{{ number_format($stats['deposits']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-radar-line"></i> Tracked wallets</div>
            <div class="adm-stat-value">{{ number_format($stats['tracked']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-refund-2-line"></i> Active subscriptions</div>
            <div class="adm-stat-value">{{ number_format($stats['subsActive']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-money-dollar-circle-line"></i> Subscription revenue</div>
            <div class="adm-stat-value">${{ number_format($stats['revenue'], 0) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-capsule-line"></i> Points issued</div>
            <div class="adm-stat-value">{{ number_format($stats['points']) }}</div>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><i class="ri-line-chart-line"></i> New signups · last 30 days</div>
        <div class="adm-card-body">
            @php $max = max(1, collect($chart)->max('count')); @endphp
            <div class="adm-chart">
                @foreach ($chart as $day)
                    <div class="adm-chart-col" title="{{ $day['label'] }} · {{ $day['count'] }}">
                        <div class="adm-chart-bar" style="height: {{ max(2, round($day['count'] / $max * 100)) }}%"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="adm-two">
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-user-star-line"></i> Top traders by followers</div>
            @if ($topTraders->isEmpty())
                <div class="adm-empty">No traders yet.</div>
            @else
                <div class="adm-list">
                    @foreach ($topTraders as $t)
                        <div class="adm-list-item">
                            <div class="adm-trader-cell">
                                <img class="adm-avatar" src="{{ $t->avatar_url ?: asset('images/veridian-mark.png') }}" alt="" onerror="this.src='{{ asset('images/veridian-mark.png') }}'">
                                <div>
                                    <div class="adm-trader-name">{{ $t->display_name }}</div>
                                    <div class="adm-trader-meta">{{ number_format($t->followers_count) }} followers</div>
                                </div>
                            </div>
                            <a href="/admin/traders/{{ $t->slug }}/edit" class="adm-btn sm icon"><i class="ri-pencil-line"></i></a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-link"></i> Connected wallets by chain</div>
            @if ($chains->isEmpty())
                <div class="adm-empty">No wallets connected yet.</div>
            @else
                <div class="adm-list">
                    @foreach ($chains as $c)
                        <div class="adm-list-item">
                            <div class="adm-trader-name">{{ $c->chain ?: 'unknown' }}</div>
                            <span class="adm-badge neutral">{{ number_format($c->c) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
