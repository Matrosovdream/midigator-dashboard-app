<?php

use App\Http\Controllers\Api\V1\Rdr\RdrController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('rdr-cases')
    ->name('rdr.')
    ->group(function () {
        Route::get('/', [RdrController::class, 'index'])->middleware('right:rdr.view')->name('index');
        Route::get('{id}', [RdrController::class, 'show'])->whereNumber('id')->middleware('right:rdr.view')->name('show');
        Route::patch('{id}', [RdrController::class, 'update'])->whereNumber('id')->middleware('right:rdr.edit')->name('update');
        Route::post('{id}/stage', [RdrController::class, 'changeStage'])->whereNumber('id')->middleware('right:rdr.stage_change')->name('stage');
        Route::post('{id}/assign', [RdrController::class, 'assign'])->whereNumber('id')->middleware('right:rdr.assign')->name('assign');
        Route::post('{id}/hide', [RdrController::class, 'hide'])->whereNumber('id')->middleware('right:rdr.hide')->name('hide');
        Route::post('{id}/resolve', [RdrController::class, 'resolve'])->whereNumber('id')->middleware('right:rdr.resolve')->name('resolve');
    });
