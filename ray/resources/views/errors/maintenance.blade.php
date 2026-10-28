<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veridian | Maintenance</title>
    <link rel="icon" type="image/png" href="{{ asset('images/veridian-mark.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}">
    <style>
        body { background: var(--bg); color: var(--text-primary); font-family: 'Geist', system-ui, sans-serif; }
        .mt-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .mt-card { max-width: 460px; width: 100%; background: var(--bg-secondary); border: 1px solid var(--primary-stroke); border-radius: 10px; padding: 40px 32px; text-align: center; }
        .mt-card img { width: 48px; height: 48px; margin-bottom: 18px; }
        .mt-icon { font-size: 44px; color: var(--blue); margin-bottom: 10px; }
        .mt-card h1 { font-size: 22px; margin: 0 0 10px; }
        .mt-card p { color: var(--text-secondary); line-height: 1.6; margin: 0 0 22px; }
        .mt-card a { color: var(--blue); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="mt-wrap">
        <div class="mt-card">
            <img src="{{ asset('images/veridian-mark.png') }}" alt="Veridian">
            <div class="mt-icon"><i class="ri-tools-line"></i></div>
            <h1>Under maintenance</h1>
            <p>{{ $message ?? 'We are performing scheduled maintenance. Please check back shortly.' }}</p>
            <a href="/login"><i class="ri-refresh-line"></i> Try again</a>
        </div>
    </div>
</body>
</html>
