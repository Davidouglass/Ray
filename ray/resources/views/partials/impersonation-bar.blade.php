@php
    $impersonating = \Illuminate\Support\Facades\Auth::guard('web')->check() && session()->has('impersonator_admin_id');
@endphp
@if ($impersonating)
    <div class="imp-bar">
        <i class="ri-user-search-line"></i>
        <span>Viewing the app as <b>{{ \Illuminate\Support\Facades\Auth::user()->name }}</b></span>
        <span class="imp-spacer"></span>
        <form method="POST" action="/admin/impersonate/stop">
            @csrf
            <button type="submit" class="imp-btn"><i class="ri-user-unfollow-line"></i> Stop impersonating</button>
        </form>
    </div>
@endif
