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
}
