<?php

use App\Http\Controllers\TimelineEntryController;
use App\Http\Controllers\LaporanInsidenViewController;
use App\Http\Controllers\InvestigasiLaporanInsidenViewController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware(['auth'])->group(function () {
    Route::get('/timeline-entries', [TimelineEntryController::class, 'index']);

    // Laporan Insiden Routes
    Route::get('/laporan-insiden/{laporan}', [LaporanInsidenViewController::class, 'show'])
        ->name('laporan-insiden.show');
    // Route::get('/laporan-insiden-dummy', [LaporanInsidenViewController::class, 'dummy'])
    //     ->name('laporan-insiden.dummy');

    // Investigasi Laporan Insiden Routes
    Route::get('/investigasi-laporan-insiden/{laporan}', [InvestigasiLaporanInsidenViewController::class, 'show'])
        ->name('investigasi-laporan-insiden.show');
});
