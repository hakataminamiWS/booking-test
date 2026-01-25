<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\ExpirePendingBookings::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'owner' => \App\Http\Middleware\OwnerMiddleware::class,
            // 'expire.pending' alias can be removed if not used elsewhere, or kept for clarity. 
            // Since we are applying it globally to web, alias is less useful but harmless.
            'expire.pending' => \App\Http\Middleware\ExpirePendingBookings::class,
        ]);

        $middleware->redirectTo(
            guests: '/',
            users: '/'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
