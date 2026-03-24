<?php

namespace App\Observers;

use App\Models\LaporanInsiden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class LaporanInsidenObserver
{
    public function created(LaporanInsiden $laporan): void
    {
        $unitName = $laporan->unitKerja?->unit_name
            ?? $laporan->unit_kerja
            ?? 'unit-kerja-tidak-diketahui';

        $unitSlug = Str::slug($unitName, '-');

        $month = optional($laporan->tanggal_lapor)->format('Y-m')
            ?? optional($laporan->tanggal_insiden)->format('Y-m')
            ?? date('Y-m');

        $reportTitle = $laporan->nomor_laporan . ': ' . Str::limit($laporan->deskripsi_kategori_insiden, 100);

        if (empty($reportTitle)) {
            $reportTitle = "laporan-{$laporan->id}";
        }

        $rootFolder = Folder::firstOrCreate(
            [
                'name' => $unitSlug,
                'collection' => 'unit_kerja',
            ],
            [
                'description' => "Root folder unit kerja {$unitName}",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => Auth::id(),
                'user_type' => Auth::check() ? get_class(Auth::user()) : null,
            ]
        );

        $laporanFolder = Folder::firstOrCreate(
            [
                'name' => 'Laporan Insiden',
                'collection' => 'laporan_insiden',
                'parent_id' => $rootFolder->id,
            ],
            [
                'description' => "Folder Laporan Insiden di unit {$unitName}",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
            ]
        );

        $reportFolder = Folder::firstOrCreate(
            [
                'name' => $reportTitle,
                'collection' => 'laporan_insiden',
                'parent_id' => $laporanFolder->id,
            ],
            [
                'description' => "Folder detail laporan {$laporan->nomor_laporan} ({$laporan->id})",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
                'model_type' => LaporanInsiden::class,
                'model_id' => $laporan->id,
            ]
        );

        if (! $reportFolder->wasRecentlyCreated) {
            $reportFolder->update([
                'model_type' => LaporanInsiden::class,
                'model_id' => $laporan->id,
            ]);
        }

        $diskPath = "{$unitSlug}/Laporan Insiden/{$month}/{$reportTitle}";
        $diskCreated = false;
        if (! Storage::disk('public')->exists($diskPath)) {
            $diskCreated = Storage::disk('public')->makeDirectory($diskPath);
        }

        Notification::make()
            ->title('Folder Laporan Insiden dibuat')
            ->success()
            ->body("Folder laporan '{$laporan->nomor_laporan}' dibuat di media manager.")
            ->send();

        logger()->info('LaporanInsiden folder create', [
            'laporan_id' => $laporan->id,
            'unit_id' => $laporan->unit_kerja_id,
            'path' => $diskPath,
            'disk_created' => $diskCreated,
            'root_folder_id' => $rootFolder->id,
            'laporan_folder_id' => $laporanFolder->id,
            'report_folder_id' => $reportFolder->id,
            'report_folder_was_new' => $reportFolder->wasRecentlyCreated,
        ]);
    }
}
