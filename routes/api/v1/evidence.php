<?php

use App\Http\Controllers\Api\V1\Evidence\EvidenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::get('{type}/{id}/evidence', [EvidenceController::class, 'index'])
        ->whereIn('type', ['chargeback', 'prevention', 'rdr', 'order'])
        ->whereNumber('id')
        ->middleware('right:evidence.view')
        ->name('evidence.index');

    Route::post('{type}/{id}/evidence', [EvidenceController::class, 'store'])
        ->whereIn('type', ['chargeback', 'prevention', 'rdr', 'order'])
        ->whereNumber('id')
        ->middleware('right:evidence.upload')
        ->name('evidence.store');

    Route::delete('evidence/{evidenceId}', [EvidenceController::class, 'destroy'])
        ->whereNumber('evidenceId')
        ->middleware('right:evidence.delete')
        ->name('evidence.destroy');
});
