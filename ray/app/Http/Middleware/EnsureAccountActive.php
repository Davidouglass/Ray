<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user && ! $user->isActive()) {
            $reason = $user->suspended_reason ? ' Reason: ' . $user->suspended_reason : '';

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, 'This account is suspended.' . $reason);
            }

            return redirect('/login')->withErrors([
                'email' => 'This account is suspended.' . $reason,
            ]);
        }

        return $next($request);
    }
}
