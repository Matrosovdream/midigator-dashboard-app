<?php

use App\Http\Controllers\Api\V1\Platform\OverviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/overview')
    ->name('platform.overview.')
    ->group(function () {
        Route::get('summary', [OverviewController::class, 'summary'])->name('summary');
    });
