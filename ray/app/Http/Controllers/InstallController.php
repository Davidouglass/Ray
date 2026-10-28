<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * One-time, token-protected setup helper for hosts without SSH/terminal.
 *
 * Usage:
 *   1. Add INSTALL_TOKEN=some-long-random-string to .env on the server.
 *   2. Visit  https://yourdomain.com/install?token=some-long-random-string
 *   3. Remove INSTALL_TOKEN from .env when finished (and ideally delete this route).
 */
class InstallController extends Controller
{
    public function run(Request $request)
    {
        $expected = trim((string) env('INSTALL_TOKEN', ''));

        if ($expected === '') {
            abort(404);
        }

        if (! hash_equals($expected, (string) $request->query('token', ''))) {
            abort(403, 'Invalid install token.');
        }

        $results = [];
        $ok = true;

        // 1. Database connectivity
        try {
            DB::connection()->getPdo();
            $results[] = ['ok', 'Database connection', DB::connection()->getDatabaseName()];
        } catch (\Throwable $e) {
            $results[] = ['fail', 'Database connection', $e->getMessage()];
            return view('install', ['results' => $results, 'ok' => false]);
        }

        // 2. Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            $results[] = ['ok', 'Migrations', trim(Artisan::output()) ?: 'Nothing to migrate'];
        } catch (\Throwable $e) {
            $results[] = ['fail', 'Migrations', $e->getMessage()];
            $ok = false;
        }

        // 3. Storage + bootstrap cache permissions
        $dirs = [
            storage_path('app'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        foreach ($dirs as $dir) {
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
        $results[] = ['ok', 'Writable directories', 'Ensured ' . count($dirs) . ' directories exist'];

        // 4. Public upload directories
        foreach (['images/traders', 'avatars'] as $rel) {
            $path = public_path($rel);
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }
        $results[] = ['ok', 'Upload directories', 'public/images/traders, public/avatars'];

        // 5. Clear stale caches compiled on another machine
        try {
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('cache:clear');
            $results[] = ['ok', 'Cleared caches', 'config, view, route, cache'];
        } catch (\Throwable $e) {
            $results[] = ['fail', 'Cleared caches', $e->getMessage()];
        }

        // 6. Optional: create a super admin from query params
        if ($request->filled('admin_email') && $request->filled('admin_password')) {
            $email = (string) $request->query('admin_email');
            $pass  = (string) $request->query('admin_password');
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
                $results[] = ['fail', 'Create admin', 'Provide a valid email and an 8+ char password'];
            } else {
                try {
                    \App\Models\User::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => 'Veridian Admin',
                            'password' => $pass,
                            'is_admin' => true,
                            'admin_role' => 'super',
                            'status' => 'active',
                        ]
                    );
                    $results[] = ['ok', 'Create admin', $email . ' (super)'];
                } catch (\Throwable $e) {
                    $results[] = ['fail', 'Create admin', $e->getMessage()];
                }
            }
        } else {
            $results[] = ['warn', 'Create admin', 'Skipped — add &admin_email=you@domain.com&admin_password=changeme123 to create one'];
        }

        // 7. Rebuild caches for production
        if (config('app.env') === 'production') {
            try {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
                $results[] = ['ok', 'Built production caches', 'config, route, view'];
            } catch (\Throwable $e) {
                $results[] = ['fail', 'Built production caches', $e->getMessage()];
            }
        } else {
            $results[] = ['warn', 'Production caches', 'Skipped (APP_ENV is not production)'];
        }

        return view('install', ['results' => $results, 'ok' => $ok]);
    }
}
