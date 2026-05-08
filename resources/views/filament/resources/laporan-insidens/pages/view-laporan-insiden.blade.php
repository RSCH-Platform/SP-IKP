<x-filament-panels::page
    x-data="{
        activeTab: 'info',
        init() {
            const hash = window.location.hash.replace('#', '');
            if (['info', 'preview', 'investigasi'].includes(hash)) {
                this.activeTab = hash;
            }

            this.$watch('activeTab', (value) => {
                history.replaceState(null, '', `#${value}`);
            });
        },
    }"> {{-- Header Section --}}
    <div class="ikp-header status-draft mb-6 rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="ikp-header-content p-6"> {{-- Hospital Info Header --}}
            <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div>
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500"> <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg> </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Laporan Insiden</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Sistem Pelaporan Insiden Keselamatan Pasien (IKP)</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold                       @switch($record->status ?? 'draft')                           @case('draft')                               bg-gray-100 text-gray-700 dark:bg-gray-800/20 dark:text-gray-300                           @break                           @case('dilaporkan')                               bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300                           @break                           @case('revisi')                               bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300                           @break                           @case('diverifikasi')                               bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300                           @break                           @case('revisi_unit')                               bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300                           @break                           @case('investigasi')                               bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300                           @break                       @endswitch                   "> <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg> <span> @switch($record->status ?? 'draft') @case('draft') Draft @break @case('dilaporkan') Dilaporkan @break @case('revisi') Perlu Revisi @break @case('diverifikasi') Diverifikasi @break @case('revisi_unit') Revisi Unit @break @case('investigasi') Investigasi @break @default Draft @endswitch </span> </div>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400"> Dibuat: {{ $record->created_at?->format('d F Y H:i') ?? 'N/A' }} </p>
                </div>
            </div>

            <div class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-700 p-5 shadow-sm">
                <div class="text-[1rem] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">
                    Insiden
                </div>
                <div class="text-base align-text-top text-gray-900 dark:text-white leading-relaxed">
                    {{ $record->deskripsi_kategori_insiden ?? '-' }}
                </div>
            </div>
            {{-- Laporan Info Grid --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class=" border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-700 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Nomor Laporan</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->nomor_laporan ?? '-' }}</div>
                </div>
                <div class=" border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-700 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Pelapor</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->nama_pelapor ?? '-' }}</div>
                </div>
                <div class=" border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-700 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Tanggal Insiden</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->tanggal_insiden?->format('d F Y') ?? '-' }}</div>
                </div>
                <div class=" border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-700 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Jenis Insiden</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->jenis_insiden ?? '-' }}</div>
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
                            </div>

                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $current['desc'] }}
                            </div>
                        </div>

                        <span class="ml-auto rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            proses
                        </span>

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
            type="button"
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'info' }"
            @click="activeTab = 'info'"
            title="Lihat informasi laporan insiden">
            <svg style="width: 1.125rem; height: 1.125rem;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Info Laporan</span>
        </button>
        <button
            type="button"
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'preview' }"
            @click="activeTab = 'preview'"
            title="Lihat pratinjau laporan insiden">
            <svg style="width: 1.125rem; height: 1.125rem;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
            </svg>
            <span>Pratinjau Laporan</span>
        </button>
        @if($record->investigation_started_at)
        <button
            type="button"
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'investigasi' }"
            @click="activeTab = 'investigasi'"
            title="Lihat hasil investigasi laporan insiden">
            <svg style="width: 1.125rem; height: 1.125rem;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Investigasi</span>
        </button>
        @else
        <button
            type="button"
            class="ikp-tab-button"
            disabled
            title="Investigasi belum dimulai"
            style="opacity: 0.6; cursor: not-allowed;">
            <svg style="width: 1.125rem; height: 1.125rem;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
            </svg>
            <span>Investigasi</span>
        </button>
        @endif
    </div>

    {{-- Tab Contents --}}
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'info' }">
        <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-6">
                {{ $this->infolist }}
            </div>
        </div>
    </div>
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'preview' }">
        <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
            <a href="{{ action([\App\Http\Controllers\LaporanInsidenViewController::class, 'show'], $record->nomor_laporan) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Buka Laporan Penuh
            </a>
        </div>
        @include('filament.resources.laporan-insidens.pages.preview-laporan-insiden-content')
    </div>
    @if($record->investigation_started_at)
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'investigasi' }">
        <a href="{{ route('investigasi-laporan-insiden.show', $record->nomor_laporan) }}" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-4 py-2 mb-4 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            Buka Laporan Investigasi
        </a>
        @include('filament.resources.laporan-insidens.pages.preview-investigasi-laporan-insiden-content')
    </div>
    @endif

    {{-- Footer Info --}}
    <div class="mt-6 text-center text-xs text-gray-600 dark:text-gray-400">
        <p>Sistem Pelaporan IKP - Informasi dalam laporan ini bersifat rahasia dan hanya untuk keperluan internal</p>
        <p class="mt-1">Terakhir diperbarui: {{ $record->updated_at?->format('d F Y H:i') ?? 'N/A' }}</p>
    </div>
    <x-filament-actions::modals />
</x-filament-panels::page>