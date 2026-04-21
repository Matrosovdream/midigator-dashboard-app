<?php

use App\Http\Controllers\Api\V1\Platform\ActivityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/activity')
    ->name('platform.activity.')
    ->group(function () {
        Route::get('/', [ActivityController::class, 'index'])->name('index');
    });
