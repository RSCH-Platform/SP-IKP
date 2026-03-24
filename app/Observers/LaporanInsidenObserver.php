<?php

namespace App\Observers;

use App\Models\LaporanInsiden;
use Filament\Notifications\Notification;
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

        $reportSlug = $laporan->nomor_laporan
            ? Str::slug($laporan->nomor_laporan, '-')
            : "laporan-{$laporan->id}";

        $path = trim("{$unitSlug}/Laporan Insiden/{$month}/{$reportSlug}", '/');

        $diskCreated = false;
        if (! Storage::disk('public')->exists($path)) {
            $diskCreated = Storage::disk('public')->makeDirectory($path);
        }

        $folder = Folder::firstOrCreate(
            [
                'name' => $path,
                'collection' => 'laporan_insiden',
            ],
            [
                'description' => "Folder laporan insiden untuk unit {$unitName} ({$laporan->nomor_laporan})",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
                'model_type' => LaporanInsiden::class,
                'model_id' => $laporan->id,
            ]
        );

        if (! $folder->wasRecentlyCreated) {
            $folder->update([
                'model_type' => LaporanInsiden::class,
                'model_id' => $laporan->id,
            ]);
        }

        Notification::make()
            ->title('Folder Laporan Insiden dibuat')
            ->success()
            ->body("Folder '{$path}' berhasil ditambahkan ke database media manager.")
            ->send();

        logger()->info('LaporanInsiden folder create', [
            'laporan_id' => $laporan->id,
            'path' => $path,
            'disk_created' => $diskCreated,
            'folder_id' => $folder->id,
            'folder_is_new' => $folder->wasRecentlyCreated,
        ]);
    }
}
