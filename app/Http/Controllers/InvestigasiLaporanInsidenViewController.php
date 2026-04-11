<?php

namespace App\Http\Controllers;

use App\Models\LaporanInsiden;
use Illuminate\Support\Facades\Gate;

class InvestigasiLaporanInsidenViewController extends Controller
{
    /**
     * Display investigasi laporan insiden untuk viewing/printing
     */
    public function show(LaporanInsiden $laporan)
    {
        // Cek autorisasi - hanya yang memiliki akses investigasi
        Gate::authorize('view', $laporan);

        // Load relasi yang diperlukan
        $laporan->load([
            'investigationData' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'investigationData.creator',
            'timelineEvents' => function ($query) {
                $query->orderBy('event_datetime', 'asc');
            },
            'timelineEvents.entries.category',
            'unitKerja',
            'reporter',
        ]);

        // Format data untuk view
        $data = [
            'laporan' => $laporan,
            'investigationDataGrouped' => $this->groupInvestigationData($laporan->investigationData),
            'timelineData' => $this->prepareTimelineData($laporan->timelineEvents),
        ];

        return view('reports.investigasi-laporan-insiden', $data);
    }

    /**
     * Group investigation data by category
     */
    private function groupInvestigationData($investigationData)
    {
        $grouped = [];

        foreach ($investigationData as $data) {
            $kategori = $data->kategori;
            if (!isset($grouped[$kategori])) {
                $grouped[$kategori] = [
                    'label' => $data->getKategoriLabel(),
                    'items' => []
                ];
            }
            $grouped[$kategori]['items'][] = $data;
        }

        return $grouped;
    }

    /**
     * Helper method to prepare timeline data
     */
    private function prepareTimelineData($events)
    {
        if (!$events || $events->isEmpty()) {
            return [
                'eventsByDate' => collect(),
                'dateCategories' => []
            ];
        }

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
}
