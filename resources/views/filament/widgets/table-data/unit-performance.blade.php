<!-- TABLE 1: UNIT KERJA PERFORMANCE -->
<div class="mt-10 space-y-3">
    <div class="border-b border-gray-200 pb-3 dark:border-gray-700 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                📊 Kinerja Penanganan Insiden per Unit Kerja
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Menampilkan perbandingan jumlah laporan, status penanganan,
                dan efektivitas tindak lanjut insiden pada setiap unit kerja.
            </p>
        </div>
        
        <!-- Table 1 Controls -->
        <div class="flex items-center gap-3 no-print">
            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:hover:bg-gray-800">
                <input type="checkbox" wire:model.live="showEmptyUnits" class="h-4 w-4 rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500/50 dark:border-gray-600 dark:bg-gray-900">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tampilkan Semua Unit</span>
            </label>
        </div>
    </div>

    @php
        $table1Data = $this->getTable1UnitPerformance();
        $rows = $table1Data['rows'];
        $paginator = $table1Data['paginator'];
        
        $statuses = $this->statuses ?? [];
        $colspan = 2 + count($statuses) + 1; // Unit, Total, statuses..., Close%
    @endphp

    <x-report-table>
        <x-slot:colgroup>
            <colgroup>
                <col class="w-2/12">
                <col class="w-1/12">
                @foreach($statuses as $k => $label)
                <col class="w-1/12">
                @endforeach
                <col class="w-1/12">
            </colgroup>
        </x-slot:colgroup>

        <x-slot:header>
            <tr>
                <x-report-table.th rowspan="2">Unit Kerja</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Total</x-report-table.th>
                <x-report-table.th :colspan="count($statuses) + 1" align="center">STATUS LAPORAN</x-report-table.th>
            </tr>
            <tr>
                @foreach($statuses as $k => $label)
                <x-report-table.th align="center">{{ $label }}</x-report-table.th>
                @endforeach
                <x-report-table.th align="center">Close%</x-report-table.th>
            </tr>
        </x-slot:header>

        @forelse($rows as $row)
        <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40">
            <x-report-table.td>{{ $row['unit_name'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['total'] }}</x-report-table.td>
            @foreach($statuses as $k => $label)
            <x-report-table.td align="center">{{ $row[$k] ?? 0 }}</x-report-table.td>
            @endforeach
            @php
                $closeRate = $row['close_rate'] ?? 0;
                $closeClass = '';
                if ($row['total'] > 0) {
                    $closeClass = $closeRate >= 85 
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' 
                        : ($closeRate >= 70 
                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' 
                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300');
                } else {
                    $closeClass = 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400';
                }
            @endphp
            <x-report-table.td align="center" class="font-bold {{ $closeClass }}">
                {{ $row['total'] > 0 ? $closeRate . '%' : '-' }}
            </x-report-table.td>
        </tr>
        @empty
        <x-report-table.empty :colspan="$colspan" title="Data tidak tersedia" description="Belum terdapat data unit kerja pada periode yang dipilih." />
        @endforelse
    </x-report-table>

    {{-- Pagination Footer --}}
    @if ($paginator->total() > 0)
        <div class="mt-4 flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between no-print">
            
            {{-- Per Page Selector --}}
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tampilkan</span>
                <div class="w-24">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="unitPerPage">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">data</span>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                {{-- Info Text --}}
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Menampilkan <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->firstItem() }}</span> – <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->lastItem() }}</span> dari <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->total() }}</span> unit
                </span>

                {{-- Pagination Nav --}}
                @if ($paginator->hasPages())
                    <nav class="flex items-center gap-2">
                        <button
                            wire:click="previousPage('unitPage')"
                            wire:loading.attr="disabled"
                            @disabled($paginator->onFirstPage())
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm outline-none transition hover:bg-gray-50 focus:ring-2 focus:ring-primary-500/50 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5" />
                        </button>
                        
                        <button
                            wire:click="nextPage('unitPage')"
                            wire:loading.attr="disabled"
                            @disabled($paginator->onLastPage())
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm outline-none transition hover:bg-gray-50 focus:ring-2 focus:ring-primary-500/50 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5" />
                        </button>
                    </nav>
                @endif
            </div>
        </div>
    @endif
</div>