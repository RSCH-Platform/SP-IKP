<?php

namespace App\Http\Controllers;

use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;
use Illuminate\Support\Facades\Gate;

class LaporanInsidenViewController extends Controller
{
    /**
     * Display laporan insiden untuk viewing/printing
     */
    public function show(string $nomor_laporan)
    {
        $laporan = LaporanInsiden::where('nomor_laporan', $nomor_laporan)->firstOrFail();

        // Cek autorisasi - hanya pembuat, kepala unit, atau super admin yang bisa melihat
        Gate::authorize('view', $laporan);

        // Load relasi yang diperlukan
        $laporan->load([
            'timelineEvents' => function ($query) {
                $query->orderBy('event_datetime', 'asc');
            },
            'timelineEvents.entries.category',
            'unitKerjas',
            'reporter',
            'verifier',
            'rejecter'
        ]);

        // Optimalkan timeline events - hapus field yang tidak perlu
        if ($laporan->timelineEvents) {
            foreach ($laporan->timelineEvents as $event) {
                // Hanya pertahankan event_datetime dan entries
                $event->makeHidden([
                    'id',
                    'laporan_insiden_id',
                    'created_by',
                    'created_at',
                    'updated_at'
                ]);

                if ($event->entries) {
                    foreach ($event->entries as $entry) {
                        // Hapus field teknis dari entry
                        $entry->makeHidden([
                            'id',
                            'timeline_event_id',
                            'category_id',
                            'created_by',
                            'created_at',
                            'updated_at'
                        ]);

                        // Optimalkan category - hanya name dan sort_order
                        if ($entry->category) {
                            $entry->category->makeHidden([
                                'id',
                                'code',
                                'created_at',
                                'updated_at'
                            ]);
                        }
                    }
                }
            }
        }

        // Format data untuk view
        $data = [
            'laporan' => $laporan,
            'periodLabel' => $laporan->tanggal_lapor?->translatedFormat('d F Y') ?? 'N/A',
            'timelineData' => $this->prepareTimelineData($laporan->timelineEvents),
        ];

        return view('reports.laporan-insiden', $data);
    }

    /**
     * Helper method to prepare timeline data
     */
    private function prepareTimelineData($events)
    {
        if (!$events || $events->isEmpty()) {
            return [
                'eventsByDate' => collect(),
                'allCategories' => collect()
            ];
        }

        // Group events by date
        $eventsByDate = $events->groupBy(function ($event) {
            return $event->event_datetime?->format('Y-m-d');
        })->sortKeys();

        // Get ALL categories from database, sorted by sort_order
        $allCategories = TimelineCategory::orderBy('sort_order')->get();

        return [
            'eventsByDate' => $eventsByDate,
            'allCategories' => $allCategories
        ];
    }
}
