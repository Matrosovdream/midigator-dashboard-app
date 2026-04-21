<?php

use App\Http\Controllers\Api\V1\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])
    ->prefix('orders')
    ->name('orders.')
    ->group(function () {
        Route::get('/', [OrderController::class, 'index'])->middleware('right:orders.view')->name('index');
        Route::post('/', [OrderController::class, 'store'])->middleware('right:orders.create')->name('store');
        Route::get('{id}', [OrderController::class, 'show'])->whereNumber('id')->middleware('right:orders.view')->name('show');
        Route::patch('{id}', [OrderController::class, 'update'])->whereNumber('id')->middleware('right:orders.edit')->name('update');
        Route::post('{id}/submit', [OrderController::class, 'submit'])->whereNumber('id')->middleware('right:orders.submit')->name('submit');
    });
