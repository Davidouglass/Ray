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
<body class="adm-body adm-login-body">
    <div class="adm-login-card">
        <div class="adm-login-brand">
            <img src="{{ asset('images/veridian-brand.png') }}" alt="Veridian">
            <span class="adm-header-tag">Admin</span>
        </div>

        <h1 class="adm-login-title">Administrator sign in</h1>
        <p class="adm-login-sub">A separate, restricted area. Your Veridian customer session is not affected.</p>

        @if ($errors->any())
            <div class="adm-flash error"><i class="ri-error-warning-line"></i> {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="adm-login-form">
            @csrf
            <div class="adm-field">
                <label>Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            <div class="adm-field">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <label class="adm-check"><input type="checkbox" name="remember" value="1"> Remember me on this device</label>
            <button type="submit" class="adm-btn primary adm-login-submit"><i class="ri-lock-line"></i> Sign in</button>
        </form>

        <a href="/" class="adm-login-back"><i class="ri-arrow-left-line"></i> Back to Veridian</a>
    </div>
</body>
</html>
