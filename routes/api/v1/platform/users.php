<?php

use App\Http\Controllers\Api\V1\Platform\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/users')
    ->name('platform.users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');

        Route::prefix('{id}')
            ->whereNumber('id')
            ->group(function () {
                Route::post('toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
                Route::post('toggle-platform-admin', [UserController::class, 'togglePlatformAdmin'])->name('toggle-platform-admin');
            });
    });
