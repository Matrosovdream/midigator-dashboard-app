<?php

use App\Http\Controllers\Api\V1\Platform\RightController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/rights')
    ->name('platform.rights.')
    ->group(function () {
        Route::get('/', [RightController::class, 'index'])->name('index');
    });
