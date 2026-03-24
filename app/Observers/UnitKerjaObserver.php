<?php

namespace App\Observers;

use App\Models\UnitKerja;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class UnitKerjaObserver
{
    public function created(UnitKerja $unitKerja): void
    {
        $unitSlug = Str::slug($unitKerja->unit_name ?: 'unit-kerja-tidak-diketahui', '_');

        // Root folder unit kerja
        $rootFolder = Folder::firstOrCreate(
            [
                'name' => $unitKerja->unit_name,
                'collection' => 'unit_kerja_'. $unitSlug,
            ],
            [
                'description' => "Root folder unit kerja {$unitKerja->unit_name}",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
            ]
        );

        // Child folder laporan insiden di bawah root folder unit kerja.
        $childFolder = Folder::firstOrCreate(
            [
                'name' => 'Laporan Insiden',
                'collection' => 'laporan_insiden',
                'parent_id' => $rootFolder->id,
            ],
            [
                'description' => "Folder Laporan Insiden untuk unit kerja {$unitKerja->unit_name}",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => Auth::id(),
                'user_type' => Auth::check() ? get_class(Auth::user()) : null,
            ]
        );

        $diskPath = "{$unitSlug}/Laporan Insiden";
        $diskCreated = false;
        if (! Storage::disk('public')->exists($diskPath)) {
            $diskCreated = Storage::disk('public')->makeDirectory($diskPath);
        }

        Notification::make()
            ->title('Folder unit kerja dibuat')
            ->success()
            ->body("Folder unit kerja '{$unitKerja->unit_name}' dan subfolder Laporan Insiden berhasil dibuat.")
            ->send();

        logger()->info('UnitKerja Observer created folder', [
            'unit_id' => $unitKerja->id,
            'unit_name' => $unitKerja->unit_name,
            'root_folder_id' => $rootFolder->id,
            'child_folder_id' => $childFolder->id,
            'disk_path' => $diskPath,
            'disk_createwd' => $diskCreated,
        ]);

        Notification::make()
            ->title('Folder unit kerja dibuat')
            ->success()
            ->body("Folder unit kerja '{$unitKerja->unit_name}' dibuat di media manager")
            ->send();
    }
}
