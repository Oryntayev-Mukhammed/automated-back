<?php

use Illuminate\Support\Facades\Route;
use Modules\I18n\Http\Controllers\I18nController;

// Exposed under /api/v1/i18n/{lang}
Route::prefix('api/v1')
    ->middleware(['api', 'throttle:60,1'])
    ->group(function () {
        Route::get('i18n/{lang?}', [I18nController::class, 'index']);
    });
