<?php

use App\Http\Controllers\Api\V1\Manager\ManagerDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('home', [ManagerDashboardController::class, 'home'])
            ->middleware('right:dashboard.view')
            ->name('home');

        Route::get('team', [ManagerDashboardController::class, 'team'])
            ->middleware('right:managers.view_profiles')
            ->name('team');

        Route::get('assignments', [ManagerDashboardController::class, 'assignments'])
            ->middleware('right:chargebacks.assign')
            ->name('assignments.index');

        Route::post('assignments/{kind}/{id}', [ManagerDashboardController::class, 'assign'])
            ->whereIn('kind', ['chargebacks', 'preventions', 'rdr'])
            ->whereNumber('id')
            ->middleware('right:chargebacks.assign')
            ->name('assignments.assign');

        Route::get('approvals', [ManagerDashboardController::class, 'approvals'])
            ->middleware('right:chargebacks.stage_change')
            ->name('approvals');

        Route::get('activity', [ManagerDashboardController::class, 'activity'])
            ->middleware('right:activity_log.view_all')
            ->name('activity');
    });
