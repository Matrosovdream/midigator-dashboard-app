<?php

use App\Http\Controllers\Api\V1\Prevention\PreventionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('preventions')
    ->name('preventions.')
    ->group(function () {
        Route::get('/', [PreventionController::class, 'index'])->middleware('right:preventions.view')->name('index');
        Route::get('{id}', [PreventionController::class, 'show'])->whereNumber('id')->middleware('right:preventions.view')->name('show');
        Route::patch('{id}', [PreventionController::class, 'update'])->whereNumber('id')->middleware('right:preventions.edit')->name('update');
        Route::post('{id}/stage', [PreventionController::class, 'changeStage'])->whereNumber('id')->middleware('right:preventions.stage_change')->name('stage');
        Route::post('{id}/assign', [PreventionController::class, 'assign'])->whereNumber('id')->middleware('right:preventions.assign')->name('assign');
        Route::post('{id}/hide', [PreventionController::class, 'hide'])->whereNumber('id')->middleware('right:preventions.hide')->name('hide');
        Route::post('{id}/resolve', [PreventionController::class, 'resolve'])->whereNumber('id')->middleware('right:preventions.resolve')->name('resolve');
    });
