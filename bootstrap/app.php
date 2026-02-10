<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'store.access' => \App\Http\Middleware\EnsureStoreAccess::class,
            'branch.access' => \App\Http\Middleware\EnsureBranchAccess::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
        ]);

        // Configure API middleware group
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\EnsureStoreAccess::class, // Auto-add for all API routes
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
