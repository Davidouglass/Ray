@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Subscriptions</div>
            <div class="adm-subtitle">{{ number_format($subs->total()) }} subscription{{ $subs->total() === 1 ? '' : 's' }}.</div>
        </div>
    </div>

    <form method="GET" action="/admin/subscriptions" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search user or trader">
        </label>
        <div class="adm-chips">
            @foreach (['all', 'active', 'cancelled', 'expired', 'refunded'] as $s)
                <a href="/admin/subscriptions?status={{ $s }}{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === $s ? 'is-active' : '' }}">{{ ucfirst($s) }}</a>
            @endforeach
        </div>
        <button type="submit" class="adm-btn">Search</button>
    </form>

    <div class="adm-card">
        @if ($subs->isEmpty())
            <div class="adm-empty">No subscriptions match your filters.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Subscriber</th>
                            <th>Trader</th>
                            <th class="adm-num">Paid</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Expires</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subs as $s)
                            <tr>
                                <td>
                                    <div class="adm-trader-name">{{ $s->follower->name ?? '—' }}</div>
                                    <div class="adm-trader-meta">{{ $s->follower->email ?? '' }}</div>
                                </td>
                                <td>{{ $s->copyTrader->display_name ?? '—' }}</td>
                                <td class="adm-num">${{ number_format((float) $s->paid_amount, 2) }}</td>
                                <td>
                                    <span class="adm-badge {{ $s->status === 'active' ? 'ok' : ($s->status === 'refunded' ? 'danger' : 'neutral') }}">{{ ucfirst($s->status) }}</span>
                                </td>
                                <td>{{ optional($s->created_at)->format('M j, Y') }}</td>
                                <td>{{ optional($s->expires_at)->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    <form method="POST" action="/admin/subscriptions/{{ $s->id }}/status" class="adm-inline">
                                        @csrf
                                        <select name="status">
                                            @foreach (['active', 'cancelled', 'expired', 'refunded'] as $st)
                                                <option value="{{ $st }}" {{ $s->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="adm-btn sm">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $subs->links('admin.pagination') }}
        @endif
    </div>
@endsection
