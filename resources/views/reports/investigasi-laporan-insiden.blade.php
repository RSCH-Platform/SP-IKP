<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investigasi Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
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
                <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Investigator</p>
                <p class="text-xs text-slate-800">{{ $laporan->investigationStarter->name ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Tanggal Investigasi</p>
                <p class="text-xs text-slate-800">{{ $laporan->investigation_started_at?->translatedFormat('d F Y') ?? '-' }} - {{ $laporan->investigation_ended_at?->translatedFormat('d F Y') ?? '-' }}</p>
            </div>
        </div>

        <x-investigasi.report-investigation-section-a :investigationDataGrouped="$investigationDataGrouped" />
        <x-investigasi.report-investigation-section-b :laporan="$laporan" :timelineData="$timelineData" />
        <x-investigasi.report-investigation-section-c :laporan="$laporan" />
    </div>
</body>

</html>