<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 🧩 Middleware global (web + api)
        $middleware->web(prepend: [
            // Jika butuh middleware tambahan di web nanti bisa ditaruh di sini
        ]);

        // 🧩 Middleware untuk API
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class, // penting untuk Sanctum
        ]);

        // 🧩 Alias middleware (agar bisa pakai 'admin' di route)
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Kamu bisa menambahkan custom handler error di sini nanti
    })
    ->create();
