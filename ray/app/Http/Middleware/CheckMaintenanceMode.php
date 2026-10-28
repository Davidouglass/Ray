<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SiteSetting::bool('maintenance_mode')) {
            return $next($request);
        }

        if ($this->isExempt($request)) {
            return $next($request);
        }

        if ($this->outsideScheduledWindow()) {
            return $next($request);
        }

        $message = trim((string) SiteSetting::get('maintenance_message', ''));

        return response()->view('errors.maintenance', [
            'message' => $message !== '' ? $message : 'We are performing scheduled maintenance. Please check back shortly.',
        ], 503);
    }

    /** True when a scheduled start/end window has been configured but is not active yet (or already over). */
    private function outsideScheduledWindow(): bool
    {
        $startsAt = trim((string) SiteSetting::get('maintenance_starts_at', ''));
        $endsAt   = trim((string) SiteSetting::get('maintenance_ends_at', ''));

        if ($startsAt !== '') {
            try {
                if (now()->lt(\Illuminate\Support\Carbon::parse($startsAt))) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Unparsable window: fall through and treat maintenance as active.
            }
        }

        if ($endsAt !== '') {
            try {
                if (now()->gt(\Illuminate\Support\Carbon::parse($endsAt))) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Unparsable window: fall through and treat maintenance as active.
            }
        }

        return false;
    }

    private function isExempt(Request $request): bool
    {
        if ($request->is('admin', 'admin/*', 'up', 'api/*')) {
            return true;
        }

        if ($request->is('login', 'signup', 'logout', 'register', 'terms', 'privacy')) {
            return true;
        }

        return Auth::guard('admin')->check();
    }
}
