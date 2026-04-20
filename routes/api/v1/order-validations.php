<?php

use App\Http\Controllers\Api\V1\OrderValidation\OrderValidationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('order-validations')
    ->name('order-validations.')
    ->group(function () {
        Route::get('/', [OrderValidationController::class, 'index'])->middleware('right:order_validations.view')->name('index');
        Route::get('{id}', [OrderValidationController::class, 'show'])->whereNumber('id')->middleware('right:order_validations.view')->name('show');
        Route::patch('{id}', [OrderValidationController::class, 'update'])->whereNumber('id')->middleware('right:order_validations.edit')->name('update');
        Route::post('{id}/stage', [OrderValidationController::class, 'changeStage'])->whereNumber('id')->middleware('right:order_validations.stage_change')->name('stage');
        Route::post('{id}/assign', [OrderValidationController::class, 'assign'])->whereNumber('id')->middleware('right:order_validations.assign')->name('assign');
        Route::post('{id}/hide', [OrderValidationController::class, 'hide'])->whereNumber('id')->middleware('right:order_validations.hide')->name('hide');
    });
