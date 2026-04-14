<?php

namespace App\Http\Controllers;

use App\Models\LaporanInsiden;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LaporanInsidenViewController extends Controller
{
    /**
     * Display laporan insiden untuk viewing/printing
     */
    public function show(LaporanInsiden $laporan)
    {
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
     * Show dummy data untuk development/testing
     */
    public function dummy()
    {
        $dummyLaporan = $this->generateDummyData();

        return view('reports.laporan-insiden-dummy', [
            'laporan' => $dummyLaporan,
            'periodLabel' => now()->translatedFormat('d F Y'),
            'isDummy' => true,
        ]);
    }

    /**
     * Generate dummy data untuk preview
     */
    private function generateDummyData()
    {
        return (object) [
            'id' => 1,
            'nomor_laporan' => 'IKP/2026/04/0005',
            'status' => 'dilaporkan',
            'unit_kerja' => 'Ruang Lotus',
            'tanggal_lapor' => now(),
            'waktu_lapor' => now()->format('H:i'),

            // Data Pasien
            'nama_pasien' => 'Budi Santoso (Dev)',
            'nomor_rekam_medis' => 'RM-DEV-001',
            'ruangan' => 'Ruang Anggrek',
            'umur' => 45,
            'kelompok_umur' => '>30 tahun - 65 tahun',
            'jenis_kelamin' => 'Laki-laki',
            'penanggung_biaya' => 'BPJS',
            'tanggal_masuk_rs' => now()->subHours(24),

            // Rincian Kejadian
            'tanggal_insiden' => now(),
            'waktu_insiden' => '10:25',
            'jenis_insiden' => 'KPC',
            'lokasi_insiden' => 'Kamar Mandi Ruang Anggrek',
            'nama_pasien_insiden' => 'Pasien Jatuh',
            'kategori_insiden' => 'Pasien Jatuh',
            'dampak_insiden' => 'Tidak ada cedera',
            'deskripsi_kategori_insiden' => '[DEV] Insiden pasien jatuh di kamar mandi disebabkan oleh lantai yang licin dan tidak adanya pegangan. Faktor risiko pasien meliputi usia lanjut dan penggunaan obat antihipertensi.',
            'insiden_terjadi_pada' => 'Pasien',
            'pelapor_insiden_pasien' => 'Perawat',
            'insiden_menyangkut_pasien' => 'Pasien rawat inap',
            'spesialisasi_pasien' => 'Penyakit Dalam',
            'tindakan_dilakukan' => '[DEV] 1. Memberikan pertolongan pertama kepada pasien 2. Menghubungi dokter jaga 3. Melaporkan kepada kepala ruangan 4. Mengisi formulir laporan insiden 5. Memasang tanda lantai licin di kamar mandi',
            'tindakan_dilakukan_oleh' => 'Perawat',
            'kejadian_pernah_terjadi_sebelumnya' => 'Tidak',
            'grading_risiko' => 'KUNING',

            // Reporter info
            'reporter' => (object) [
                'id' => 65,
                'name' => 'ROSITA DEBBY IRAWAN, S.Kep., Ners',
                'email' => 'pelapor@example.com',
            ],

            // Unit Kerja
            'unitKerjas' => (object) [
                'id' => 34,
                'unit_name' => 'Ruang Lotus',
                'description' => 'Unit Perawatan Pasien Lantai 2',
            ],

            // Timeline dummy
            'timelineEvents' => collect([
                (object) [
                    'id' => 1,
                    'laporan_insiden_id' => 1,
                    'event_datetime' => now(),
                    'entries' => collect([
                        (object) [
                            'id' => 1,
                            'category_id' => 1,
                            'description' => 'Pasien jatuh saat menggunakan kamar mandi. Lantai licin karena basah.',
                        ],
                        (object) [
                            'id' => 2,
                            'category_id' => 2,
                            'description' => 'Faktor penyebab: Usia lanjut (45 th), penggunaan antihipertensi, tidak ada pegangan di kamar mandi.',
                        ],
                        (object) [
                            'id' => 3,
                            'category_id' => 3,
                            'description' => 'Hasil pemeriksaan: tidak ada cedera, vital sign stabil.',
                        ],
                    ]),
                ],
            ]),
        ];
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
