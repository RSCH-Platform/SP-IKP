<?php

namespace App\Observers;

use App\Models\ProblemAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class ProblemActionObserver
{
    public function creating(ProblemAction $action): void
    {
        $incident = $action->problem?->incident;

        $unitName = $incident->unitKerja?->unit_name
            ?? $incident->unit_kerja
            ?? 'unit-kerja-tidak-diketahui';

        $unitFolder = \Illuminate\Support\Str::slug($unitName, '-');

        $month = optional($incident?->tanggal_lapor)->format('Y-m')
            ?? optional($incident?->tanggal_insiden)->format('Y-m')
            ?? date('Y-m');

        $reportSegment = $incident?->nomor_laporan
            ? \Illuminate\Support\Str::slug($incident->nomor_laporan, '-')
            : ($incident?->id ? (string) $incident->id : 'laporan-tidak-tersedia');

        $path = trim("{$unitFolder}/Laporan Insiden/{$month}/{$reportSegment}", '/');

        $existsBefore = Storage::disk('public')->exists($path);
        $diskFolderCreated = false;

        if (! $existsBefore) {
            $diskFolderCreated = Storage::disk('public')->makeDirectory($path);
        }

        $folder = Folder::firstOrCreate(
            [
                'name' => $path,
                'collection' => 'action_evidence',
            ],
            [
                'description' => 'Folder untuk upload bukti action_evidence',
                'is_public' => true,
                'has_user_access' => true,
                'user_id' => auth()->id(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : null,
            ]
        );

        logger()->debug('ProblemAction creating event', [
            'event' => 'creating',
            'model' => ProblemAction::class,
            'payload' => $action->toArray(),
            'incident_id' => $action->problem_id,
            'target_path' => $path,
            'exists_before' => $existsBefore,
            'disk_folder_created' => $diskFolderCreated,
            'meta_folder_id' => $folder->id,
            'meta_folder_was_recent' => $folder->wasRecentlyCreated,
        ]);

        if ($folder->wasRecentlyCreated) {
            Notification::make()
                ->title('Folder media baru dibuat')
                ->success()
                ->body("Folder '{$path}' dibuat dan dicatat di database untuk collection action_evidence.")
                ->send();
        }
    }

    public function created(ProblemAction $action): void
    {
        $incident = $action->problem?->incident;

        $unitName = $incident->unitKerja?->unit_name
            ?? $incident->unit_kerja
            ?? 'unit-kerja-tidak-diketahui';

        $month = optional($incident?->tanggal_lapor)->format('Y-m')
            ?? optional($incident?->tanggal_insiden)->format('Y-m')
            ?? date('Y-m');

        $reportSegment = $incident?->nomor_laporan
            ? \Illuminate\Support\Str::slug($incident->nomor_laporan, '-')
            : ($incident?->id ? (string) $incident->id : 'laporan-tidak-tersedia');

        $path = trim("{$unitName}/Laporan Insiden/{$month}/{$reportSegment}", '/');

        $folder = Folder::where('name', $path)
            ->where('collection', 'action_evidence')
            ->first();

        if ($folder) {
            $folder->update([
                'model_type' => ProblemAction::class,
                'model_id' => $action->id,
            ]);

            Notification::make()
                ->title('Folder media terhubung')
                ->success()
                ->body("Folder '{$path}' ditautkan ke ProblemAction #{$action->id}.")
                ->send();
        }

        logger()->debug('ProblemAction created event', [
            'event' => 'created',
            'model' => ProblemAction::class,
            'payload' => $action->toArray(),
            'id' => $action->id,
            'folder_id' => $folder?->id,
        ]);
    }

    public function updating(ProblemAction $action): void
    {
        logger()->debug('ProblemAction updating event', [
            'event' => 'updating',
            'model' => ProblemAction::class,
            'payload' => $action->toArray(),
            'id' => $action->id,
        ]);
    }

    public function updated(ProblemAction $action): void
    {
        logger()->debug('ProblemAction updated event', [
            'event' => 'updated',
            'model' => ProblemAction::class,
            'payload' => $action->toArray(),
            'id' => $action->id,
        ]);
    }

    public function deleting(ProblemAction $action): void
    {
        logger()->debug('ProblemAction deleting event', [
            'event' => 'deleting',
            'model' => ProblemAction::class,
            'payload' => $action->toArray(),
            'id' => $action->id,
        ]);
    }

    public function deleted(ProblemAction $action): void
    {
        logger()->debug('ProblemAction deleted event', [
            'event' => 'deleted',
            'model' => ProblemAction::class,
            'payload' => $action->toArray(),
            'id' => $action->id,
        ]);
    }
}
