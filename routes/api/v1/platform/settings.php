<?php

use App\Http\Controllers\Api\V1\Platform\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'platform.admin'])
    ->prefix('platform/settings')
    ->name('platform.settings.')
    ->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'upsert'])->name('upsert');
        Route::delete('/', [SettingController::class, 'destroy'])->name('destroy');
    });
