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
    <x-report-patient-section :laporan="$laporan" />

    <!-- SECTION B: RINCIAN KEJADIAN -->
    <x-report-incident-details :laporan="$laporan" />

    <!-- SECTION C: TINDAKAN YANG DILAKUKAN -->
    <x-report-action-section :laporan="$laporan" />

    <!-- SECTION D: KRONOLOGI TIMELINE -->
    @php
    $timelineData = $this->getTimelineEventsForComponent();
    @endphp
    <x-report-timeline-section :timeline-data="$timelineData" />

    <!-- SECTION E: GRADING RISIKO -->
    <div class="break-inside-avoid mb-8">
        <x-section-header title="BAGIAN E: Grading Risiko" />
        <div class="bg-white border border-slate-300 p-2">
            <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" />
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
</div>