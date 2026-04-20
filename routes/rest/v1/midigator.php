<?php

use App\Http\Controllers\Rest\V1\Midigator\MidigatorWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')
    ->name('webhooks.')
    ->group(function () {
        Route::post('midigator', [MidigatorWebhookController::class, 'handle'])
            ->middleware('midigator.webhook.auth')
            ->name('midigator');
    });
