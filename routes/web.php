<?php

use App\Http\Controllers\TimelineEntryController;
use App\Http\Controllers\LaporanInsidenViewController;
use App\Http\Controllers\InvestigasiLaporanInsidenViewController;
use Illuminate\Support\Facades\Route;

// Root redirect - ke admin atau SSO login tergantung config
Route::get('/', function () {
    $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

    if ($ssoEnabled) {
        return redirect(config('iam.login_route', '/sso/login'));
    }

    return redirect('/admin');
});

// Handle IAM callback - support both /callback dan /sso/callback paths
Route::name('iam.sso.callback.alternate')->group(function () {
    Route::get('/sso/callback', function (Illuminate\Http\Request $request) {
        // Redirect to actual callback route with token preserved
        return redirect('/callback?token=' . $request->query('token'), 301);
    });

    // Route::get('/timeline-entries', [TimelineEntryController::class, 'index']);

    // // Laporan Insiden Routes
    // Route::get('/laporan-insiden/{laporan}', [LaporanInsidenViewController::class, 'show'])
    //     ->name('laporan-insiden.show');
    // // Route::get('/laporan-insiden-dummy', [LaporanInsidenViewController::class, 'dummy'])
    // //     ->name('laporan-insiden.dummy');

    // // Investigasi Laporan Insiden Routes
    // Route::get('/investigasi-laporan-insiden/{laporan}', [InvestigasiLaporanInsidenViewController::class, 'show'])
    //     ->name('investigasi-laporan-insiden.show');
});
