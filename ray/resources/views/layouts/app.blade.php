<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veridian | {{ $title ?? 'App' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Geist+Mono:wght@100..900&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}?v={{ @filemtime(public_path('css/app-shell.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/axiom-magic.css') }}?v={{ @filemtime(public_path('css/axiom-magic.css')) }}">
    @stack('styles')
</head>
@php
    $spot   = $spot ?? (function () { try { return app(\App\Services\MarketFeed::class)->spot(); } catch (\Throwable $e) { return []; } })();
    $tokens = $tokens ?? [];
    $active = $active ?? '';
@endphp
<body class="platform-body">
    <div class="platform-app-shell mg-content">
        <div class="platform-shell-row">
            <div class="platform-col">

                @include('partials.top-nav', ['active' => $active])
                @include('partials.ticker', ['tokens' => $tokens])

                <main class="platform-main-content">
                    <div class="platform-layout-container">
                        @yield('content')
                    </div>
                </main>

                @include('partials.status-bar', ['spot' => $spot])
                @include('partials.mobile-navbar', ['active' => $active])

            </div>
        </div>
    </div>
    @stack('scripts')
    <script src="{{ asset('js/axiom-magic.js') }}" defer></script>
</body>
</html>
