<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ── Middleware global para todas las rutas web ──────────────
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            // Sanitización Anti-XSS en todos los requests web
            \App\Http\Middleware\SanitizeInput::class,
        ]);

        // ── Aliases de middleware ────────────────────────────────────
        $middleware->alias([
            // POS: autenticación por session token
            'pos.auth' => \App\Http\Middleware\PosAuthentication::class,
        ]);

        // ── Rate limiting para la API ────────────────────────────────
        // Laravel 12 ya configura throttle:api por defecto en routes/api.php
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
