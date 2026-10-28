@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Wallets</div>
            <div class="adm-subtitle">{{ number_format($rows->total()) }} {{ $kind === 'deposits' ? 'deposit address' : 'connected wallet' }}{{ $rows->total() === 1 ? '' : 's' }}.</div>
        </div>
    </div>

    <form method="GET" action="/admin/wallets" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search address, name or email">
        </label>
        <div class="adm-chips">
            <a href="/admin/wallets?kind=connected{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $kind === 'connected' ? 'is-active' : '' }}">Connected</a>
            <a href="/admin/wallets?kind=deposits{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $kind === 'deposits' ? 'is-active' : '' }}">Deposit addresses</a>
        </div>
        <button type="submit" class="adm-btn">Search</button>
    </form>

    <div class="adm-card">
        @if ($rows->isEmpty())
            <div class="adm-empty">No wallets found.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Address</th>
                            <th>Chain</th>
                            <th>Owner</th>
                            <th>Added</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $w)
                            <tr>
                                <td><span class="adm-mono">{{ $w->address }}</span> @if($w->is_primary ?? false)<span class="adm-badge ok">Primary</span>@endif</td>
                                <td><span class="adm-badge neutral">{{ $w->chain ?: '—' }}</span></td>
                                <td>
                                    <div class="adm-trader-name">{{ $w->user->name ?? '—' }}</div>
                                    <div class="adm-trader-meta">{{ $w->user->email ?? '' }}</div>
                                </td>
                                <td>{{ optional($w->created_at)->format('M j, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ $kind === 'deposits' ? '/admin/wallets/deposit/' . $w->id : '/admin/wallets/connected/' . $w->id }}" onsubmit="return confirm('Remove this wallet?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="adm-btn sm icon danger" title="Remove"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $rows->links('admin.pagination') }}
        @endif
    </div>
@endsection
