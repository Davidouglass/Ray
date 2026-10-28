@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Copy Trades</div>
            <div class="adm-subtitle">{{ number_format($trades->total()) }} executed trade{{ $trades->total() === 1 ? '' : 's' }}.</div>
        </div>
    </div>

    <form method="GET" action="/admin/copy-trades" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search symbol, mint or signature">
        </label>
        <button type="submit" class="adm-btn">Search</button>
        @if ($q)
            <a href="/admin/copy-trades" class="adm-btn sm">Reset</a>
        @endif
    </form>

    <div class="adm-card">
        @if ($trades->isEmpty())
            <div class="adm-empty">No copy trades recorded.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Trade</th>
                            <th>Trader</th>
                            <th>Follower</th>
                            <th>Action</th>
                            <th class="adm-num">Amount</th>
                            <th class="adm-num">Total</th>
                            <th>Executed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trades as $t)
                            <tr>
                                <td>
                                    <div class="adm-trader-name">{{ $t->symbol ?: '—' }}</div>
                                    <div class="adm-trader-meta adm-mono">{{ \Illuminate\Support\Str::limit($t->mint, 14, '…') }}</div>
                                </td>
                                <td>{{ $t->copyTrader->display_name ?? '—' }}</td>
                                <td>{{ $t->subscription->follower->name ?? '—' }}</td>
                                <td>
                                    <span class="adm-badge {{ strtolower($t->action) === 'buy' ? 'ok' : 'danger' }}">{{ strtoupper($t->action ?: '—') }}</span>
                                </td>
                                <td class="adm-num">{{ rtrim(rtrim(number_format((float) $t->amount, 4), '0'), '.') }}</td>
                                <td class="adm-num">${{ number_format((float) $t->total_usd, 2) }}</td>
                                <td>{{ optional($t->executed_at)->format('M j, H:i') ?? '—' }}</td>
                                <td>
                                    @if ($t->tx_signature)
                                        <span class="adm-mono" title="{{ $t->tx_signature }}"><i class="ri-file-copy-line"></i> {{ \Illuminate\Support\Str::limit($t->tx_signature, 8, '…') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $trades->links('admin.pagination') }}
        @endif
    </div>
@endsection
