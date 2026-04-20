<?php

use App\Http\Controllers\Api\V1\ManagerProfile\ManagerProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::patch('users/{userId}/score', [ManagerProfileController::class, 'updateScore'])
        ->whereNumber('userId')
        ->middleware('right:managers.score')
        ->name('managers.score.update');
});
