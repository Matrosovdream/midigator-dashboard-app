<?php

use App\Http\Controllers\Api\V1\Email\EmailController;
use App\Http\Controllers\Api\V1\Email\EmailTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant.scope'])->group(function () {
    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->middleware('right:emails.view')->name('index');
        Route::post('/', [EmailTemplateController::class, 'store'])->middleware('right:emails.templates_manage')->name('store');
        Route::patch('{id}', [EmailTemplateController::class, 'update'])->whereNumber('id')->middleware('right:emails.templates_manage')->name('update');
        Route::delete('{id}', [EmailTemplateController::class, 'destroy'])->whereNumber('id')->middleware('right:emails.templates_manage')->name('destroy');
    });

    Route::post('emails/send', [EmailController::class, 'send'])->middleware('right:emails.send')->name('emails.send');
    Route::get('email-logs', [EmailController::class, 'logs'])->middleware('right:emails.view')->name('emails.logs');
});
