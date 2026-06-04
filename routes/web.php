<?php

use App\Http\Controllers\TimelineEntryController;
use App\Http\Controllers\LaporanInsidenViewController;
use App\Http\Controllers\InvestigasiLaporanInsidenViewController;
use App\Http\Controllers\CustomLaporanInsidenDashboardController;
use App\Http\Controllers\Auth\LogoutController;
use App\Models\ProblemAction;
use App\Models\LaporanInsiden;
use App\Exports\TimelineGridExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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

// Export Timeline route
Route::post('/export/timeline', function (Request $request) {
    abort_unless(Auth::check(), 403);

    $recordId = $request->input('record_id');
    $record = LaporanInsiden::findOrFail($recordId);

    // Check authorization - user must be able to view this record
    abort_unless(\Gate::allows('view', $record), 403);

    return (new TimelineGridExport($record))->download();
})->name('export.timeline');

Route::post('/ikp-application/problem-actions/{action}/status', function (Request $request, ProblemAction $action) {
    abort_unless(Auth::check(), 403);

    $validated = $request->validate([
        'status' => ['required', 'in:pending,ongoing,completed'],
    ]);

    $action->update([
        'status' => $validated['status'],
    ]);

    $report = $action->problem?->incident;
    $reportCompleted = false;

    if ($report) {
        $report->loadMissing('problems.actions');

        $reportCompleted = $report->problems->isNotEmpty() && $report->problems->every(function ($problem): bool {
            return $problem->actions->isNotEmpty() && $problem->actions->every(fn ($problemAction) => $problemAction->status === 'completed');
        });
    }

    return response()->json([
        'status' => $action->status,
        'report_completed' => $reportCompleted,
    ]);
})->name('problem-actions.status.update');

// Laporan Insiden Routes
Route::get('/laporan-insiden/nomor/{nomor_laporan}', [LaporanInsidenViewController::class, 'show'])
    ->where('nomor_laporan', '.+')
    ->name('laporan-insiden.show');

Route::get('/laporan-insiden/custom-dashboard', CustomLaporanInsidenDashboardController::class)
    ->name('laporan-insiden.custom-dashboard');
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

Route::get('/investigasi-file/{encryptedPath}', function (string $encryptedPath) {
    try {
        $path = decrypt($encryptedPath);
    } catch (\Throwable $e) {
        abort(404);
    }

    $availableDisks = ['local', 'private', 'public', 's3'];
    $diskName = null;

    foreach ($availableDisks as $candidate) {
        if (Storage::disk($candidate)->exists($path)) {
            $diskName = $candidate;
            break;
        }
    }

    if (! $diskName) {
        abort(404);
    }

    $disk = Storage::disk($diskName);
    $fullPath = $disk->path($path);
    $mimeType = $disk->mimeType($path);

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
    ]);
})->where('encryptedPath', '.*')->name('investigasi-file');

// PDF generation disabled
// Route::get('/investigasi-laporan-insiden/pdf/{nomor_laporan}', [InvestigasiLaporanInsidenViewController::class, 'pdf'])
//     ->where('nomor_laporan', '.*')
//     ->name('investigasi-laporan-insiden.pdf');
