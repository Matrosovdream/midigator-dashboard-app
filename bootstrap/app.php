<?php

use App\Http\Middleware\CheckRight;
use App\Http\Middleware\EnsureTenantScope;
use App\Http\Middleware\ValidateMidigatorWebhookAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('rest')
                ->group(__DIR__.'/../routes/rest.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'right' => CheckRight::class,
            'tenant.scope' => EnsureTenantScope::class,
            'midigator.webhook.auth' => ValidateMidigatorWebhookAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
