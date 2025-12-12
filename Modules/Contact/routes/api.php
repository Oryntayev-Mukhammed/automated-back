<?php

use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\ContactSubmissionController;

Route::post('/contact-submissions', [ContactSubmissionController::class, 'store']);
