<?php

use App\Http\Controllers\Api\V1\Chargeback\ChargebackController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('chargebacks')
    ->name('chargebacks.')
    ->group(function () {
        Route::get('/', [ChargebackController::class, 'index'])->middleware('right:chargebacks.view')->name('index');
        Route::post('hide/bulk', [ChargebackController::class, 'bulkHide'])->middleware('right:chargebacks.hide')->name('bulk-hide');
        Route::get('{id}', [ChargebackController::class, 'show'])->whereNumber('id')->middleware('right:chargebacks.view')->name('show');
        Route::patch('{id}', [ChargebackController::class, 'update'])->whereNumber('id')->middleware('right:chargebacks.edit')->name('update');
        Route::post('{id}/stage', [ChargebackController::class, 'changeStage'])->whereNumber('id')->middleware('right:chargebacks.stage_change')->name('stage');
        Route::post('{id}/assign', [ChargebackController::class, 'assign'])->whereNumber('id')->middleware('right:chargebacks.assign')->name('assign');
        Route::post('{id}/hide', [ChargebackController::class, 'hide'])->whereNumber('id')->middleware('right:chargebacks.hide')->name('hide');
    });
