<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Middleware yang dibutuhkan
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /*
        |--------------------------------------------------------------------------
        | 🌍 Global Middleware
        |--------------------------------------------------------------------------
        */
        $middleware->use([
            HandleCors::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 🧩 Web Middleware
        |--------------------------------------------------------------------------
        */
        $middleware->web(prepend: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 🔐 API Middleware (untuk Sanctum SPA / Frontend React)
        |--------------------------------------------------------------------------
        | - Memastikan request dari frontend (localhost:3000) dianggap "stateful"
        | - Membolehkan pengiriman cookie & CSRF
        */
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 🧱 Alias Middleware
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Kamu bisa tambahkan handler custom di sini
    })
    ->create();
