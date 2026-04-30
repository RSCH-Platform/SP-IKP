<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investigasi Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
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
    </style>
</head>

<body class="portrait-mode bg-white text-slate-800 font-sans leading-relaxed">
    <!-- Document Container -->
    <div class="portrait-mode block print:block">
        <!-- Header Component -->
        <x-pelaporan-insiden-header
            title="INVESTIGASI LAPORAN INSIDEN"
            :documentNumber="$laporan->nomor_laporan"
            :additionalInfo="[
                ['label' => 'Tanggal Lapor', 'value' => $laporan->tanggal_lapor?->translatedFormat('d F Y') ?? '-'],
                ['label' => 'Unit Kerja', 'value' => $laporan->unitKerja?->unit_name ?? '-'],
                ['label' => 'Status', 'value' => ucfirst($laporan->status ?? 'Draft')]
            ]" />

        <!-- Info Summary -->
        <section class="mb-4 print:block">
            <x-investigasi.report-investigation-section-a class="page-break" :laporan="$laporan" :investigationDataGrouped="$investigationDataGrouped" />
            <x-investigasi.report-investigation-section-b class="page-break mt-4" :laporan="$laporan" :timelineData="$timelineData" />

            <!-- <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 overflow-x-auto print:hidden">
                <div class="font-semibold mb-2">DEBUG FAKTOR KONTRIBUTOR</div>
                <pre class="whitespace-pre-wrap break-words">{{ json_encode($debugPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div> -->

            <x-investigasi.report-investigation-section-c class="page-break mt-4" :laporan="$laporan" />
        </section>

    </div>
</body>

</html>