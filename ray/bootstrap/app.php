<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Env;

// Apache on Windows (threaded mpm) shares the process environment across worker
// threads, so the putenv adapter leaks APP_ENV and races while loading ".env".
// That intermittently made Laravel skip ".env" (falling back to the sqlite /
// no-APP_KEY defaults). Reading env through $_ENV/$_SERVER only is thread-safe.
Env::disablePutenv();

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\EnsureAccountActive::class);
        $middleware->append(\App\Http\Middleware\NoCacheHeaders::class);
        $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
        $middleware->alias([
            'admin'   => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'feature' => \App\Http\Middleware\EnsureFeatureEnabled::class,
        ]);
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));
        $middleware->redirectUsersTo(fn ($request) => $request->is('admin', 'admin/*') ? route('admin.dashboard') : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
