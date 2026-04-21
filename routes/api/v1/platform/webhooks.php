<?php

use App\Http\Controllers\Api\V1\Platform\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/webhooks')
    ->name('platform.webhooks.')
    ->group(function () {
        Route::get('/', [WebhookController::class, 'index'])->name('index');
        Route::get('{id}', [WebhookController::class, 'show'])->whereNumber('id')->name('show');
    });
