@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Streams</div>
            <div class="adm-subtitle">{{ number_format($streams->total()) }} stream{{ $streams->total() === 1 ? '' : 's' }}.</div>
        </div>
    </div>

    <form method="GET" action="/admin/streams" class="adm-toolbar">
        <div class="adm-chips">
            @foreach (['all', 'visible', 'hidden', 'removed'] as $m)
                <a href="/admin/streams?moderation={{ $m }}" class="{{ $moderation === $m ? 'is-active' : '' }}">{{ ucfirst($m) }}</a>
            @endforeach
        </div>
    </form>

    <div class="adm-card">
        @if ($streams->isEmpty())
            <div class="adm-empty">No streams found.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Stream</th>
                            <th>Host</th>
                            <th>Status</th>
                            <th>Moderation</th>
                            <th class="adm-num">Viewers</th>
                            <th>Started</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($streams as $s)
                            <tr>
                                <td>
                                    <div class="adm-trader-name">{{ $s->title ?: 'Untitled' }}</div>
                                    <div class="adm-trader-meta">{{ $s->token_symbol ?: $s->source ?: '—' }}</div>
                                </td>
                                <td>{{ $s->user->name ?? '—' }}</td>
                                <td>
                                    <span class="adm-badge {{ $s->status === 'live' ? 'ok' : 'neutral' }}">{{ ucfirst($s->status) }}</span>
                                </td>
                                <td>
                                    <span class="adm-badge {{ $s->moderation_status === 'visible' ? 'neutral' : 'danger' }}">{{ ucfirst($s->moderation_status ?? 'visible') }}</span>
                                </td>
                                <td class="adm-num">{{ number_format($s->viewers) }}</td>
                                <td>{{ optional($s->started_at)->diffForHumans() ?? '—' }}</td>
                                <td>
                                    <div class="adm-row-actions">
                                        <form method="POST" action="/admin/streams/{{ $s->id }}/moderate">
                                            @csrf
                                            <input type="hidden" name="action" value="{{ $s->moderation_status === 'visible' ? 'hide' : 'show' }}">
                                            <button type="submit" class="adm-btn sm"><i class="ri-eye-{{ $s->moderation_status === 'visible' ? 'off' : 'line' }}"></i> {{ $s->moderation_status === 'visible' ? 'Hide' : 'Show' }}</button>
                                        </form>
                                        @if ($s->status === 'live')
                                            <form method="POST" action="/admin/streams/{{ $s->id }}/moderate">
                                                @csrf
                                                <input type="hidden" name="action" value="end">
                                                <button type="submit" class="adm-btn sm"><i class="ri-stop-circle-line"></i> End</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="/admin/streams/{{ $s->id }}" onsubmit="return confirm('Delete this stream?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="adm-btn sm icon danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $streams->links('admin.pagination') }}
        @endif
    </div>
@endsection
