<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
            background: #f1f5f9;
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

<body x-data="{ orientation: 'portrait' }" :class="orientation === 'portrait' ? 'portrait-mode' : 'landscape-mode'" class="bg-gray-500 text-slate-800 font-sans leading-relaxed">
    <style x-text="orientation === 'portrait' ? '@page { size: A4 portrait; margin: 0; }' : '@page { size: A4 landscape; margin: 0; }'"></style>
    <!-- Control Panel -->
    <div class="no-print sticky top-0 z-50 bg-white border-b border-slate-300 shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Format Info -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-slate-700">Format:</label>
                    <button type="button" @click="orientation = 'portrait'" :class="orientation === 'portrait' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-300'" class="px-3 py-1.5 text-sm border rounded-lg transition">
                        Portrait
                    </button>
                    <button type="button" @click="orientation = 'landscape'" :class="orientation === 'landscape' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-300'" class="px-3 py-1.5 text-sm border rounded-lg transition">
                        Landscape
                    </button>
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