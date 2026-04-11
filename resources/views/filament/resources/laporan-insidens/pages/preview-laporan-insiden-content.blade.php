@php
$laporan = $record;
@endphp

<style>
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: white;
            font-size: 10pt;
        }

        .a4-landscape-container {
            width: 100%;
            padding: 10mm;
            box-sizing: border-box;
        }

        .break-inside-avoid {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .no-print {
            display: none !important;
        }
    }

    .a4-landscape-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        background-color: white;
        padding: 1rem;
        font-size: 14px;
    }

    /* Optimize for landscape: reduce vertical space, optimize horizontal */
    .a4-landscape-container .grid {
        column-gap: 0.75rem;
        row-gap: 0.5rem;
    }

    .a4-landscape-container .space-y-3>*+* {
        margin-top: 0.5rem;
    }

    .a4-landscape-container .space-y-4>*+* {
        margin-top: 0.75rem;
    }

    .a4-landscape-container .mb-6 {
        margin-bottom: 0.75rem;
    }

    .a4-landscape-container .px-4 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .a4-landscape-container .py-4 {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
    }

    .a4-landscape-container .text-xs {
        font-size: 0.7rem;
    }

    .a4-landscape-container .p-2 {
        padding: 0.5rem;
    }
</style>

<div class="a4-landscape-container">
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
    <div class="break-inside-avoid mb-8">
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
    <div class="break-inside-avoid mb-8">
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
                <x-data-row label="Orang Pelapor" :value="$laporan->pelapor_insiden_pasien ?? '-'" />
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
    <div class="break-inside-avoid mb-8">
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
    <div class="break-inside-avoid mb-8">
        <x-section-header title="BAGIAN D: Timeline Kronologi" />
        <div class="bg-white border border-slate-300 p-2">
            @php
            $timelineData = $this->getTimelineEventsForComponent();
            @endphp
            <x-timeline-events :eventsByDate="$timelineData['eventsByDate']" :dateCategories="$timelineData['dateCategories']" />
        </div>
    </div>

    <!-- SECTION E: GRADING RISIKO -->
    @if(in_array($laporan->status, ['dilaporkan', 'revisi_unit']))
    <div class="break-inside-avoid mb-8">
        <x-section-header title="BAGIAN E: Grading Risiko" />
        <div class="bg-white border border-slate-300 p-2">
            @if($laporan->status === 'dilaporkan')
            <!-- Editable version for dilaporkan status -->
            <x-grading-display :grade="$laporan->grading_risiko ?? 'BIRU'" :justification="$laporan->catatan_tambahan ?? 'Tidak ada justifikasi'" :editable="true" />
            @else
            <!-- Read-only version for revisi_unit status -->
            <x-grading-display :grade="$laporan->grading_risiko ?? 'BIRU'" :justification="$laporan->catatan_tambahan ?? 'Tidak ada justifikasi'" :disabled="true" />
            @endif
        </div>
    </div>
    @endif

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
</div>