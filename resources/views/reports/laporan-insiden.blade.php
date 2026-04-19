<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    @php
    $viteCss = null;
    $manifestPath = public_path('build/manifest.json');
    if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    $viteCss = $manifest['resources/css/app.css']['file'] ?? null;
    }
    @endphp
    @if ($viteCss)
    <link rel="stylesheet" href="{{ asset('build/'.$viteCss) }}">
    @else
    <style>
        /* CSS fallback jika build Vite tidak tersedia */
        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
        }
    </style>
    @endif
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
            max-width: 210mm;
        }

        .landscape-mode {
            max-width: 297mm;
        }
    </style>
</head>

<body x-data="{ orientation: 'portrait' }" :class="orientation === 'portrait' ? 'portrait-mode' : 'landscape-mode'" class="bg-white text-slate-800 font-sans leading-relaxed">
    <style x-text="orientation === 'portrait' ? '@page { size: A4 portrait; margin: 0; }' : '@page { size: A4 landscape; margin: 0; }'"></style>
    <!-- Document Container -->
    <div :class="orientation === 'portrait' ? 'portrait-mode' : 'landscape-mode'">
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
        <x-report-patient-section :laporan="$laporan" />

        <!-- SECTION B: RINCIAN KEJADIAN -->
        <x-report-incident-details :laporan="$laporan" />

        <!-- SECTION C: TINDAKAN YANG DILAKUKAN -->
        <x-report-action-section :laporan="$laporan" />

        <!-- SECTION D: KRONOLOGI TIMELINE -->
        <x-report-timeline-section :timeline-data="$timelineData" />

        <!-- SECTION E: GRADING RISIKO -->
        <div class="break-inside-avoid mb-6">
            <x-section-header title="BAGIAN E: Grading Risiko" />
            <div class="bg-white border border-slate-300 p-2">
                @if($laporan->status === 'dilaporkan')
                <!-- Editable version for dilaporkan status -->
                <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" :editable="false" />
                @else
                <!-- Read-only version for revisi_unit status -->
                <x-grading-display :grade="$laporan->grading_risiko" :justification="$laporan->catatan_tambahan" :disabled="false" />
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
    </div>

</body>

</html>