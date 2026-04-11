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

    /**
     * Prepare timeline events data for the component
     */
    public function getTimelineEventsForComponent()
    {
        return $this->prepareTimelineData($this->record->timelineEvents);
    }

    /**
     * Helper method to prepare timeline data
     */
    private function prepareTimelineData($events)
    {
        // Group events by date
        $eventsByDate = $events->groupBy(function ($event) {
            return $event->event_datetime?->format('Y-m-d');
        })->sortKeys();

        // Extract unique categories per date
        $dateCategories = [];
        foreach ($eventsByDate as $date => $dateEvents) {
            $dateCategories[$date] = $dateEvents->flatMap(fn($event) => $event->entries ?? [])
                ->pluck('category')
                ->unique('id')
                ->sortBy('sort_order')
                ->values();
        }

        return [
            'eventsByDate' => $eventsByDate,
            'dateCategories' => $dateCategories
        ];
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
