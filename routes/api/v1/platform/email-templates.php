<?php

use App\Http\Controllers\Api\V1\Platform\EmailTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/emails/templates')
    ->name('platform.emails.templates.')
    ->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::post('/', [EmailTemplateController::class, 'store'])->name('store');

        Route::prefix('{id}')
            ->whereNumber('id')
            ->group(function () {
                Route::get('/', [EmailTemplateController::class, 'show'])->name('show');
                Route::patch('/', [EmailTemplateController::class, 'update'])->name('update');
                Route::delete('/', [EmailTemplateController::class, 'destroy'])->name('destroy');
            });
    });
