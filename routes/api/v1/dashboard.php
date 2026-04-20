<?php

use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('summary', [DashboardController::class, 'summary'])
            ->middleware('right:dashboard.view')
            ->name('summary');

        Route::get('manager-performance', [DashboardController::class, 'managerPerformance'])
            ->middleware('right:dashboard.manager_performance')
            ->name('manager-performance');

        Route::get('recent-activity', [DashboardController::class, 'recentActivity'])
            ->middleware('right:dashboard.view')
            ->name('recent-activity');
    });
