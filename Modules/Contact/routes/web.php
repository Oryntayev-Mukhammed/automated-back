<?php

use Illuminate\Support\Facades\Route;

Route::prefix('contact')->group(function () {
    Route::get('/', function () {
        return 'contact';
    });
});
