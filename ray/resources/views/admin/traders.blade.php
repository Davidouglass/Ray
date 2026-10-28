@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Copy Traders</div>
            <div class="adm-subtitle">{{ number_format($traders->total()) }} trader{{ $traders->total() === 1 ? '' : 's' }} on the platform.</div>
        </div>
        <span class="adm-header-spacer"></span>
        <a href="/admin/traders/create" class="adm-btn primary"><i class="ri-add-line"></i> New trader</a>
    </div>

    <form method="GET" action="/admin/traders" class="adm-toolbar">
        <label class="adm-search">
            <i class="ri-search-line"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="Search name, slug or wallet">
        </label>
        <div class="adm-chips">
            <a href="/admin/traders?status=all{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'all' ? 'is-active' : '' }}">All</a>
            <a href="/admin/traders?status=active{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'active' ? 'is-active' : '' }}">Active</a>
            <a href="/admin/traders?status=inactive{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'inactive' ? 'is-active' : '' }}">Inactive</a>
            <a href="/admin/traders?status=verified{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'verified' ? 'is-active' : '' }}">Verified</a>
            <a href="/admin/traders?status=featured{{ $q ? '&q=' . urlencode($q) : '' }}" class="{{ $status === 'featured' ? 'is-active' : '' }}">Featured</a>
        </div>
        <button type="submit" class="adm-btn">Search</button>
        @if ($q || $status !== 'all')
            <a href="/admin/traders" class="adm-btn sm">Reset</a>
        @endif
    </form>

    <div class="adm-card">
        @if ($traders->isEmpty())
            <div class="adm-empty">No copy traders match your filters.</div>
        @else
            <div class="adm-table-scroll">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Trader</th>
                            <th>Wallet</th>
                            <th>Risk</th>
                            <th class="adm-num">30d ROI</th>
                            <th class="adm-num">Win %</th>
                            <th class="adm-num">Followers</th>
                            <th>Flags</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($traders as $t)
                            <tr>
                                <td>
                                    <div class="adm-trader-cell">
                                        <img class="adm-avatar" src="{{ $t->avatar_url ?: asset('images/veridian-mark.png') }}" alt="" onerror="this.src='{{ asset('images/veridian-mark.png') }}'">
                                        <div>
                                            <div class="adm-trader-name">{{ $t->display_name }}</div>
                                            <div class="adm-trader-meta">{{ $t->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="adm-mono">{{ \Illuminate\Support\Str::limit($t->wallet_address, 10, '…') }}</span></td>
                                <td><span class="adm-badge neutral">{{ strtoupper($t->risk_level) }}</span></td>
                                <td class="adm-num {{ $t->roi_30d >= 0 ? '' : '' }}">{{ number_format($t->roi_30d, 1) }}%</td>
                                <td class="adm-num">{{ number_format($t->win_rate, 0) }}%</td>
                                <td class="adm-num">{{ number_format($t->followers_count) }}</td>
                                <td>
                                    <div class="adm-flags">
                                        <form method="POST" action="/admin/traders/{{ $t->slug }}/toggle">
                                            @csrf
                                            <input type="hidden" name="field" value="is_active">
                                            <button type="submit" class="adm-flag {{ $t->is_active ? 'is-on is-ok' : '' }}" title="Toggle active"><i class="ri-toggle-{{ $t->is_active ? 'fill' : 'line' }}"></i> Active</button>
                                        </form>
                                        <form method="POST" action="/admin/traders/{{ $t->slug }}/toggle">
                                            @csrf
                                            <input type="hidden" name="field" value="is_verified">
                                            <button type="submit" class="adm-flag {{ $t->is_verified ? 'is-on' : '' }}" title="Toggle verified"><i class="ri-verified-badge-{{ $t->is_verified ? 'fill' : 'line' }}"></i> Verified</button>
                                        </form>
                                        <form method="POST" action="/admin/traders/{{ $t->slug }}/toggle">
                                            @csrf
                                            <input type="hidden" name="field" value="is_featured">
                                            <button type="submit" class="adm-flag {{ $t->is_featured ? 'is-on is-warn' : '' }}" title="Toggle featured"><i class="ri-star-{{ $t->is_featured ? 'fill' : 'line' }}"></i> Featured</button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <div class="adm-row-actions">
                                        <a href="/admin/traders/{{ $t->slug }}/edit" class="adm-btn sm icon" title="Edit"><i class="ri-pencil-line"></i></a>
                                        <form method="POST" action="/admin/traders/{{ $t->slug }}" onsubmit="return confirm('Delete {{ $t->display_name }}?');">
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
            {{ $traders->links('admin.pagination') }}
        @endif
    </div>
@endsection
