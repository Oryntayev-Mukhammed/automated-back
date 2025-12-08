<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogController;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('posts', [BlogController::class, 'index']);
    Route::get('posts/{slug}', [BlogController::class, 'show']);
    Route::post('posts', [BlogController::class, 'store']);
    Route::post('posts/{slug}', [BlogController::class, 'update']);
    Route::match(['put', 'patch'], 'posts/{slug}', [BlogController::class, 'update']);
    Route::delete('posts/{slug}', [BlogController::class, 'destroy']);
});
