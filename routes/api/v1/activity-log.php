<?php

use App\Http\Controllers\Api\V1\ActivityLog\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::get('activity-log', [ActivityLogController::class, 'index'])
        ->middleware('right:activity_log.view')
        ->name('activity-log.index');
});
