<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (! $user || ! $user->is_admin) {
            Auth::guard('admin')->logout();

            if ($request->expectsJson()) {
                abort(403, 'This area is restricted to administrators.');
            }

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
