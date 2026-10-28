@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Users</div>
            <div class="adm-subtitle">{{ number_format($users->total()) }} user{{ $users->total() === 1 ? '' : 's' }} matching.</div>
        </div>
    </div>

    <form method="GET" action="/admin/users" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search name or email">
        </label>
        <div class="adm-chips">
            <a href="/admin/users?status=all{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'all' ? 'is-active' : '' }}">All</a>
            <a href="/admin/users?status=active{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'active' ? 'is-active' : '' }}">Active</a>
            <a href="/admin/users?status=admins{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'admins' ? 'is-active' : '' }}">Admins</a>
            <a href="/admin/users?status=banned{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'banned' ? 'is-active' : '' }}">Restricted</a>
        </div>
        <button type="submit" class="adm-btn">Search</button>
        @if ($q || $status !== 'all')
            <a href="/admin/users" class="adm-btn sm">Reset</a>
        @endif
    </form>

    <div class="adm-card">
        @if ($users->isEmpty())
            <div class="adm-empty">No users match your filters.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="adm-num">Wallets</th>
                            <th class="adm-num">Deposits</th>
                            <th>Last login</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr>
                                <td>
                                    <div class="adm-trader-cell">
                                        <div>
                                            <div class="adm-trader-name">{{ $u->name }}</div>
                                            <div class="adm-trader-meta">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($u->is_admin)
                                        <span class="adm-badge blue"><i class="ri-shield-user-line"></i> {{ ucfirst($u->admin_role ?? 'admin') }}</span>
                                    @else
                                        <span class="adm-badge neutral">User</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($u->isActive())
                                        <span class="adm-badge ok">Active</span>
                                    @else
                                        <span class="adm-badge danger">{{ ucfirst($u->status) }}</span>
                                    @endif
                                </td>
                                <td class="adm-num">{{ number_format($u->connected_wallets_count) }}</td>
                                <td class="adm-num">{{ number_format($u->deposit_addresses_count) }}</td>
                                <td>{{ optional($u->last_login_at)->diffForHumans() ?? '—' }}</td>
                                <td>
                                    <div class="adm-row-actions">
                                        <a href="/admin/users/{{ $u->id }}" class="adm-btn sm"><i class="ri-eye-line"></i> View</a>
                                        <form method="POST" action="/admin/users/{{ $u->id }}/impersonate">
                                            @csrf
                                            <button type="submit" class="adm-btn sm icon" title="Impersonate" {{ $u->id === auth('admin')->id() ? 'disabled' : '' }}><i class="ri-login-circle-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $users->links('admin.pagination') }}
        @endif
    </div>
@endsection
