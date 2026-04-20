<?php

use App\Http\Controllers\Api\V1\Export\ExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::get('export/{type}.csv', [ExportController::class, 'csv'])
        ->whereIn('type', ['chargeback', 'prevention', 'order', 'order_validation', 'rdr'])
        ->middleware('right:export.run')
        ->name('export.csv');
});
