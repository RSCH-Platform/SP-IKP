<?php

use App\Http\Controllers\TimelineEntryController;
use App\Http\Controllers\LaporanInsidenViewController;
use App\Http\Controllers\InvestigasiLaporanInsidenViewController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Root redirect - redirect to ikp-application path
Route::get('/', function () {
    return redirect('/ikp-application');
});

// Logout route - handles both SSO and local logout
// This route is used to ensure proper global IAM logout chain
// Posted to by account widget and Filament logout action
Route::post('/logout', LogoutController::class)->name('logout');

// Also handle Filament's default logout route and delegate to our LogoutController
// Filament auto-generates this route, but we intercept it to use our custom logout logic
Route::post('/ikp-application/logout', LogoutController::class)->name('filament.ikp-application.auth.logout');

// Laporan Insiden Routes
Route::get('/laporan-insiden/nomor/{nomor_laporan}', [LaporanInsidenViewController::class, 'show'])
    ->where('nomor_laporan', '.+')
    ->name('laporan-insiden.show');
// PDF generation disabled
// Route::get('/laporan-insiden/pdf/{nomor_laporan}', [LaporanInsidenViewController::class, 'pdf'])
//     ->where('nomor_laporan', '.+')
//     ->name('laporan-insiden.pdf');
// Route::get('/laporan-insiden/pdf-view/{nomor_laporan}', [LaporanInsidenViewController::class, 'pdfView'])
//     ->where('nomor_laporan', '.+')
//     ->name('laporan-insiden.pdf.view');
// Route::get('/laporan-insiden/pdf-url/{nomor_laporan}', [LaporanInsidenViewController::class, 'pdfUrl'])
//     ->where('nomor_laporan', '.+')
//     ->name('laporan-insiden.pdf.url');

// Test route - dummy PDF from static HTML
// Route::get('/test/pdf-dummy', [LaporanInsidenViewController::class, 'testHello'])
//     ->name('test.pdf-dummy');

// Handle IAM callback - support both /callback dan /sso/callback paths
Route::name('iam.sso.callback.alternate')->group(function () {
    Route::get('/sso/callback', function (Illuminate\Http\Request $request) {
        // Redirect to actual callback route with token preserved
        return redirect('/callback?token=' . $request->query('token'), 301);
    });

    // Route::get('/timeline-entries', [TimelineEntryController::class, 'index']);
});

Route::get('/investigasi-laporan-insiden/{nomor_laporan}', [InvestigasiLaporanInsidenViewController::class, 'show'])
    ->where('nomor_laporan', '.*')
    ->name('investigasi-laporan-insiden.show');
// PDF generation disabled
// Route::get('/investigasi-laporan-insiden/pdf/{nomor_laporan}', [InvestigasiLaporanInsidenViewController::class, 'pdf'])
//     ->where('nomor_laporan', '.*')
//     ->name('investigasi-laporan-insiden.pdf');
