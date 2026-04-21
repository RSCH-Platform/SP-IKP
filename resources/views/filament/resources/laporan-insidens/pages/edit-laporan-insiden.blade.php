<x-filament-panels::page x-data="{ activeTab: 'form' }">
    {{-- Header Section --}}
    <div class="ikp-header status-{{ str_replace('_', '-', $record->status ?? 'draft') }}">
        <div class="ikp-header-content">
            {{-- Hospital Info Header --}}
            <div class="ikp-header-top">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <div class="ikp-logo-circle">
                            <svg style="width: 2.5rem; height: 2.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="ikp-title">Edit Laporan Insiden</h2>
                        <p class="ikp-subtitle">Sistem Pelaporan Insiden Keselamatan Pasien (IKP)</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="ikp-status-badge status-{{ str_replace('_', '-', $record->status ?? 'draft') }}">
                        <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>
                            @switch($record->status ?? 'draft')
                            @case('draft')
                            Draft
                            @break
                            @case('dilaporkan')
                            Dilaporkan
                            @break
                            @case('revisi')
                            Perlu Revisi
                            @break
                            @case('diverifikasi')
                            Diverifikasi
                            @break
                            @case('revisi_unit')
                            Revisi Unit
                            @break
                            @case('investigasi')
                            Investigasi
                            @break
                            @default
                            Draft
                            @endswitch
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                        Dibuat: {{ $record->created_at?->format('d F Y H:i') ?? 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Laporan Info Grid --}}
            <div class="ikp-info-grid">
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Nomor Laporan</div>
                    <div class="ikp-info-value">{{ $record->nomor_laporan ?? '-' }}</div>
                </div>
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Pelapor</div>
                    <div class="ikp-info-value">{{ $record->nama_pelapor ?? '-' }}</div>
                </div>
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Tanggal Insiden</div>
                    <div class="ikp-info-value">{{ $record->tanggal_insiden?->format('d F Y') ?? '-' }}</div>
                </div>
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Jenis Insiden</div>
                    <div class="ikp-info-value">{{ $record->jenis_insiden ?? '-' }}</div>
                </div>
            </div>

            <div x-data="{open:false}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                {{-- Header --}}
                <div
                    @click="open = !open"
                    class="group flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700 transition">

                    {{-- Left: Title --}}
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        <x-heroicon-o-clock class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        Workflow Progress
                    </h3>

                    {{-- Right: Chevron --}}
                    <div class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 dark:bg-slate-700 group-hover:bg-gray-200 dark:group-hover:bg-slate-600 transition">

                        <x-heroicon-o-chevron-right
                            class="h-4 w-4 text-gray-500 dark:text-gray-400 transition-transform duration-300"
                            ::class="{ 'rotate-90': open }" />
                    </div>

                </div>

                {{-- COLLAPSED SUMMARY --}}
                <div
                    x-show="!open"
                    x-transition.opacity
                    class="px-6 py-4">
                    @php
                    $current = collect($this->getWorkflowSteps())
                    ->first(fn($s) => $this->getStepStatus($s['key'], $record->status) === 'current');
                    @endphp

                    @if($current)
                    <div class="flex items-center gap-4">

                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white">
                            <x-dynamic-component :component="$current['icon']" class="h-4 w-4" />
                        </div>

                        <div>
                            <div class="font-semibold text-slate-900 dark:text-slate-100">
                                {{ $current['title'] }}
                                <span class="ml-auto rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    proses
                                </span>
                            </div>

                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $current['desc'] }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                {{-- CONTENT --}}
                <div x-show="open" x-transition class="p-8">
                    <div class="relative">
                        {{-- vertical line --}}
                        <div class="absolute left-5 top-0 h-full w-px bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-10">
                            @foreach($this->getWorkflowSteps() as $step)
                            @php $state = $this->getStepStatus($step['key'], $record->status); @endphp
                            <div x-data="{open:false}" class="relative flex items-start gap-6">
                                {{-- ICON --}}
                                <div class="relative z-10 flex h-10 w-10 items-center justify-center rounded-full border-4 border-white dark:border-slate-900 shadow-sm @if($state === 'done') bg-emerald-500 text-white @elseif($state === 'current') bg-blue-600 text-white @else bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 @endif">
                                    @if($state === 'done') <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg> @else <x-dynamic-component :component="$step['icon']" class="h-5 w-5" /> @endif
                                </div>

                                {{-- CONTENT --}}
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="font-semibold text-slate-900 dark:text-slate-100"> {{ $step['title'] }} </div>
                                        {{-- BADGE --}} @if($state === 'done') <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"> selesai </span>
                                        @elseif($state === 'current') <span class="rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400"> proses </span> @endif
                                    </div>

                                    <div class="mb-3 text-sm text-slate-500 dark:text-slate-400"> {{ $step['desc'] }} </div>

                                    @php $stepDetail = $this->getStepDetail($step); $lines = explode("\n", $stepDetail); $message = trim($lines[0]); $details = array_slice($lines, 1); @endphp

                                    @if(count($details) > 0)
                                    {{-- Detail Card --}}
                                    <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
                                        <div class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"> {{ $message }} </div>
                                        <div class="mt-3 space-y-3 border-t border-slate-200 pt-3 dark:border-slate-700">
                                            @foreach($details as $detail)
                                            @if(trim($detail))
                                            <div class="flex items-start gap-3 text-sm">
                                                @if(str_contains($detail,'👤'))
                                                <span class="flex-shrink-0 text-base">👤</span>
                                                <div>
                                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400"> Oleh </div>
                                                    <div class="font-semibold text-slate-900 dark:text-slate-100"> {{ str_replace('👤 ', '', str_replace('👤 Pelapor: ', '', str_replace('👤 Oleh: ', '', trim($detail)))) }} </div>
                                                </div>
                                                @elseif(str_contains($detail,'⏰'))
                                                <span class="flex-shrink-0 text-base">⏰</span>
                                                <div>
                                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400"> Tanggal </div>
                                                    <div class="font-semibold text-slate-900 dark:text-slate-100"> {{ str_replace('⏰ Tanggal: ', '', str_replace('⏰ ', '', trim($detail))) }} </div>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            {{-- Important Notice --}}
            @if($record->rejection_reason ?? false) <div class="mt-6 rounded-lg border-l-4 border-yellow-400 bg-yellow-50 p-4 dark:bg-yellow-900/20">
                <h3 class="text-sm font-semibold text-yellow-900 dark:text-yellow-300">📝 Alasan Pengembalian</h3>
                <p class="mt-1 text-sm text-yellow-800 dark:text-yellow-400"> {{ $record->rejection_reason }} </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="ikp-tabs">
        <button
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'form' }"
            @click="activeTab = 'form'">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Form Edit
        </button>
        <button
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'preview' }"
            @click="activeTab = 'preview'">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Preview Laporan
        </button>
        @if($record->investigation_started_at)
        <button
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'investigasi' }"
            @click="activeTab = 'investigasi'">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Preview Investigasi
        </button>
        @else
        <button
            class="ikp-tab-button"
            disabled
            style="opacity: 0.5; cursor: not-allowed;"
            title="Investigasi belum dimulai">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Preview Investigasi
        </button>
        @endif
    </div>

    {{-- Tab Contents --}}
    {{-- Tab 1: Form Edit --}}
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'form' }">
        <div class="ikp-form-wrapper">
            @if($record->investigation_started_at && $record->investigationStarter)
            <div style="background-color: #ecfdf5; border: 1px solid #86efac; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                <p style="font-size: 0.875rem; color: #166534; margin: 0;">
                    <svg style="width: 1rem; height: 1rem; display: inline-block; margin-right: 0.5rem; vertical-align: middle;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <strong>Investigasi dimulai oleh:</strong> {{ $record->investigationStarter->name ?? '-' }} pada {{ $record->investigation_started_at->translatedFormat('d F Y H:i') ?? '-' }}
                </p>
            </div>
            @endif
            {{ $this->form }}

            <div class="ikp-form-footer">
                <div style="font-size: 0.875rem; color: #6b7280;">
                    <p style="display: flex; align-items: center; margin: 0;">
                        <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Pastikan semua data sudah benar sebelum submit
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @foreach ($this->getFormActions() as $action)
                    {{ $action }}
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 2: Preview Laporan --}}
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'preview' }">
        <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
            <a href="{{ action([\App\Http\Controllers\LaporanInsidenViewController::class, 'show'], $record->nomor_laporan) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Buka Laporan Penuh
            </a>
            <!-- <a href="{{ route('laporan-insiden.pdf', $record->nomor_laporan) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                </svg>
                Download PDF
            </a> -->
        </div>
        @include('filament.resources.laporan-insidens.pages.preview-laporan-insiden-content')
    </div>
    </div>

    {{-- Tab 3: Preview Investigasi --}}
    @if($record->investigation_started_at)
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'investigasi' }">
        <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('investigasi-laporan-insiden.show', $record->nomor_laporan) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Buka Laporan Investigasi
            </a>
            <!-- <a href="{{ route('investigasi-laporan-insiden.pdf', $record->nomor_laporan) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                </svg>
                Download PDF Investigasi
            </a> -->
        </div>
        <div class="preview-container">
            @include('filament.resources.laporan-insidens.pages.preview-investigasi-laporan-insiden-content')
        </div>
    </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>