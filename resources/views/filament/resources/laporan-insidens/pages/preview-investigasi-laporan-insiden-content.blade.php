@php
$laporan = $record;
$investigationDataGrouped = isset($investigationDataGrouped) ? $investigationDataGrouped : $this->getGroupedInvestigationData();
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
            @php
            $timelineData = $this->getTimelineEventsForComponent();
            @endphp
            <x-timeline-events :eventsByDate="$timelineData['eventsByDate']" :dateCategories="$timelineData['dateCategories']" />
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-xs text-yellow-800">Belum ada timeline untuk laporan ini.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- SECTION D: FLOWCHART ANALISA 5 WHY -->
    <div class="mb-6">
        <x-section-header title="BAGIAN D: Flowchart Analisa 5 WHY" />
        <div class="bg-white border border-slate-300 p-3 space-y-6">

            @if($laporan->problems && $laporan->problems->count() > 0)

            <!-- LEVEL 1 + 2 (FIXED FLOW STRUCTURE) -->
            <div class="flex flex-col items-center mb-12">

                <!-- LEVEL 1: INSIDEN -->
                <div class="px-6 py-3 rounded-lg bg-slate-800 text-white text-sm font-semibold text-center shadow">
                    INSIDEN
                    <div class="text-xs text-slate-300 mt-1">
                        {{ $laporan->deskripsi_kategori_insiden ?? 'Insiden' }}
                    </div>
                </div>

                <!-- VERTICAL LINE -->
                <div class="w-px h-8 bg-slate-300"></div>

                <!-- HORIZONTAL BRANCH LINE -->
                <div class="relative w-full max-w-5xl">
                    <div class="absolute top-0 left-0 right-0 h-px bg-slate-300"></div>

                    <!-- PROBLEMS -->
                    <div class="flex justify-between">

                        @foreach($laporan->problems as $idx => $problem)

                        <div class="flex flex-col items-center w-full">

                            <!-- VERTICAL LINE TO NODE -->
                            <div class="w-px h-6 bg-slate-300"></div>

                            <!-- NODE: PROBLEM -->
                            <div class="w-56 p-4 rounded-lg border border-slate-300 bg-white shadow-sm text-center">

                                <!-- TYPE BADGE (MINIMAL) -->
                                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                    {{ $problem->problem_type }}
                                </span>

                                <!-- TITLE -->
                                <p class="text-xs font-semibold text-slate-800 mt-2">
                                    Masalah {{ $idx + 1 }}
                                </p>

                                <!-- DESC -->
                                <p class="text-xs text-slate-600 mt-1 leading-snug">
                                    {{ Str::limit($problem->problem_description, 70) }}
                                </p>

                            </div>

                        </div>

                        @endforeach

                    </div>
                </div>

            </div>

            <!-- LEVEL 3+: Individual 5 WHY Sub-Flowcharts per Problem -->
            <div class="space-y-8">
                @foreach($laporan->problems as $problemIdx => $problem)

                <div class="break-inside-avoid">

                    <!-- Problem Title Box -->
                    <div class="bg-white border border-slate-300 p-4 mb-4 rounded shadow-sm">
                        <p class="text-xs font-semibold text-slate-900 uppercase">Masalah #{{ $problemIdx + 1 }}: {{ $problem->problem_type }}</p>
                        <p class="text-sm text-slate-700 mt-2">{{ $problem->problem_description }}</p>
                    </div>

                    <!-- Penyebab Langsung -->
                    @if($problem->whys->count() > 0)
                    <div class="mb-4 p-4 bg-white border-l-4 border-l-slate-400 border border-slate-200 rounded">
                        <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Penyebab Langsung</p>
                        <div class="space-y-3">
                            @foreach($problem->whys->sortBy('why_level') as $why)
                            <div class="bg-slate-50 p-3 rounded border border-slate-200">
                                <p class="text-xs font-semibold text-slate-700">Level {{ $why->why_level }}</p>
                                <p class="text-sm text-slate-700 mt-1">{{ $why->problem_statement }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center mb-3">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M12 5v10m0 0l-3-3m3 3l3-3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    @endif

                    <!-- Akar Masalah -->
                    <div class="mb-4 p-4 bg-white border-l-4 border-l-yellow-400 border border-slate-200 rounded">
                        <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Akar Masalah (Root Cause)</p>
                        <div class="bg-slate-50 p-3 rounded border border-slate-200">
                            <p class="text-sm text-slate-800">
                                @if($problem->whys->count() > 0)
                                {{ $problem->whys->sortBy('why_level')->last()?->problem_statement ?? '-' }}
                                @else
                                -
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center mb-3">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M12 5v10m0 0l-3-3m3 3l3-3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <!-- Faktor Kontributor -->
                    @if($problem->contributors->count() > 0)
                    <div class="mb-4 p-4 bg-white border-l-4 border-l-purple-500 border border-slate-200 rounded">
                        <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Faktor Kontributor</p>
                        @php
                        $contributorsByCategory = $problem->contributors->groupBy(function($contrib) {
                        return $contrib->category?->id ?? 'uncategorized';
                        });
                        @endphp
                        <div class="space-y-3">
                            @foreach($contributorsByCategory as $categoryId => $contribs)
                            @php
                            $categoryName = $contribs->first()?->category?->name ?? 'Tidak Dikategorikan';
                            @endphp
                            <div class="border-l-4 border-slate-300 pl-3">
                                <p class="text-xs font-semibold text-slate-800 uppercase mb-2">{{ $categoryName }}</p>
                                <div class="space-y-2 ml-1">
                                    @foreach($contribs as $contrib)
                                    <div class="bg-slate-50 p-3 rounded border border-slate-200 text-xs">
                                        <p class="font-semibold text-slate-800">
                                            {{ $contrib->category?->name ?? '-' }}
                                            @if($contrib->component)
                                            > {{ $contrib->component->name }}
                                            @endif
                                            @if($contrib->subComponent)
                                            > {{ $contrib->subComponent->name }}
                                            @endif
                                        </p>
                                        @if($contrib->description)
                                        <p class="text-slate-700 opacity-75 mt-1">{{ $contrib->description }}</p>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Arrow Down -->
                    <div class="flex justify-center mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-width="2" d="M12 5v10m0 0l-3-3m3 3l3-3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    @endif

                    <!-- Rekomendasi & Tindakan -->
                    @if($problem->recommendations->count() > 0 || $problem->actions->count() > 0)
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Rekomendasi -->
                        @if($problem->recommendations->count() > 0)
                        <div class="p-4 bg-white border-l-4 border-l-green-500 border border-slate-200 rounded">
                            <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Rekomendasi</p>
                            <div class="space-y-3">
                                @foreach($problem->recommendations as $rec)
                                @php
                                $priority = $rec->priority ?? 'medium';
                                $priorityLabels = [
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                                ];
                                @endphp
                                <div class="bg-slate-50 p-3 rounded border border-slate-200">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <p class="text-sm text-slate-800 flex-1">{{ $rec->recommendation_text }}</p>
                                        <span class="text-[11px] font-semibold text-slate-700 px-2 py-1 rounded bg-slate-100 whitespace-nowrap">{{ $priorityLabels[$priority] ?? 'Sedang' }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Tindakan -->
                        @if($problem->actions->count() > 0)
                        <div class="p-4 bg-white border-l-4 border-l-orange-500 border border-slate-200 rounded">
                            <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Tindakan</p>
                            <div class="space-y-3">
                                @foreach($problem->actions as $action)
                                @php
                                $status = $action->status ?? 'pending';
                                $statusLabels = [
                                'pending' => 'Pending',
                                'in-progress' => 'Proses',
                                'completed' => 'Selesai',
                                ];
                                $statusStyles = [
                                'pending' => 'bg-slate-100 text-slate-700',
                                'in-progress' => 'bg-slate-100 text-slate-700',
                                'completed' => 'bg-slate-100 text-slate-700',
                                ];
                                $evidence = $action->getMedia('action_evidence');
                                @endphp
                                <div class="bg-slate-50 p-3 rounded border border-slate-200 text-xs">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <p class="text-sm text-slate-800 flex-1">{{ $action->action_text }}</p>
                                        <span class="{{ $statusStyles[$status] ?? 'bg-slate-100 text-slate-700' }} px-2 py-1 rounded whitespace-nowrap text-[11px] font-semibold">{{ $statusLabels[$status] ?? 'Pending' }}</span>
                                    </div>

                                    @if($action->responsible_person || $action->deadline)
                                    <div class="text-slate-700 mb-2 space-y-1 text-[11px] bg-white bg-opacity-70 p-2 rounded border border-slate-200">
                                        @if($action->responsible_person)
                                        <p><span class="font-semibold">PIC:</span> {{ $action->responsible_person }}</p>
                                        @endif
                                        @if($action->deadline)
                                        <p><span class="font-semibold">Deadline:</span> {{ $action->deadline->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                    @endif

                                    @if($evidence->count() > 0)
                                    <div class="bg-slate-50 p-2 rounded border border-slate-200 mt-2 text-[11px] text-slate-700">
                                        <p class="font-semibold mb-2">Bukti/Dokumen ({{ $evidence->count() }})</p>
                                        <div class="space-y-2">
                                            @foreach($evidence as $media)
                                            @php
                                            $fileName = $media->name;
                                            $fileSize = round($media->size / 1024, 2) . ' KB';
                                            $isImage = in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/gif']);
                                            @endphp
                                            <div class="text-slate-700">
                                                @if($isImage)
                                                <div class="mb-1 p-1 bg-white rounded border border-slate-200 inline-block">
                                                    <img src="{{ $media->getUrl() }}" alt="{{ $fileName }}" class="max-w-[180px] max-h-[280px] rounded border border-slate-300">
                                                </div>
                                                <p>
                                                    @if($media->name)
                                                    {{ Str::limit($media->name, 40) }}
                                                    @endif
                                                    <span class="text-slate-500">({{ $fileSize }})</span>
                                                </p>
                                                @else
                                                <div class="flex items-center gap-2 p-2 bg-white rounded border border-slate-200">
                                                    <span class="text-slate-600">📄</span>
                                                    <p class="text-slate-700 flex-1">{{ Str::limit($media->name, 40) }}</p>
                                                    <span class="text-slate-500 text-[11px]">{{ $fileSize }}</span>
                                                </div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                </div>

                @endforeach
            </div>

            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-xs text-yellow-800">Belum ada masalah/problem yang diidentifikasi untuk laporan ini.</p>
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
</div>
</div>