<?php

use App\Models\LaporanInsiden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('can be created using factory', function () {
    $laporan = LaporanInsiden::factory()->create();
    
    expect($laporan)->toBeInstanceOf(LaporanInsiden::class)
        ->and($laporan->id)->not->toBeNull();
});

it('generates sequential nomor_laporan correctly', function () {
    // Simulasi bulan dan tahun saat ini
    $now = now();
    $month = $now->format('m');
    $year = $now->format('Y');

    // Buat laporan pertama
    $laporan1 = LaporanInsiden::factory()->create([
        'nomor_laporan' => LaporanInsiden::generateNomorLaporan()
    ]);
    expect($laporan1->nomor_laporan)->toBe("IKP/{$year}/{$month}/0001");

    // Buat laporan kedua
    $laporan2 = LaporanInsiden::factory()->create([
        'nomor_laporan' => LaporanInsiden::generateNomorLaporan()
    ]);
    expect($laporan2->nomor_laporan)->toBe("IKP/{$year}/{$month}/0002");
});

it('has fast performance when generating nomor_laporan for large dataset', function () {
    // Generate 1000 dummy reports in the database first to simulate large data
    LaporanInsiden::factory()->count(1000)->create([
        'tanggal_lapor' => now()->toDateString(),
    ]);

    // Test execution time for generating the 1001st report number
    $startTime = microtime(true);
    
    $nomor = LaporanInsiden::generateNomorLaporan();
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // in milliseconds

    // Log the performance explicitly as requested
    Log::info("Performance Test: generateNomorLaporan took {$executionTime} ms for 1000 records.");
    echo "\n🚀 Performance Log: generateNomorLaporan executed in " . round($executionTime, 2) . " ms.\n";

    // Expect the execution time to be less than 50ms (optimized query)
    expect($executionTime)->toBeLessThan(50);
});
