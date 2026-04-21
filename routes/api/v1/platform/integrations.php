<?php

use App\Http\Controllers\Api\V1\Platform\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/integrations')
    ->name('platform.integrations.')
    ->group(function () {
        Route::get('health', [IntegrationController::class, 'health'])->name('health');
    });
