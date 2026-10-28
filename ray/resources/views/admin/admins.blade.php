@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Administrators</div>
            <div class="adm-subtitle">{{ number_format($admins->count()) }} administrator{{ $admins->count() === 1 ? '' : 's' }}.</div>
        </div>
    </div>

    @if (auth('admin')->user()?->canManageAdmins())
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-user-add-line"></i> Add administrator</div>
            <form method="POST" action="/admin/admins" class="adm-card-body">
                @csrf
                <div class="adm-form">
                    <div class="adm-field">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required maxlength="120">
                    </div>
                    <div class="adm-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required maxlength="255">
                    </div>
                    <div class="adm-field">
                        <label>Temporary password</label>
                        <input type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="adm-field">
                        <label>Role</label>
                        <select name="admin_role">
                            @foreach (['support', 'admin', 'super'] as $role)
                                <option value="{{ $role }}" {{ old('admin_role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="adm-form-actions">
                        <button type="submit" class="adm-btn primary"><i class="ri-user-add-line"></i> Create admin</button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <div class="adm-card">
        @if ($admins->isEmpty())
            <div class="adm-empty">No administrators.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last login</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($admins as $a)
                            <tr>
                                <td>
                                    <div class="adm-trader-name">{{ $a->name }}</div>
                                    <div class="adm-trader-meta">{{ $a->email }}</div>
                                </td>
                                <td>
                                    @if ($a->admin_role === 'super')
                                        <span class="adm-badge blue"><i class="ri-vip-crown-line"></i> Super</span>
                                    @elseif ($a->admin_role === 'admin')
                                        <span class="adm-badge neutral">Admin</span>
                                    @else
                                        <span class="adm-badge neutral">Support</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="adm-badge {{ $a->isActive() ? 'ok' : 'danger' }}">{{ ucfirst($a->status ?? 'active') }}</span>
                                </td>
                                <td>{{ optional($a->last_login_at)->diffForHumans() ?? '—' }}</td>
                                <td>
                                    @if (auth('admin')->user()?->canManageAdmins())
                                        <form method="POST" action="/admin/admins/{{ $a->id }}/role" class="adm-inline">
                                            @csrf
                                            <select name="admin_role">
                                                @foreach (['super', 'admin', 'support'] as $role)
                                                    <option value="{{ $role }}" {{ $a->admin_role === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="adm-btn sm" {{ $a->id === auth('admin')->id() ? 'disabled' : '' }}>Save</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
