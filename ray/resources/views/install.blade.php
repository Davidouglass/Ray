<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Veridian | Installer</title>
    <link rel="icon" type="image/png" href="{{ asset('images/veridian-mark.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="{{ asset('css/app-shell.css') }}">
    <style>
        body { background: var(--bg); color: var(--text-primary); font-family: 'Geist', system-ui, sans-serif; }
        .ins-wrap { max-width: 720px; margin: 0 auto; padding: 40px 20px 80px; }
        .ins-head { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; }
        .ins-head img { height: 26px; }
        .ins-head h1 { font-size: 22px; margin: 0; }
        .ins-sub { color: var(--text-tertiary); font-size: 13.5px; margin: 0 0 24px; }
        .ins-card { background: var(--bg-secondary); border: 1px solid var(--primary-stroke); border-radius: 10px; overflow: hidden; }
        .ins-row { display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; border-bottom: 1px solid var(--primary-stroke); }
        .ins-row:last-child { border-bottom: 0; }
        .ins-row i { font-size: 18px; margin-top: 1px; }
        .ins-row.ok i { color: var(--up); }
        .ins-row.fail i { color: var(--red); }
        .ins-row.warn i { color: #F0B90B; }
        .ins-row b { display: block; font-size: 14px; }
        .ins-row span { color: var(--text-tertiary); font-size: 13px; word-break: break-word; }
        .ins-note { margin-top: 20px; padding: 14px 18px; border-radius: 10px; border: 1px solid rgba(240,185,11,.35); background: rgba(240,185,11,.08); color: var(--text-secondary); font-size: 13px; line-height: 1.6; }
        .ins-note b { color: #F0B90B; }
        .ins-actions { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .ins-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; background: var(--blue); color: #fff; text-decoration: none; font-weight: 600; font-size: 13.5px; }
    </style>
</head>
<body>
    <div class="ins-wrap">
        <div class="ins-head">
            <img src="{{ asset('images/veridian-mark.png') }}" alt="Veridian">
            <h1>Installation results</h1>
        </div>
        <p class="ins-sub">Veridian setup helper — remove the INSTALL_TOKEN from .env and delete <code>app/Http/Controllers/InstallController.php</code> + the <code>/install</code> route once finished.</p>

        <div class="ins-card">
            @foreach ($results as $r)
                <div class="ins-row {{ $r[0] }}">
                    <i class="ri-{{ $r[0] === 'ok' ? 'checkbox-circle-line' : ($r[0] === 'fail' ? 'close-circle-line' : 'alert-line') }}"></i>
                    <div>
                        <b>{{ $r[1] }}</b>
                        <span>{{ $r[2] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($ok)
            <div class="ins-note">
                <b>Security step required:</b> now open your <code>.env</code> and remove the <code>INSTALL_TOKEN</code> line, then delete this installer route. Otherwise anyone who knows the token can re-run migrations.
            </div>
            <div class="ins-actions">
                <a class="ins-btn" href="/"><i class="ri-home-line"></i> Go to site</a>
                <a class="ins-btn" href="/admin/login"><i class="ri-shield-user-line"></i> Admin login</a>
            </div>
        @endif
    </div>
</body>
</html>
