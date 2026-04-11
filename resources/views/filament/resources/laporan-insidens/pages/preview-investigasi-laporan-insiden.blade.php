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
            <p class="text-xs text-slate-800">{{ $laporan->unitKerja?->unit_name ?? '-' }}</p>
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

    <!-- SECTION A: DATA INSIDEN -->
    <div class="break-inside-avoid mb-6">
        <x-section-header title="BAGIAN A: Ringkasan Data Insiden" />
        <div class="bg-white border border-slate-300 p-2 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <x-data-row label="Nama Pasien" :value="$laporan->nama_pasien ?? '-'" />
                <x-data-row label="No. Rekam Medis" :value="$laporan->nomor_rekam_medis ?? '-'" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <x-data-row label="Tanggal Insiden" :value="$laporan->tanggal_insiden?->translatedFormat('d F Y') ?? '-'" />
                <x-data-row label="Jenis Insiden" :value="$laporan->jenis_insiden ?? '-'" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <x-data-row label="Kategori Insiden" :value="$laporan->kategori_insiden ?? '-'" />
                <x-data-row label="Dampak Insiden" :value="$laporan->dampak_insiden ?? '-'" />
            </div>
            <x-long-text-display label="Penjelasan Insiden" :text="$laporan->deskripsi_kategori_insiden ?? '-'" />
        </div>
    </div>

    <!-- SECTION B: PENGUMPULAN DATA INVESTIGASI -->
    <div class="break-inside-avoid mb-6">
        <x-section-header title="BAGIAN B: Pengumpulan Data Investigasi" />
        <div class="bg-white border border-slate-300 p-2 space-y-4">
            @forelse ($investigationDataGrouped as $categoryKey => $categoryData)
            <!-- Investigation Category Section -->
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <h3 class="text-sm font-bold text-slate-800 uppercase mb-3">
                    {{ $categoryData['label'] }}
                </h3>

                <div class="space-y-3">
                    @forelse ($categoryData['items'] as $item)
                    <div class="bg-slate-50 border border-slate-200 rounded p-3">
                        <!-- Item Header -->
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-slate-700 mb-1">Sumber: <span class="font-normal">{{ $item->sumber ?? '-' }}</span></p>
                                <p class="text-xs text-slate-600">Lokasi: {{ $item->lokasi ?? '-' }}</p>
                            </div>
                            <p class="text-xs text-slate-500">{{ $item->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
                        </div>

                        <!-- Item Content -->
                        <div class="mt-3 bg-white border border-slate-200 rounded p-2">
                            <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">Hasil Investigasi:</p>
                            <p class="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap break-words">{{ $item->hasil ?? '-' }}</p>
                        </div>

                        <!-- File Attachment -->
                        @if($item->file_path)
                        <div class="mt-2 bg-blue-50 border border-blue-200 rounded p-2 flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM18.5 1a2.5 2.5 0 00-2.5 2.5V4H5V3.5A2.5 2.5 0 002.5 1h-1a2.5 2.5 0 00-2.5 2.5v12A2.5 2.5 0 001.5 18h1A2.5 2.5 0 005 15.5V15h8v.5a2.5 2.5 0 001.5 2.5h1a2.5 2.5 0 002.5-2.5v-12A2.5 2.5 0 0018.5 1z" />
                            </svg>
                            <span class="text-xs text-blue-700 font-medium truncate">{{ basename($item->file_path) }}</span>
                        </div>
                        @endif

                        <!-- Investigator Info -->
                        @if($item->creator)
                        <div class="mt-2 text-xs text-slate-600 border-t border-slate-200 pt-2">
                            <p>Diinput oleh: <span class="font-medium">{{ $item->creator->name ?? '-' }}</span></p>
                        </div>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 italic">Tidak ada data {{ strtolower($categoryData['label']) }}</p>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-xs text-yellow-800">Belum ada data pengumpulan investigasi untuk laporan ini.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- SECTION C: KRONOLOGI TIMELINE INVESTIGASI -->
    <div class="break-inside-avoid mb-6">
        <x-section-header title="BAGIAN C: Kronologi Timeline" />
        <div class="bg-white border border-slate-300 p-2">
            @if($laporan->timelineEvents && $laporan->timelineEvents->count() > 0)
            <x-timeline-events :events="$laporan->timelineEvents" />
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-xs text-yellow-800">Belum ada timeline untuk laporan ini.</p>
            </div>
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
                'Dokumen investigasi ini bersifat RAHASIA',
                'Data investigasi diambil dari berbagai sumber termasuk interview, review dokumen, dan observasi',
                'Semua temuan harus diverifikasi dan didokumentasikan dengan baik',
                'Laporan investigasi menjadi dasar untuk penentuan rekomendasi tindak lanjut'
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