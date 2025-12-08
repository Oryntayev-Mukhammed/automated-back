<?php

use Illuminate\Support\Facades\Route;
use Modules\Case\Http\Controllers\CaseController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cases', CaseController::class)->names('case');
});
