<?php

use App\Http\Controllers\Api\V1\Platform\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/tenants')
    ->name('platform.tenants.')
    ->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::post('/', [TenantController::class, 'store'])->name('store');
        Route::post('/test-connection', [TenantController::class, 'testConnection'])->name('test-connection');

        Route::prefix('{id}')
            ->whereNumber('id')
            ->group(function () {
                Route::get('/', [TenantController::class, 'show'])->name('show');
                Route::patch('/', [TenantController::class, 'update'])->name('update');
                Route::delete('/', [TenantController::class, 'destroy'])->name('destroy');
                Route::get('overview', [TenantController::class, 'overview'])->name('overview');
                Route::get('users', [TenantController::class, 'users'])->name('users');
                Route::get('activity', [TenantController::class, 'activity'])->name('activity');
                Route::get('webhooks', [TenantController::class, 'webhooks'])->name('webhooks');
                Route::post('toggle-active', [TenantController::class, 'toggleActive'])->name('toggle-active');
            });
    });
