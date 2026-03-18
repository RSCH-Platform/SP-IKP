<?php

use App\Http\Controllers\TimelineEntryController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware(['auth'])->group(function () {
    Route::get('/timeline-entries', [TimelineEntryController::class, 'index']);
});
