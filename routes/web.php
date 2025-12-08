<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Swagger UI (static)
Route::view('/swagger', 'swagger');
