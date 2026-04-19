<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Font sizes in pixels */
        .text-xs {
            font-size: 10px !important;
            line-height: 1.4;
        }

        .text-sm {
            font-size: 12px !important;
            line-height: 1.4;
        }

        .text-base {
            font-size: 16px !important;
            line-height: 1.5;
        }

        .text-lg {
            font-size: 18px !important;
            line-height: 1.5;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        @media print and (orientation: landscape) {
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            * {
                margin: 0;
                padding: 0;
            }

            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .portrait-mode {
                width: 210mm;
                height: 297mm;
            }

            .landscape-mode {
                width: 297mm;
                height: 210mm;
            }

            .break-inside-avoid {
                break-inside: avoid;
            }
        }

        /* Screen mode */
        .portrait-mode {
            max-width: 210mm;
            margin: 0 auto;
            padding: 1rem;
            background: white;
        }

        .landscape-mode {
            max-width: 297mm;
            margin: 0 auto;
            padding: 1rem;
            background: white;
        }
    </style>
</head>

<body class="bg-slate-300 text-slate-800 font-sans leading-relaxed">
    <!-- Control Panel -->
    <div class="no-print sticky top-0 z-50 bg-white border-b border-slate-300 shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Format Info -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-slate-700">Format:</label>
                    <span class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg bg-slate-50 text-slate-700">Portrait A4</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Print Button -->
                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak
                </button>

                <!-- Back Button -->
                <button onclick="window.history.back()" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </button>
            </div>
        </div>
    </div>

    <!-- Document Container -->
    <div class="portrait-mode">
        <!-- DEBUG SECTION -->
        <!-- <div class="no-print mb-6 bg-red-50 border-2 border-red-400 rounded-lg p-4">
            <p class="text-sm font-bold text-red-700 mb-3">🔴 DEBUG - Semua Data dari Controller:</p>
            <pre class="text-xs bg-white p-3 rounded border border-red-200 overflow-x-auto text-slate-800"><code>{{ json_encode($laporan->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
        </div> -->

        <!-- Header Component -->
        <x-pelaporan-insiden-header
            title="LAPORAN INSIDEN"
            :documentNumber="$laporan->nomor_laporan"
            :additionalInfo="[
                ['label' => 'Tanggal Lapor', 'value' => $laporan->tanggal_lapor?->translatedFormat('d F Y') ?? '-'],
                ['label' => 'Unit Kerja', 'value' => $laporan->unitKerja?->unit_name ?? '-'],
                ['label' => 'Status', 'value' => ucfirst($laporan->status ?? 'Draft')]
            ]" />

        <!-- Info Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 bg-white border border-slate-300 p-1 items-center text-left">
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">No. Laporan</p>
                <p class="text-xs text-slate-800">{{ $laporan->nomor_laporan ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Unit Kerja</p>
                <p class="text-xs text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Status</p>
                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ ucfirst($laporan->status ?? 'Draft') }}</span>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Tanggal Cetak</p>
                <p class="text-xs text-slate-800">{{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- SECTION A: DATA PASIEN -->
        <div class="break-inside-avoid mb-6">
            <x-section-header title="BAGIAN A: Data Pasien" />
            <div class="bg-white border border-slate-300 p-2 space-y-3">
                <!-- Row 1: Nama Pasien & No Rekam Medis -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">Nama Pasien</p>
                        <p class="text-xs text-slate-800 font-medium">{{ $laporan->nama_pasien ?? '-' }}</p>
                    </div>
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">No. Rekam Medis</p>
                        <p class="text-xs text-slate-800 font-medium">{{ $laporan->nomor_rekam_medis ?? '-' }}</p>
                    </div>
                </div>

                <!-- Row 2: Ruangan -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">Ruangan</p>
                    <p class="text-xs text-slate-800">{{ $laporan->ruangan ?? '-' }}</p>
                </div>

                <!-- Row 3: Umur & Kelompok Umur -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <div class="border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">Umur</p>
                        <p class="text-xs text-slate-800 font-medium">{{ $laporan->umur ?? '-' }} tahun</p>
                    </div>
                    <div class="col-span-2 border border-slate-200 p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-2">Kelompok Umur</p>
                        <div class="space-y-1">
                            @php
                            $ageGroups = [
                            '0-1 bulan' => '0-1 bulan',
                            '> 1 bulan - 1 tahun' => '> 1 bulan - 1 tahun',
                            '> 1 tahun - 5 tahun' => '> 1 tahun - 5 tahun',
                            '> 5 tahun - 15 tahun' => '> 5 tahun - 15 tahun',
                            '> 15 tahun - 30 tahun' => '> 15 tahun - 30 tahun',
                            '>30 tahun - 65 tahun' => '>30 tahun - 65 tahun',
                            '> 65 tahun' => '> 65 tahun'
                            ];
                            $selectedAge = trim($laporan->kelompok_umur ?? '');
                            @endphp
                            <div class="grid grid-cols-2 gap-1">
                                @foreach($ageGroups as $key => $label)
                                <x-checkbox-display :checked="trim($key) === $selectedAge" :label="$label" disabled />
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Jenis Kelamin -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-2">Jenis Kelamin</p>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                        $selectedGender = trim($laporan->jenis_kelamin ?? '');
                        @endphp
                        <x-checkbox-display :checked="trim('Laki-laki') === $selectedGender" label="Laki-laki" disabled />
                        <x-checkbox-display :checked="trim('Perempuan') === $selectedGender" label="Perempuan" disabled />
                    </div>
                </div>

                <!-- Row 5: Penanggung Biaya -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-2">Penanggung Biaya Pasien</p>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                        $selectedPayment = trim($laporan->penanggung_biaya ?? '');
                        @endphp
                        <x-checkbox-display :checked="trim('Pribadi') === $selectedPayment" label="Pribadi" disabled />
                        <x-checkbox-display :checked="trim('Asuransi Swasta') === $selectedPayment" label="Asuransi Swasta" disabled />
                        <x-checkbox-display :checked="trim('BPJS') === $selectedPayment" label="BPJS" disabled />
                        <x-checkbox-display :checked="trim('Lainnya') === $selectedPayment" label="Lainnya" disabled />
                    </div>
                </div>

                <!-- Row 6: Tanggal Masuk RS -->
                <div class="border border-slate-200 p-2">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">Tanggal Masuk RS</p>
                    <p class="text-xs text-slate-800">
                        @if($laporan->tanggal_masuk_rs)
                        Pada tanggal {{ $laporan->tanggal_masuk_rs->translatedFormat('d F Y') }} di jam {{ $laporan->tanggal_masuk_rs->translatedFormat('H:i') }} WIB
                        @else
                        -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- SECTION B: RINCIAN KEJADIAN -->
        <div class="break-inside-avoid mb-6">
            <x-section-header title="BAGIAN B: Rincian Kejadian" />
            <div class="bg-white border border-slate-300 p-2 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <x-data-row label="Tanggal Insiden" :value="$laporan->tanggal_insiden?->translatedFormat('d F Y') ?? '-'" />
                    <x-data-row label="Waktu Insiden" :value="$laporan->waktu_insiden ?? '-'" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <x-data-row label="Jenis Insiden" :value="$laporan->jenis_insiden ?? '-'" />
                    <x-data-row label="Lokasi Insiden" :value="$laporan->lokasi_insiden ?? '-'" />
                </div>
                <x-long-text-display label="Penjelasan Insiden" :text="$laporan->deskripsi_kategori_insiden ?? '-'" />
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <x-data-row label="Kategori Insiden" :value="$laporan->kategori_insiden ?? '-'" />
                    <x-data-row label="Orang yang Pelapor" :value="$laporan->pelapor_insiden_pasien ?? '-'" />
                    <x-data-row label="Insiden Menyangkut" :value="$laporan->insiden_menyangkut_pasien ?? '-'" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <x-data-row label="Spesialisasi Pasien" :value="$laporan->spesialisasi_pasien ?? '-'" />
                    <x-data-row label="Dampak Insiden" :value="$laporan->dampak_insiden ?? '-'" />
                </div>
                <div class="border border-slate-200 p-2 col-span-full">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-2">Kejadian Sebelumnya</p>
                    <div class="grid grid-cols-2 gap-2">
                        @php
                        $kejadianSebelumnya = trim($laporan->kejadian_pernah_terjadi_sebelumnya ?? '');
                        @endphp
                        <x-checkbox-display :checked="$kejadianSebelumnya === 'Ya'" label="Ya" disabled />
                        <x-checkbox-display :checked="$kejadianSebelumnya === 'Tidak'" label="Tidak" disabled />
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION C: TINDAKAN YANG DILAKUKAN -->
        <div class="break-inside-avoid mb-6">
            <x-section-header title="BAGIAN C: Tindakan Setelah Kejadian" />
            <div class="bg-white border border-slate-300 p-2 space-y-3">
                <x-long-text-display label="Tindakan yang Dilakukan Segera Setelah Kejadian" :text="$laporan->tindakan_dilakukan ?? '-'" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <x-data-row label="Tindakan Dilakukan Oleh" :value="$laporan->tindakan_dilakukan_oleh ?? '-'" />
                    <x-data-row label="Unit Penyebab" :value="$laporan->unit_kerja ?? '-'" />
                </div>
            </div>
        </div>

        <!-- SECTION D: KRONOLOGI TIMELINE -->
        <div class="break-inside-avoid mb-6">
            <x-section-header title="BAGIAN D: Kronologi Timeline" />
            <div class="bg-white border border-slate-300 p-2">
                <div class="space-y-6">
                    @forelse($timelineData['eventsByDate'] as $date => $dateEvents)
                    <!-- Date Header Section -->
                    <div>
                        <div class="bg-slate-100 px-4 py-3 border-t-4 border-b-4 border-slate-400 mb-4">
                            <p class="text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                TANGGAL: {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)?->translatedFormat('l, d F Y') ?? 'Tanggal tidak tersedia' }}
                            </p>
                        </div>

                        <!-- Timeline Table -->
                        @if($dateEvents->flatMap(fn($event) => $event->entries ?? [])->count() > 0)
                        @php
                        $categories = $timelineData['allCategories'];
                        @endphp
                        <div class="border border-slate-300 rounded-lg w-full">
                            <table class="w-full text-xs table-fixed border-collapse">
                                <!-- Table Header -->
                                <thead>
                                    <tr class="bg-slate-200 border-b-2 border-slate-400">
                                        <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wide border-r border-slate-300 text-xs" style="width: 15%;">WAKTU</th>
                                        @foreach($categories as $category)
                                        <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wide border-r border-slate-300 text-xs" style="width: {{ 85 / count($categories) }}%;">
                                            {{ $category->name ?? 'Kategori' }}
                                        </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <!-- Table Body -->
                                <tbody>
                                    @php
                                    $timeGroups = $dateEvents->groupBy(fn($event) => \Carbon\Carbon::parse($event->event_datetime)->format('H:i'));
                                    @endphp

                                    @foreach($timeGroups as $time => $eventsAtSameTime)
                                    @php
                                    $mergedEntries = collect($eventsAtSameTime)
                                    ->flatMap(fn($event) => $event->entries ?? [])
                                    ->groupBy('category_id');
                                    @endphp
                                    <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                        <!-- Waktu -->
                                        <td class="px-3 py-2 text-slate-700 font-medium border-r border-slate-200 whitespace-nowrap text-xs" style="width: 15%;">
                                            {{ $time }}
                                        </td>

                                        <!-- Category Data -->
                                        @foreach($categories as $category)
                                        @php
                                        $entries = $mergedEntries[$category->id] ?? collect();
                                        $descriptions = collect($entries)->pluck('description')->filter()->all();
                                        @endphp
                                        <td class="px-3 py-2 text-slate-700 border-r border-slate-200 text-xs" style="width: {{ 85 / count($categories) }}%;">
                                            @if(count($descriptions) > 0)
                                            <div class="space-y-1">
                                                @foreach($descriptions as $description)
                                                <p class="text-xs leading-relaxed">{{ $description }}</p>
                                                @endforeach
                                            </div>
                                            @else
                                            <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-6 bg-slate-50 rounded-lg border border-slate-200">
                            <p class="text-xs text-slate-500 italic">Tidak ada entri untuk tanggal ini</p>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-xs text-slate-500 italic">Belum ada kronologi timeline yang tersedia</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- SECTION E: GRADING RISIKO -->
        <div class="break-inside-avoid mb-6">
            <x-section-header title="BAGIAN E: Grading Risiko" />
            <div class="bg-white border border-slate-300 p-2">
                @if($laporan->status === 'dilaporkan')
                <!-- Editable version for dilaporkan status -->
                <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan ?? 'Tidak ada justifikasi'" :editable="true" />
                @else
                <!-- Read-only version for revisi_unit status -->
                <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan ?? 'Tidak ada justifikasi'" :disabled="true" />
                @endif
            </div>
        </div>

        <!-- Footer Report Component -->
        <x-footer-report
            :createdByName="$laporan->reporter?->name ?? $laporan->nama_pelapor ?? '-'"
            :createdByNip="$laporan->reporter?->nip ?? '-'"
            :createdByPosition="'Pelapor'"
            :unitId="$laporan->unit_kerja_id"
            :reportDate="$laporan->tanggal_lapor?->translatedFormat('d F Y')"
            :receivedDate="$laporan->verified_at?->translatedFormat('d F Y')"
            :notes="[
                'Dokumen ini bersifat RAHASIA dan tidak boleh difotocopy',
                'Laporan harus diserahkan maksimal 2 x 24 jam setelah kejadian',
                'Semua field harus diisi dengan lengkap dan jelas',
                'Grading risiko harus ditentukan oleh kepala unit kerja'
            ]" />

    </div>
</body>

</html>