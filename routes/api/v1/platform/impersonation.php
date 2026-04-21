<?php

use App\Http\Controllers\Api\V1\Platform\ImpersonationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('platform/impersonate')
    ->name('platform.impersonate.')
    ->group(function () {
        // Stop is available to the currently-acting session; platform.admin is NOT required
        // because while impersonating, the active user is the target tenant user.
        Route::post('stop', [ImpersonationController::class, 'stop'])->name('stop');

        // Start requires platform admin.
        Route::middleware('platform.admin')
            ->post('{userId}', [ImpersonationController::class, 'start'])
            ->whereNumber('userId')
            ->name('start');
    });
