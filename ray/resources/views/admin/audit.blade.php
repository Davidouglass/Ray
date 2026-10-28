@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Audit log</div>
            <div class="adm-subtitle">{{ number_format($logs->total()) }} recorded action{{ $logs->total() === 1 ? '' : 's' }}.</div>
        <span class="adm-header-spacer"></span>
        <a href="/admin/audit/export" class="adm-btn"><i class="ri-download-2-line"></i> Export CSV</a>
    </div>

    <form method="GET" action="/admin/audit" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search action or admin">
        </label>
        <button type="submit" class="adm-btn">Search</button>
        @if ($q)
            <a href="/admin/audit" class="adm-btn sm">Reset</a>
        @endif
    </form>

    <div class="adm-card">
        @if ($logs->isEmpty())
            <div class="adm-empty">No audit entries.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Admin</th>
                            <th>Target</th>
                            <th>Details</th>
                            <th>IP</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td><span class="adm-badge neutral">{{ $log->action }}</span></td>
                                <td>{{ $log->admin->name ?? 'system' }}</td>
                                <td class="adm-mono">{{ $log->target_type ? class_basename($log->target_type) . ' #' . $log->target_id : '—' }}</td>
                                <td>
                                    @if ($log->meta)
                                        <details class="adm-details">
                                            <summary>{{ \Illuminate\Support\Str::limit(json_encode($log->meta), 30) }}</summary>
                                            <pre class="adm-json">{{ json_encode($log->meta, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @else
                                        <span class="adm-trader-meta">—</span>
                                    @endif
                                </td>
                                <td class="adm-mono">{{ $log->ip ?: '—' }}</td>
                                <td>{{ optional($log->created_at)->format('M j, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $logs->links('admin.pagination') }}
        @endif
    </div>
@endsection
