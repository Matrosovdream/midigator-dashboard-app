<?php

use App\Http\Controllers\Api\V1\Comment\CommentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::get('{type}/{id}/comments', [CommentController::class, 'index'])
        ->whereIn('type', ['chargeback', 'prevention', 'order', 'order_validation', 'rdr'])
        ->whereNumber('id')
        ->middleware('right:comments.view')
        ->name('comments.index');

    Route::post('{type}/{id}/comments', [CommentController::class, 'store'])
        ->whereIn('type', ['chargeback', 'prevention', 'order', 'order_validation', 'rdr'])
        ->whereNumber('id')
        ->middleware('right:comments.create')
        ->name('comments.store');

    Route::delete('comments/{commentId}', [CommentController::class, 'destroy'])
        ->whereNumber('commentId')
        ->middleware('right:comments.delete')
        ->name('comments.destroy');
});
