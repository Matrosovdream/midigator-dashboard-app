<?php

use App\Http\Controllers\Api\V1\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notification-settings', [NotificationController::class, 'settings'])->name('notifications.settings');
    Route::patch('notification-settings', [NotificationController::class, 'updateSettings'])->name('notifications.settings.update');
});
