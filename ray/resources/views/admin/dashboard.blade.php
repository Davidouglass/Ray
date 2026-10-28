@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Dashboard</div>
            <div class="adm-subtitle">Overview of the Veridian platform.</div>
        </div>
        <span class="adm-header-spacer"></span>
        <a href="/admin/analytics" class="adm-btn"><i class="ri-line-chart-line"></i> Analytics</a>
    </div>

    <div class="adm-quick">
        <a class="adm-quick-item" href="/admin/traders/create"><i class="ri-user-add-line"></i><span>New trader</span></a>
        <a class="adm-quick-item" href="/admin/announcements"><i class="ri-megaphone-line"></i><span>Announcement</span></a>
        <a class="adm-quick-item" href="/admin/users"><i class="ri-group-line"></i><span>Users</span></a>
        <a class="adm-quick-item" href="/admin/rewards"><i class="ri-capsule-line"></i><span>Points</span></a>
        <a class="adm-quick-item" href="/admin/wallets"><i class="ri-wallet-3-line"></i><span>Wallets</span></a>
        <a class="adm-quick-item" href="/admin/settings"><i class="ri-settings-3-line"></i><span>Settings</span></a>
    </div>

    <div class="adm-stats">
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-group-line"></i> Users</div>
            <div class="adm-stat-value">{{ number_format($stats['users']) }} <small>· {{ $stats['admins'] }} admin{{ $stats['admins'] === 1 ? '' : 's' }}</small></div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-user-forbid-line"></i> Restricted</div>
            <div class="adm-stat-value">{{ number_format($stats['banned']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-user-star-line"></i> Copy Traders</div>
            <div class="adm-stat-value">{{ number_format($stats['traders']) }} <small>· {{ $stats['activeTraders'] }} active</small></div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-shield-check-line"></i> Verified</div>
            <div class="adm-stat-value">{{ number_format($stats['verifiedTraders']) }} <small>· {{ $stats['featuredTraders'] }} featured</small></div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-refund-2-line"></i> Active Subscriptions</div>
            <div class="adm-stat-value">{{ number_format($stats['subscriptions']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-exchange-funds-line"></i> Copy Trades</div>
            <div class="adm-stat-value">{{ number_format($stats['trades']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-live-line"></i> Live Streams</div>
            <div class="adm-stat-value">{{ number_format($stats['liveStreams']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-wallet-3-line"></i> Connected Wallets</div>
            <div class="adm-stat-value">{{ number_format($stats['wallets']) }}</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-label"><i class="ri-capsule-line"></i> Points issued</div>
            <div class="adm-stat-value">{{ number_format($stats['points']) }}</div>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><i class="ri-line-chart-line"></i> New signups · last 14 days</div>
        <div class="adm-card-body">
            @php $max = max(1, collect($chart)->max('count')); @endphp
            <div class="adm-chart">
                @foreach ($chart as $day)
                    <div class="adm-chart-col" title="{{ $day['label'] }} · {{ $day['count'] }}">
                        <div class="adm-chart-bar" style="height: {{ max(2, round($day['count'] / $max * 100)) }}%"></div>
                        <span class="adm-chart-x">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="adm-two">
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-user-add-line"></i> Newest users <span class="adm-header-spacer"></span><a href="/admin/users" class="adm-btn sm">View all</a></div>
            @if ($recentUsers->isEmpty())
                <div class="adm-empty">No users yet.</div>
            @else
                <div class="adm-list">
                    @foreach ($recentUsers as $u)
                        <div class="adm-list-item">
                            <div class="adm-trader-cell">
                                <div>
                                    <div class="adm-trader-name">{{ $u->name }}</div>
                                    <div class="adm-trader-meta">{{ $u->email }}</div>
                                </div>
                            </div>
                            @if ($u->is_admin)
                                <span class="adm-badge blue"><i class="ri-shield-user-line"></i> Admin</span>
                            @elseif (! $u->isActive())
                                <span class="adm-badge danger">{{ ucfirst($u->status) }}</span>
                            @endif
                            <span class="adm-list-time">{{ optional($u->created_at)->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-user-star-line"></i> Newest copy traders <span class="adm-header-spacer"></span><a href="/admin/traders" class="adm-btn sm">View all</a></div>
            @if ($recentTraders->isEmpty())
                <div class="adm-empty">No copy traders yet.</div>
            @else
                <div class="adm-list">
                    @foreach ($recentTraders as $t)
                        <div class="adm-list-item">
                            <div class="adm-trader-cell">
                                <img class="adm-avatar" src="{{ $t->avatar_url ?: asset('images/veridian-mark.png') }}" alt="" onerror="this.src='{{ asset('images/veridian-mark.png') }}'">
                                <div>
                                    <div class="adm-trader-name">{{ $t->display_name }}</div>
                                    <div class="adm-trader-meta">{{ number_format($t->followers_count) }} followers · {{ strtoupper($t->risk_level) }} risk</div>
                                </div>
                            </div>
                            <a href="/admin/traders/{{ $t->slug }}/edit" class="adm-btn sm icon"><i class="ri-pencil-line"></i></a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><i class="ri-history-line"></i> Recent admin activity <span class="adm-header-spacer"></span><a href="/admin/audit" class="adm-btn sm">Full log</a></div>
        @if ($recentAudit->isEmpty())
            <div class="adm-empty">No activity recorded yet.</div>
        @else
            <div class="adm-list">
                @foreach ($recentAudit as $log)
                    <div class="adm-list-item">
                        <div>
                            <div class="adm-trader-name">{{ $log->action }}</div>
                            <div class="adm-trader-meta">{{ $log->admin->name ?? 'system' }} · {{ $log->target_type ? class_basename($log->target_type) . ' #' . $log->target_id : '—' }}</div>
                        </div>
                        <span class="adm-list-time">{{ optional($log->created_at)->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
