@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">{{ $user->name }}</div>
            <div class="adm-subtitle">
                {{ $user->email }}
                @if ($user->is_admin) · <span class="adm-badge blue">{{ ucfirst($user->admin_role ?? 'admin') }}</span>@endif
                @if (! $user->isActive()) · <span class="adm-badge danger">{{ ucfirst($user->status) }}</span>@endif
            </div>
        </div>
        <span class="adm-header-spacer"></span>
        <a href="/admin/users" class="adm-btn"><i class="ri-arrow-left-line"></i> Back</a>
    </div>

    <div class="adm-stats">
        <div class="adm-stat"><div class="adm-stat-label"><i class="ri-wallet-3-line"></i> Wallets</div><div class="adm-stat-value">{{ number_format($user->connected_wallets_count) }}</div></div>
        <div class="adm-stat"><div class="adm-stat-label"><i class="ri-anchor-line"></i> Deposits</div><div class="adm-stat-value">{{ number_format($user->deposit_addresses_count) }}</div></div>
        <div class="adm-stat"><div class="adm-stat-label"><i class="ri-radar-line"></i> Tracked</div><div class="adm-stat-value">{{ number_format($user->tracked_wallets_count) }}</div></div>
        <div class="adm-stat"><div class="adm-stat-label"><i class="ri-capsule-line"></i> Points</div><div class="adm-stat-value">{{ number_format($points_total) }}</div></div>
        <div class="adm-stat"><div class="adm-stat-label"><i class="ri-refund-2-line"></i> Subscriptions</div><div class="adm-stat-value">{{ number_format($user->copy_subscriptions_count) }}</div></div>
        <div class="adm-stat"><div class="adm-stat-label"><i class="ri-live-line"></i> Streams</div><div class="adm-stat-value">{{ number_format($user->streams_count) }}</div></div>
    </div>

    <div class="adm-two">
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-user-settings-line"></i> Account details</div>
            <form method="POST" action="/admin/users/{{ $user->id }}" class="adm-card-body">
                @csrf
                @method('PUT')
                <div class="adm-form">
                    <div class="adm-field full">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="adm-field full">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="adm-field">
                        <label>Status</label>
                        <select name="status">
                            @foreach (['active', 'suspended', 'banned'] as $s)
                                <option value="{{ $s }}" {{ old('status', $user->status ?? 'active') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="adm-field">
                        <label>New password (optional)</label>
                        <input type="text" name="password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="adm-field full">
                        <label>Suspension reason</label>
                        <input type="text" name="suspended_reason" value="{{ old('suspended_reason', $user->suspended_reason) }}" maxlength="255">
                    </div>
                    <div class="adm-form-actions">
                        <button type="submit" class="adm-btn primary"><i class="ri-save-line"></i> Save user</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-shield-user-line"></i> Moderation & access</div>
            <div class="adm-card-body adm-stack">
                <form method="POST" action="/admin/users/{{ $user->id }}/status" class="adm-inline">
                    @csrf
                    <select name="status">
                        @foreach (['active', 'suspended', 'banned'] as $s)
                            <option value="{{ $s }}" {{ ($user->status ?? 'active') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="reason" value="{{ $user->suspended_reason }}" placeholder="Reason (optional)" maxlength="255">
                    <button type="submit" class="adm-btn">Update status</button>
                </form>

                <form method="POST" action="/admin/users/{{ $user->id }}/impersonate">
                    @csrf
                    <button type="submit" class="adm-btn"><i class="ri-login-circle-line"></i> Impersonate user</button>
                </form>

                <form method="POST" action="/admin/users/{{ $user->id }}/toggle-admin">
                    @csrf
                    <button type="submit" class="adm-btn"><i class="ri-shield-{{ $user->is_admin ? 'cross' : 'check' }}-line"></i> {{ $user->is_admin ? 'Revoke admin' : 'Grant admin' }}</button>
                </form>

                <form method="POST" action="/admin/rewards/adjust" class="adm-inline">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="number" name="points" placeholder="± points" required>
                    <input type="text" name="reason" placeholder="Reason">
                    <button type="submit" class="adm-btn">Adjust points</button>
                </form>

                <form method="POST" action="/admin/users/{{ $user->id }}" onsubmit="return confirm('Delete {{ $user->email }} permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="adm-btn danger"><i class="ri-delete-bin-line"></i> Delete account</button>
                </form>
            </div>
        </div>
    </div>

    <div class="adm-two">
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-wallet-3-line"></i> Connected wallets</div>
            @if ($wallets->isEmpty())
                <div class="adm-empty">No wallets connected.</div>
            @else
                <div class="adm-list">
                    @foreach ($wallets as $w)
                        <div class="adm-list-item">
                            <div>
                                <div class="adm-mono">{{ $w->address }}</div>
                                <div class="adm-trader-meta">{{ $w->chain }} · {{ $w->label ?? 'wallet' }} @if($w->is_primary) · primary @endif</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-anchor-line"></i> Deposit addresses</div>
            @if ($deposits->isEmpty())
                <div class="adm-empty">No deposit addresses.</div>
            @else
                <div class="adm-list">
                    @foreach ($deposits as $d)
                        <div class="adm-list-item">
                            <div>
                                <div class="adm-mono">{{ $d->address }}</div>
                                <div class="adm-trader-meta">{{ $d->chain }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="adm-two">
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-refund-2-line"></i> Copy subscriptions</div>
            @if ($subscriptions->isEmpty())
                <div class="adm-empty">No subscriptions.</div>
            @else
                <div class="adm-list">
                    @foreach ($subscriptions as $s)
                        <div class="adm-list-item">
                            <div>
                                <div class="adm-trader-name">{{ $s->copyTrader->display_name ?? '—' }}</div>
                                <div class="adm-trader-meta">{{ optional($s->created_at)->format('M j, Y') }}</div>
                            </div>
                            <span class="adm-badge {{ $s->status === 'active' ? 'ok' : 'neutral' }}">{{ $s->status }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-capsule-line"></i> Recent point events</div>
            @if ($points->isEmpty())
                <div class="adm-empty">No point events.</div>
            @else
                <div class="adm-list">
                    @foreach ($points as $p)
                        <div class="adm-list-item">
                            <div>
                                <div class="adm-trader-name">{{ $p->kind }}</div>
                                <div class="adm-trader-meta">{{ optional($p->created_at)->format('M j, Y') }}</div>
                            </div>
                            <span class="adm-badge {{ $p->points >= 0 ? 'ok' : 'danger' }}">{{ $p->points >= 0 ? '+' : '' }}{{ number_format($p->points) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
