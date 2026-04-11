<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Models\LaporanInsiden;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Gate;

class PreviewLaporanInsiden extends Page
{
    protected static string $resource = 'App\Filament\Resources\LaporanInsidens\LaporanInsidenResource';

    protected string $view = 'filament.resources.laporan-insidens.pages.preview-laporan-insiden';

    public LaporanInsiden $record;

    public function mount(LaporanInsiden $record): void
    {
        $this->record = $record;

        // Cek autorisasi
        Gate::authorize('view', $record);

        // Load relasi yang diperlukan
        $record->load([
            'timelineEvents' => function ($query) {
                $query->orderBy('event_datetime', 'asc');
            },
            'timelineEvents.entries.category',
            'unitKerja',
            'reporter',
            'verifier',
            'rejecter'
        ]);
    }

    public static function canAccess(array $parameters = []): bool
    {
        return true;
    }

    // public static function getTitle(): string
    // {
    //     return 'Preview Laporan Insiden';
    // }
}
