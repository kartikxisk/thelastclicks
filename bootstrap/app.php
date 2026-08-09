<?php

use App\Http\Middleware\RedirectToCanonicalHost;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\ResponseCache\Middlewares\CacheResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'cacheResponse' => CacheResponse::class,
        ]);

        // Global, and in this order. The host redirect runs first so a request to
        // the wrong host is turned away before anything renders it — otherwise
        // the response cache stores a page keyed under a host we are trying to
        // stop serving.
        $middleware->prepend([
            RedirectToCanonicalHost::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
