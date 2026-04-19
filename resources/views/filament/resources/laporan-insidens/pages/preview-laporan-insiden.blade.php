@php
$laporan = $this->record;
@endphp

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

<div class="max-w-5xl mx-auto px-4 py-4 bg-white">
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
    <x-report-incident-details :laporan="$laporan" />

    <!-- SECTION C: TINDAKAN YANG DILAKUKAN -->
    <x-report-action-section :laporan="$laporan" />

    <!-- SECTION D: KRONOLOGI TIMELINE -->
    <div class="break-inside-avoid mb-6">
        <x-section-header title="BAGIAN D: Kronologi Timeline" />
        <div class="bg-white border border-slate-300 p-2">
            @php
            $timelineData = $this->getTimelineEventsForComponent();
            @endphp
            <x-timeline-events :eventsByDate="$timelineData['eventsByDate']" :dateCategories="$timelineData['dateCategories']" />
        </div>
    </div>

    <!-- SECTION E: GRADING RISIKO -->
    @if(in_array($laporan->status, ['dilaporkan', 'revisi_unit']))
    <div class="break-inside-avoid mb-6">
        <x-section-header title="BAGIAN E: Grading Risiko" />
        <div class="bg-white border border-slate-300 p-2">
            @if($laporan->status === 'dilaporkan')
            <!-- Editable version for dilaporkan status -->
            <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" :editable="true" />
            @else
            <!-- Read-only version for revisi_unit status -->
            <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" :disabled="true" />
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