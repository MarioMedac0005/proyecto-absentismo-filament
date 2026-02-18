<?php

use App\Http\Controllers\SubjectHoursExportController;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome');
}); */

Route::get('/export/subject-hours', SubjectHoursExportController::class)
    ->middleware('auth')
    ->name('export.subject-hours');
