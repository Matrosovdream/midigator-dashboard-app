<?php

use App\Http\Controllers\Api\V1\Search\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::get('search', [SearchController::class, 'index'])->name('search.index');
});
