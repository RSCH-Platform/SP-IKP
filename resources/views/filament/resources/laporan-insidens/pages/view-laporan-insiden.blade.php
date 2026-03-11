<x-filament-panels::page>
    {{-- Header Section --}}
    <div class="mb-6 rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            {{-- Hospital Info Header --}}
            <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div>
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500">
                            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Laporan Insiden</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Sistem Pelaporan Insiden Keselamatan Pasien (IKP)</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold
                        @switch($record->status ?? 'draft')
                            @case('draft')
                                bg-gray-100 text-gray-700 dark:bg-gray-800/20 dark:text-gray-300
                            @break
                            @case('dilaporkan')
                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300
                            @break
                            @case('revisi')
                                bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300
                            @break
                            @case('diverifikasi')
                                bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300
                            @break
                            @case('revisi_unit')
                                bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300
                            @break
                            @case('investigasi')
                                bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300
                            @break
                        @endswitch
                    ">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
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
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                        Dibuat: {{ $record->created_at?->format('d F Y H:i') ?? 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Laporan Info Grid --}}
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded bg-gray-100 p-4 dark:bg-gray-800/50 dark:ring-1 dark:ring-gray-700">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Nomor Laporan</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->nomor_laporan ?? '-' }}</div>
                </div>
                <div class="rounded bg-gray-100 p-4 dark:bg-gray-800/50 dark:ring-1 dark:ring-gray-700">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Pelapor</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->nama_pelapor ?? '-' }}</div>
                </div>
                <div class="rounded bg-gray-100 p-4 dark:bg-gray-800/50 dark:ring-1 dark:ring-gray-700">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Tanggal Insiden</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->tanggal_insiden?->format('d F Y') ?? '-' }}</div>
                </div>
                <div class="rounded bg-gray-100 p-4 dark:bg-gray-800/50 dark:ring-1 dark:ring-gray-700">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Jenis Insiden</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $record->jenis_insiden ?? '-' }}</div>
                </div>
            </div>

            {{-- Workflow Progress Component --}}
            <livewire:laporan-workflow-timeline :record="$record" wire:key="workflow-{{ $record->id }}" />

            {{-- Important Notice --}}
            @if($record->rejection_reason ?? false)
            <div class="mt-6 rounded-lg border-l-4 border-yellow-400 bg-yellow-50 p-4 dark:bg-yellow-900/20">
                <h3 class="text-sm font-semibold text-yellow-900 dark:text-yellow-300">📝 Alasan Pengembalian</h3>
                <p class="mt-1 text-sm text-yellow-800 dark:text-yellow-400">
                    {{ $record->rejection_reason }}
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Content Section --}}
    <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            {{ $this->infolist }}

            <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                <div class="flex items-center justify-end gap-2 flex-wrap">
                    @foreach ($this->getHeaderActions() as $action)
                    {{ $action }}
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Info --}}
    <div class="mt-6 text-center text-xs text-gray-600 dark:text-gray-400">
        <p>Sistem Pelaporan IKP - Informasi dalam laporan ini bersifat rahasia dan hanya untuk keperluan internal</p>
        <p class="mt-1">Terakhir diperbarui: {{ $record->updated_at?->format('d F Y H:i') ?? 'N/A' }}</p>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>