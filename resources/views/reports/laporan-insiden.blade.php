<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @page {
            margin: 15mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: white;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .allow-break {
            page-break-inside: auto;
        }

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

        @media print {
            * {
                margin: 0;
                padding: 0;
                box-shadow: none !important;
            }

            .no-print {
                display: none !important;
            }

            .portrait-mode {
                width: 210mm;
                min-height: 297mm;
            }

            .landscape-mode {
                width: 297mm;
                min-height: 210mm;
            }

            .print\:block {
                display: block !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .print\:break-inside-auto {
                break-inside: auto !important;
            }

            .print\:break-inside-avoid {
                break-inside: avoid !important;
            }
        }

        body {
            background: white;
        }

        .portrait-mode,
        .landscape-mode {
            margin: 0 auto;
            padding: 0;
            background: white;
            border: none;
            box-shadow: none;
        }

        .portrait-mode {
            width: 210mm;
            min-height: 297mm;
        }

        .landscape-mode {
            width: 297mm;
            min-height: 210mm;
        }

            {
            ! ! $inlineCss ?? '' ! !
        }
    </style>
</head>

<body class="portrait-mode bg-white text-slate-800 font-sans leading-relaxed">
    <!-- Document Container -->
    <div class="portrait-mode block print:block">
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
        <section class="mb-4 break-inside-auto print:block">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 bg-white border border-slate-300 p-1 items-center text-left print:block break-inside-avoid print:break-inside-avoid">
                <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">No. Laporan</p>
                    <p class="text-xs text-slate-800">{{ $laporan->nomor_laporan ?? '-' }}</p>
                </div>
                <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Unit Kerja</p>
                    <p class="text-xs text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
                </div>
                <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Status</p>
                    <p class="text-xs text-blue-800 font-medium">{{ ucfirst($laporan->status ?? 'Draft') }}</p>
                </div>
                <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Tanggal Cetak</p>
                    <p class="text-xs text-slate-800">{{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </section>

        <!-- SECTION A: DATA PASIEN -->
        <section class="mb-4 break-inside-auto print:block">
            <x-report-patient-section :laporan="$laporan" />
        </section>

        <!-- SECTION B: RINCIAN KEJADIAN -->
        <section class="mb-4 break-inside-auto print:block">
            <x-report-incident-details :laporan="$laporan" />
        </section>

        <!-- SECTION C: TINDAKAN YANG DILAKUKAN -->
        <section class="mb-4 break-inside-auto print:block">
            <x-report-action-section :laporan="$laporan" />
        </section>

        <!-- SECTION D: KRONOLOGI TIMELINE -->
        <section class="mb-4 break-inside-auto print:block">
            <x-report-timeline-section :timeline-data="$timelineData" />
        </section>

        <!-- SECTION E: GRADING RISIKO -->
        <section class="mb-4 break-inside-auto print:block">
            <x-section-header title="BAGIAN E: Grading Risiko" />
            <div class="bg-white border border-slate-300 p-2 print:block break-inside-avoid print:break-inside-avoid">
                @if($laporan->status === 'dilaporkan')
                <!-- Editable version for dilaporkan status -->
                <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" :editable="false" />
                @else
                <!-- Read-only version for revisi_unit status -->
                <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" :disabled="false" />
                @endif
            </div>
        </section>

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