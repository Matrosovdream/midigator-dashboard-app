<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('rest.v1.')
    ->group(function () {
        foreach (glob(__DIR__.'/rest/v1/*.php') as $file) {
            require $file;
        }
    });
