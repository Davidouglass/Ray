<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veridian</title>
    <meta name="description" content="The Gateway to DeFi">
    <meta property="og:title" content="Veridian">
    <meta property="og:description" content="The Gateway to DeFi">
    <meta property="og:type" content="website">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav class="nav" id="nav">
        <div class="nav-inner">
            <a href="/" class="nav-brand" aria-label="Veridian home">
                <img src="{{ asset('images/veridian-brand.png') }}" alt="Veridian" class="nav-wordmark-img">
            </a>
            <div class="nav-actions">
                <a href="/login" class="btn btn-login">Login</a>
                <a href="/signup" class="btn btn-signup">Sign up</a>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer-top">
            <div class="footer-left">
                <img src="{{ asset('images/veridian-brand.png') }}" alt="Veridian" class="footer-logo-img">
                <span class="footer-copy">&copy; 2026 Veridian. All rights reserved.</span>
            </div>
            <div class="footer-right">
                @if ($supportUrl = \App\Models\SiteSetting::supportUrl())
                    <a href="{{ $supportUrl }}" target="_blank" rel="noopener">Support</a>
                @endif
            </div>
        </div>
        <div class="footer-legal">
            <a href="/privacy">Privacy Policy</a>
            <span>&bull;</span>
            <a href="/terms">Terms of Service</a>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
