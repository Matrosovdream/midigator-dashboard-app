<?php

use App\Http\Controllers\Api\V1\Platform\EmailLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/emails/logs')
    ->name('platform.emails.logs.')
    ->group(function () {
        Route::get('/', [EmailLogController::class, 'index'])->name('index');
        Route::get('{id}', [EmailLogController::class, 'show'])->whereNumber('id')->name('show');
    });
