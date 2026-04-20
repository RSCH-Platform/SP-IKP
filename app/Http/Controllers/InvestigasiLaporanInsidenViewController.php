<?php

namespace App\Http\Controllers;

use App\Models\LaporanInsiden;
use Illuminate\Support\Facades\Gate;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

class InvestigasiLaporanInsidenViewController extends Controller
{
    /**
     * Display investigasi laporan insiden untuk viewing/printing
     */
    public function show(string $nomor_laporan)
    {
        $laporan = $this->findByNomorLaporan($nomor_laporan);

        Gate::authorize('view', $laporan);

        return view('reports.investigasi-laporan-insiden', $this->buildViewData($laporan));
    }

    /**
     * Generate PDF investigasi laporan insiden
     */
    public function pdf(string $nomor_laporan)
    {
        $laporan = $this->findByNomorLaporan($nomor_laporan);

        Gate::authorize('view', $laporan);

        $data = $this->buildViewData($laporan);
        $filename = "Investigasi-Laporan-Insiden-{$laporan->nomor_laporan}-" . now()->format('Y-m-d-H-i-s') . ".pdf";

        return Pdf::view('reports.investigasi-laporan-insiden', $data)
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot
                    ->setChromePath(env('BROWSERSHOT_CHROME_PATH', '/home/juni/.cache/puppeteer/chrome/linux-147.0.7727.57/chrome-linux64/chrome'))
                    ->addChromiumArguments([
                        '--no-sandbox',
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                    ])
                    ->waitUntilNetworkIdle()
                    ->emulateMedia('print');
            })
            ->format('A4')
            ->landscape()
            ->margins(15, 15, 15, 15)
            ->inline($filename);
    }

    private function findByNomorLaporan(string $nomor_laporan): LaporanInsiden
    {
        return LaporanInsiden::where('nomor_laporan', $nomor_laporan)->firstOrFail();
    }

    /**
     * Build common view data for investigasi report.
     */
    private function buildViewData(LaporanInsiden $laporan): array
    {
        $laporan->load([
            'investigationData' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'investigationData.creator',
            'timelineEvents' => function ($query) {
                $query->orderBy('event_datetime', 'asc');
            },
            'timelineEvents.entries.category',
            'unitKerjas',
            'reporter',
            'problems.whys',
            'problems.contributors.category',
            'problems.contributors.component',
            'problems.contributors.subComponent',
            'problems.recommendations',
            'problems.actions',
        ]);

        return [
            'laporan' => $laporan,
            'investigationDataGrouped' => $this->groupInvestigationData($laporan->investigationData),
            'timelineData' => $this->prepareTimelineData($laporan->timelineEvents),
        ];
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
