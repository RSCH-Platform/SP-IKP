<?php

namespace App\Observers;

use App\Models\UnitKerja;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class UnitKerjaObserver
{
    public function created(UnitKerja $unitKerja): void
    {
        $unitSlug = Str::slug($unitKerja->unit_name ?: 'unit-kerja-tidak-diketahui', '-');
        $path = trim("{$unitSlug}/Laporan Insiden", '/');

        $diskCreated = false;
        if (! Storage::disk('public')->exists($path)) {
            $diskCreated = Storage::disk('public')->makeDirectory($path);
        }

        $folder = Folder::firstOrCreate(
            [
                'name' => $path,
                'collection' => 'unit_kerja_laporan_insiden',
            ],
            [
                'description' => "Folder penyimpanan laporan insiden untuk unit kerja {$unitKerja->unit_name}",
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
            ]
        );

        Notification::make()
            ->title('Folder unit kerja dibuat')
            ->success()
            ->body("Folder unit kerja '{$unitKerja->unit_name}' dibuat di media manager (path: {$path}).")
            ->send();

        logger()->info('UnitKerja Observer created folder', [
            'unit_id' => $unitKerja->id,
            'unit_name' => $unitKerja->unit_name,
            'path' => $path,
            'diskCreated' => $diskCreated,
            'folder_id' => $folder->id,
            'folder_created' => $folder->wasRecentlyCreated,
        ]);
    }
}
