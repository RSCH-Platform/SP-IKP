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

<body class="bg-slate-300 text-slate-800 font-sans leading-relaxed">
    <div class="max-w-5xl mx-auto px-4 py-4 bg-white">
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
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 bg-white border border-slate-300 p-1 items-center text-left">
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">No. Laporan</p>
                <p class="text-xs text-slate-800">{{ $laporan->nomor_laporan ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Unit Kerja</p>
                <p class="text-xs text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Status</p>
                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ ucfirst($laporan->status ?? 'Draft') }}</span>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Tanggal Cetak</p>
                <p class="text-xs text-slate-800">{{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- SECTION A: DATA PASIEN -->
        <div class="break-inside-avoid mb-6">
            <h2 class="text-sm font-semibold text-slate-800 pb-1 mb-2 border-b border-slate-300">Data Pasien</h2>
            <div class="bg-white border border-slate-300 p-2 space-y-3">
                <!-- Row 1: Nama Pasien & No Rekam Medis -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Nama Pasien</p>
                        <p class="text-xs text-slate-800 font-medium">{{ $laporan->nama_pasien ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">No. Rekam Medis</p>
                        <p class="text-xs text-slate-800 font-medium">{{ $laporan->nomor_rekam_medis ?? '-' }}</p>
                    </div>
                </div>

                <!-- Row 2: Ruangan -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Ruangan</p>
                    <p class="text-xs text-slate-800">{{ $laporan->ruangan ?? '-' }}</p>
                </div>

                <!-- Row 3: Umur & Kelompok Umur -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Umur</p>
                        <p class="text-xs text-slate-800 font-medium">{{ $laporan->umur ?? '-' }} tahun</p>
                    </div>
                    <div class="col-span-2 border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Kelompok Umur</p>
                        <div class="space-y-1">
                            @php
                            $ageGroups = [
                            '0-1 bulan' => '0-1 bulan',
                            '1 bulan - 1 tahun' => '> 1 bulan - 1 tahun',
                            '1-5 tahun' => '> 1 tahun - 5 tahun',
                            '5-15 tahun' => '> 5 tahun - 15 tahun',
                            '15-30 tahun' => '> 15 tahun - 30 tahun',
                            '30-65 tahun' => '> 30 tahun - 65 tahun',
                            '> 65 tahun' => '> 65 tahun'
                            ];
                            $selectedAge = $laporan->kelompok_umur ?? '';
                            @endphp
                            <div class="grid grid-cols-2 gap-1">
                                @foreach($ageGroups as $key => $label)
                                <label class="flex items-center gap-1 cursor-default">
                                    <input type="checkbox" disabled {{ $selectedAge === $key ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                                    <span class="text-xs text-slate-700">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Jenis Kelamin -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Jenis Kelamin</p>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                        $selectedGender = $laporan->jenis_kelamin ?? '';
                        @endphp
                        <label class="flex items-center gap-1 cursor-default">
                            <input type="checkbox" disabled {{ $selectedGender === 'Laki-laki' ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                            <span class="text-xs text-slate-700">Laki-laki</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-default">
                            <input type="checkbox" disabled {{ $selectedGender === 'Perempuan' ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                            <span class="text-xs text-slate-700">Perempuan</span>
                        </label>
                    </div>
                </div>

                <!-- Row 5: Penanggung Biaya -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-2">Penanggung Biaya Pasien</p>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                        $selectedPayment = $laporan->penanggung_biaya ?? '';
                        @endphp
                        <label class="flex items-center gap-1 cursor-default">
                            <input type="checkbox" disabled {{ $selectedPayment === 'Pribadi' ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                            <span class="text-xs text-slate-700">Pribadi</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-default">
                            <input type="checkbox" disabled {{ $selectedPayment === 'Asuransi Swasta' ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                            <span class="text-xs text-slate-700">Asuransi Swasta</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-default">
                            <input type="checkbox" disabled {{ $selectedPayment === 'BPJS' ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                            <span class="text-xs text-slate-700">BPJS</span>
                        </label>
                        <label class="flex items-center gap-1 cursor-default">
                            <input type="checkbox" disabled {{ $selectedPayment === 'Lainnya' ? 'checked' : '' }} class="w-3 h-3 rounded-sm cursor-default pointer-events-none">
                            <span class="text-xs text-slate-700">Lainnya</span>
                        </label>
                    </div>
                </div>

                <!-- Row 6: Tanggal Masuk RS -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Tanggal Masuk RS</p>
                    <p class="text-xs text-slate-800">{{ $laporan->tanggal_masuk_rs?->translatedFormat('d F Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- SECTION B: RINCIAN KEJADIAN -->
        <div class="break-inside-avoid mb-6">
            <h2 class="text-sm font-semibold text-slate-800 pb-1 mb-2 border-b border-slate-300">Rincian Kejadian</h2>
            <div class="bg-white border border-slate-300 p-1 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-center text-left">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Tanggal Insiden</p>
                        <p class="text-xs text-slate-800">{{ $laporan->tanggal_insiden?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Waktu Insiden</p>
                        <p class="text-xs text-slate-800">{{ $laporan->waktu_insiden ?? '-' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-center text-left">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Jenis Insiden</p>
                        <p class="text-xs text-slate-800">{{ $laporan->jenis_insiden ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Lokasi Insiden</p>
                        <p class="text-xs text-slate-800">{{ $laporan->lokasi_insiden ?? '-' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Penjelasan Insiden</p>
                    <div class="text-xs text-slate-800 whitespace-pre-wrap bg-slate-50 p-2 rounded">{{ $laporan->deskripsi_kategori_insiden ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center text-left">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Kategori Insiden</p>
                        <p class="text-xs text-slate-800">{{ $laporan->kategori_insiden ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Orang yang Pelapor</p>
                        <p class="text-xs text-slate-800">{{ $laporan->pelapor_insiden_pasien ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Insiden Menyangkut</p>
                        <p class="text-xs text-slate-800">{{ $laporan->insiden_menyangkut_pasien ?? '-' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-center text-left">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Spesialisasi Pasien</p>
                        <p class="text-xs text-slate-800">{{ $laporan->spesialisasi_pasien ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Dampak Insiden</p>
                        <p class="text-xs text-slate-800">{{ $laporan->dampak_insiden ?? '-' }}</p>
                    </div>
                </div>
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Kejadian Sebelumnya</p>
                    <p class="text-xs text-slate-800">{{ $laporan->kejadian_pernah_terjadi_sebelumnya ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- SECTION C: KRONOLOGI TIMELINE -->
        <div class="break-inside-avoid mb-6">
            <h2 class="text-sm font-semibold text-slate-800 pb-1 mb-2 border-b border-slate-300">Kronologi Timeline</h2>
            <div class="bg-white border border-slate-300 p-1">
                @if($laporan->timelineEvents && $laporan->timelineEvents->count() > 0)
                <div class="space-y-2">
                    @foreach($laporan->timelineEvents as $event)
                    <div class="border-l-2 border-slate-300 pl-3">
                        <p class="text-xs font-semibold text-slate-800 mb-1">
                            {{ $event->event_datetime?->translatedFormat('d F Y H:i') ?? 'Waktu tidak tersedia' }}
                        </p>
                        @if($event->entries && $event->entries->count() > 0)
                        <div class="space-y-1">
                            @foreach($event->entries as $entry)
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">
                                    Kategori
                                </p>
                                <p class="text-xs text-slate-800 whitespace-pre-wrap">{{ $entry->description ?? '-' }}</p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-500">Tidak ada entri untuk timeline ini</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-slate-500 py-2 text-center">Data timeline tidak tersedia</p>
                @endif
            </div>
        </div>

        <!-- SECTION D: TINDAKAN YANG DILAKUKAN -->
        <div class="break-inside-avoid mb-6">
            <h2 class="text-sm font-semibold text-slate-800 pb-1 mb-2 border-b border-slate-300">Tindakan yang Dilakukan</h2>
            <div class="bg-white border border-slate-300 p-1 space-y-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Tindakan Segera Setelah Kejadian</p>
                    <div class="text-xs text-slate-800 whitespace-pre-wrap bg-slate-50 p-2 rounded">{{ $laporan->tindakan_dilakukan ?? '-' }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 items-center text-left">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Tindakan Dilakukan Oleh</p>
                        <p class="text-xs text-slate-800">{{ $laporan->tindakan_dilakukan_oleh ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-0.5">Unit Penyebab</p>
                        <p class="text-xs text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
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
        <div class="no-print grid grid-cols-2 gap-2 mb-4 items-center">
            <button onclick="window.history.back()" class="px-4 py-2 rounded border border-slate-300 text-slate-700 text-xs font-medium hover:bg-slate-50">
                Kembali
            </button>
            <button onclick="window.print()" class="px-4 py-2 rounded bg-blue-600 text-white text-xs font-medium hover:bg-blue-700">
                Cetak
            </button>
        </div>
    </div>
</body>

</html>