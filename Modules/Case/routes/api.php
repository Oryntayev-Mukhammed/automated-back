<?php

use Illuminate\Support\Facades\Route;
use Modules\Case\Http\Controllers\CaseController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('cases', [CaseController::class, 'index']);
    Route::get('cases/{slug}', [CaseController::class, 'show']);
    Route::post('cases', [CaseController::class, 'store']);
    Route::post('cases/{slug}', [CaseController::class, 'update']);
    Route::match(['put', 'patch'], 'cases/{slug}', [CaseController::class, 'update']);
    Route::delete('cases/{slug}', [CaseController::class, 'destroy']);
});
