<?php

use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('summary', [DashboardController::class, 'summary'])
            ->middleware('right:dashboard.view')
            ->name('summary');
    });
