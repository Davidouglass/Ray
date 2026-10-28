@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Rewards & Points</div>
            <div class="adm-subtitle">Issued {{ number_format($total) }} points total · {{ number_format($issued30) }} in the last 30 days · {{ number_format($referrals) }} referral events.</div>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><i class="ri-capsule-line"></i> Manual adjustment</div>
        <form method="POST" action="/admin/rewards/adjust" class="adm-card-body">
            @csrf
            <div class="adm-form">
                <div class="adm-field">
                    <label>User ID</label>
                    <input type="number" name="user_id" value="{{ old('user_id') }}" required>
                </div>
                <div class="adm-field">
                    <label>Points (± )</label>
                    <input type="number" name="points" value="{{ old('points') }}" required>
                </div>
                <div class="adm-field full">
                    <label>Reason</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" maxlength="160">
                </div>
                <div class="adm-form-actions">
                    <button type="submit" class="adm-btn primary"><i class="ri-add-line"></i> Record adjustment</button>
                </div>
            </div>
        </form>
    </div>

    <form method="GET" action="/admin/rewards" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search kind, name or email">
        </label>
        <button type="submit" class="adm-btn">Search</button>
        @if ($q)
            <a href="/admin/rewards" class="adm-btn sm">Reset</a>
        @endif
    </form>

    <div class="adm-card">
        @if ($events->isEmpty())
            <div class="adm-empty">No point events.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Kind</th>
                            <th class="adm-num">Points</th>
                            <th>Reason</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $e)
                            <tr>
                                <td>
                                    <div class="adm-trader-name">{{ $e->user->name ?? '—' }}</div>
                                    <div class="adm-trader-meta">{{ $e->user->email ?? '' }}</div>
                                </td>
                                <td><span class="adm-badge neutral">{{ $e->kind }}</span></td>
                                <td class="adm-num {{ $e->points >= 0 ? '' : '' }}">{{ $e->points >= 0 ? '+' : '' }}{{ number_format($e->points) }}</td>
                                <td>{{ $e->meta['reason'] ?? '—' }}</td>
                                <td>{{ optional($e->created_at)->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $events->links('admin.pagination') }}
        @endif
    </div>
@endsection
