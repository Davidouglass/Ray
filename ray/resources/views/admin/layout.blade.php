<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veridian | {{ $title ?? 'Admin' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/veridian-mark.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="{{ asset('css/app-shell.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
@php
    $adminUser = auth('admin')->user();
    $canSettings = $adminUser && $adminUser->canManageSettings();
    $canAdmins   = $adminUser && $adminUser->canManageAdmins();
    $nav = [
        ['dashboard',     '/admin',              'ri-dashboard-3-line',  'Dashboard',     true],
        ['analytics',     '/admin/analytics',    'ri-line-chart-line',   'Analytics',     true],
        ['users',         '/admin/users',        'ri-group-line',        'Users',         true],
        ['admins',        '/admin/admins',       'ri-shield-user-line',  'Admins',        $canAdmins],
        ['traders',       '/admin/traders',      'ri-user-star-line',    'Traders',       true],
        ['subscriptions', '/admin/subscriptions','ri-refund-2-line',     'Subscriptions', true],
        ['copy-trades',   '/admin/copy-trades',  'ri-exchange-funds-line','Trades',       true],
        ['streams',       '/admin/streams',      'ri-live-line',         'Streams',       true],
        ['wallets',       '/admin/wallets',      'ri-wallet-3-line',     'Wallets',       true],
        ['rewards',       '/admin/rewards',      'ri-capsule-line',      'Rewards',       true],
        ['announcements', '/admin/announcements','ri-megaphone-line',    'Announcements', $canSettings],
        ['content',       '/admin/content',      'ri-article-line',      'Content',       $canSettings],
        ['settings',      '/admin/settings',     'ri-settings-3-line',   'Settings',      $canSettings],
        ['audit',         '/admin/audit',        'ri-history-line',      'Audit',         $canSettings],
    ];
@endphp
<body class="adm-body">
    <header class="adm-header">
        <a href="/admin" class="adm-header-brand">
            <img src="{{ asset('images/veridian-brand.png') }}" alt="Veridian">
            <span class="adm-header-tag">Admin</span>
        </a>
        <nav class="adm-nav">
            @foreach ($nav as $item)
                @if ($item[4])
                    <a href="{{ $item[1] }}" class="{{ ($active ?? '') === $item[0] ? 'is-active' : '' }}"><i class="{{ $item[2] }}"></i> {{ $item[3] }}</a>
                @endif
            @endforeach
        </nav>
        <span class="adm-header-spacer"></span>
        @if (session('impersonator_admin_id'))
            <form method="POST" action="{{ route('admin.impersonate.stop') }}">
                @csrf
                <button type="submit" class="adm-btn sm danger"><i class="ri-user-unfollow-line"></i> Stop impersonating</button>
            </form>
        @endif
        <span class="adm-header-user"><i class="ri-shield-user-line"></i> <b>{{ $adminUser->name ?? 'admin' }}</b>
            @if ($adminUser?->admin_role)<span class="adm-role-tag">{{ ucfirst($adminUser->admin_role) }}</span>@endif
        </span>
        <a href="/" class="adm-btn sm"><i class="ri-external-link-line"></i> Back to app</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="adm-btn sm"><i class="ri-logout-box-r-line"></i> Log out</button>
        </form>
    </header>

    <main class="adm-wrap">
        @if (session('ok'))
            <div class="adm-flash ok"><i class="ri-checkbox-circle-line"></i> {{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="adm-flash error"><i class="ri-error-warning-line"></i> {{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
