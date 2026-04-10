<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .break-inside-avoid {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 font-sans leading-relaxed">
    <div class="max-w-5xl mx-auto px-6 py-8">
        <!-- Dev Tag -->
        <div class="mb-8 bg-amber-50 border border-amber-200 rounded-lg p-4 ring-1 ring-amber-100">
            <p class="text-xs font-semibold text-amber-700">DUMMY DATA - UNTUK TESTING DEVELOPMENT</p>
            <p class="text-xs text-amber-600 mt-1">Ini adalah data uji untuk keperluan pengembangan sistem</p>
        </div>

        <!-- Header Component -->
        <x-pelaporan-insiden-header
            title="LAPORAN INSIDEN"
            :documentNumber="$laporan->nomor_laporan"
            :additionalInfo="[
                ['label' => 'Tanggal Lapor', 'value' => $laporan->tanggal_lapor?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y')],
                ['label' => 'Unit Kerja', 'value' => $laporan->unit_kerja ?? 'Ruang Lotus'],
                ['label' => 'Status', 'value' => ucfirst($laporan->status ?? 'Draft')]
            ]" />

        <!-- Info Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 bg-white rounded-lg shadow-sm ring-1 ring-slate-200/60 p-6">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">No. Laporan</p>
                <p class="text-sm text-slate-800">{{ $laporan->nomor_laporan ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Unit Kerja</p>
                <p class="text-sm text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Status</p>
                <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">{{ ucfirst($laporan->status ?? 'Draft') }}</span>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Tanggal Cetak</p>
                <p class="text-sm text-slate-800">{{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- SECTION A: DATA PASIEN -->
        <div class="mb-4">
            <h2 class="text-xs font-bold border-b pb-1 mb-2">A. DATA PASIEN</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-1 text-xs leading-tight">
                <div><span class="text-slate-500">Nama</span><br><span class="font-medium">{{ $laporan->nama_pasien ?? '-' }}</span></div>
                <div><span class="text-slate-500">No RM</span><br><span class="font-medium">{{ $laporan->nomor_rekam_medis ?? '-' }}</span></div>
                <div><span class="text-slate-500">Umur</span><br><span class="font-medium">{{ $laporan->umur ?? '-' }} th</span></div>
                <div><span class="text-slate-500">JK</span><br><span class="font-medium">{{ $laporan->jenis_kelamin ?? '-' }}</span></div>

                <div><span class="text-slate-500">Ruangan</span><br><span class="font-medium">{{ $laporan->ruangan ?? '-' }}</span></div>
                <div><span class="text-slate-500">Kelompok Umur</span><br><span class="font-medium">{{ $laporan->kelompok_umur ?? '-' }}</span></div>
                <div><span class="text-slate-500">Penjamin</span><br><span class="font-medium">{{ $laporan->penanggung_biaya ?? '-' }}</span></div>
                <div><span class="text-slate-500">Masuk RS</span><br><span class="font-medium">{{ $laporan->tanggal_masuk_rs?->format('d/m/Y H:i') ?? '-' }}</span></div>
            </div>
        </div>
        
        <!-- SECTION B: RINCIAN KEJADIAN -->
        <div class="break-inside-avoid mb-8">
            <h2 class="text-base font-semibold text-slate-800 pb-2 mb-4 border-b border-slate-200">Rincian Kejadian</h2>
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-200/60 p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Tanggal Insiden</p>
                        <p class="text-sm text-slate-800">{{ $laporan->tanggal_insiden?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Waktu Insiden</p>
                        <p class="text-sm text-slate-800">{{ $laporan->waktu_insiden ?? '-' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Jenis Insiden</p>
                        <p class="text-sm text-slate-800">{{ $laporan->jenis_insiden ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Lokasi Insiden</p>
                        <p class="text-sm text-slate-800">{{ $laporan->lokasi_insiden ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Penjelasan Insiden</p>
                    <div class="text-sm text-slate-800 whitespace-pre-wrap bg-slate-50 p-4 rounded">{{ $laporan->deskripsi_kategori_insiden ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Kategori Insiden</p>
                        <p class="text-sm text-slate-800">{{ $laporan->kategori_insiden ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Orang Pelapor</p>
                        <p class="text-sm text-slate-800">{{ $laporan->pelapor_insiden_pasien ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Insiden Menyangkut</p>
                        <p class="text-sm text-slate-800">{{ $laporan->insiden_menyangkut_pasien ?? '-' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Spesialisasi Pasien</p>
                        <p class="text-sm text-slate-800">{{ $laporan->spesialisasi_pasien ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Dampak Insiden</p>
                        <p class="text-sm text-slate-800">{{ $laporan->dampak_insiden ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Kejadian Sebelumnya</p>
                    <p class="text-sm text-slate-800">{{ $laporan->kejadian_pernah_terjadi_sebelumnya ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- SECTION C: KRONOLOGI TIMELINE -->
        <div class="break-inside-avoid mb-8">
            <h2 class="text-base font-semibold text-slate-800 pb-2 mb-4 border-b border-slate-200">Kronologi Timeline</h2>
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-200/60 p-6">
                @if($laporan->timelineEvents && $laporan->timelineEvents->count() > 0)
                <div class="space-y-6">
                    @foreach($laporan->timelineEvents as $event)
                    <div class="border-l-2 border-slate-300 pl-6">
                        <p class="text-sm font-semibold text-slate-800 mb-3">
                            {{ $event->event_datetime?->translatedFormat('d F Y H:i') ?? 'Waktu tidak tersedia' }}
                        </p>
                        @if($event->entries && $event->entries->count() > 0)
                        <div class="space-y-4">
                            @foreach($event->entries as $entry)
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">
                                    Kategori
                                </p>
                                <p class="text-sm text-slate-800 whitespace-pre-wrap">{{ $entry->description ?? '-' }}</p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-slate-500">Tidak ada entri untuk timeline ini</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500 py-6 text-center">Data timeline tidak tersedia</p>
                @endif
            </div>
        </div>

        <!-- SECTION D: TINDAKAN YANG DILAKUKAN -->
        <div class="break-inside-avoid mb-8">
            <h2 class="text-base font-semibold text-slate-800 pb-2 mb-4 border-b border-slate-200">Tindakan yang Dilakukan</h2>
            <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-200/60 p-6 space-y-6">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Tindakan Segera Setelah Kejadian</p>
                    <div class="text-sm text-slate-800 whitespace-pre-wrap bg-slate-50 p-4 rounded">{{ $laporan->tindakan_dilakukan ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Tindakan Dilakukan Oleh</p>
                        <p class="text-sm text-slate-800">{{ $laporan->tindakan_dilakukan_oleh ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Unit Penyebab</p>
                        <p class="text-sm text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Report Component -->
        <x-footer-report
            createdByName="ROSITA DEBBY IRAWAN, S.Kep., Ners"
            createdByNip="198504101998022001"
            createdByPosition="Perawat Ruang Lotus"
            :unitId="1"
            :reportDate="now()->translatedFormat('d F Y')"
            :notes="[
                'Dokumen ini bersifat RAHASIA dan tidak boleh difotocopy',
                'Laporan harus diserahkan maksimal 2 x 24 jam setelah kejadian',
                'Semua field harus diisi dengan lengkap dan jelas',
                'Grading risiko harus ditentukan oleh kepala unit kerja'
            ]" />

        <!-- Print Controls -->
        <div class="no-print flex gap-3 mb-8">
            <button onclick="window.history.back()" class="px-4 py-2 rounded border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50">
                Kembali
            </button>
            <button onclick="window.print()" class="flex-1 px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                Cetak
            </button>
        </div>
    </div>
</body>

</html>