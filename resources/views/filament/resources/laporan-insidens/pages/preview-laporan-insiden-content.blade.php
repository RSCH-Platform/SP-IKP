@php
$laporan = $record;
@endphp

<style>
    /* Font sizes in pixels */
    .text-xs {
        font-size: 10px !important;
        line-height: 1.4;
    }

    .text-sm {
        font-size: 10px !important;
        line-height: 1.2;
    }

    .report-field-label {
        font-size: 10px !important;
        line-height: 1;
        color: #1e293b;
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .report-field-title {
        font-size: 12px !important;
        line-height: 1.2;
        color: #0f172a;
        font-weight: 400;
        margin-bottom: 0.25rem;
        text-transform: none;
        letter-spacing: normal;
    }

    .text-lg {
        font-size: 18px !important;
        line-height: 1.5;
    }

    @page {
        size: A4 portrait;
        margin: 0;
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
    }

    body {
        background: white;
    }

    /* Screen mode */
    .portrait-mode,
    .landscape-mode {
        margin: 2rem auto;
        padding: 1.5rem;
        background: white;
        border: 1px solid #cbd5e1;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
    }

    .portrait-mode {
        width: 210mm;
        min-height: 297mm;
    }

    .landscape-mode {
        width: 297mm;
        min-height: 210mm;
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