@php
    $plugin = (function_exists('filament') && filament()->isServing()) ? \Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin::get() : null;
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $isCollapsible = $this->isCollapsible();
    $darkMode = $this->getDarkMode();
    $pollingInterval = $this->getPollingInterval();
    $chartId = $this->getChartId();
    $chartOptions = $this->getCategoryOptions();
    $loadingIndicator = $this->getLoadingIndicator();
    $deferLoading = $this->getDeferLoading();
    $readyToLoad = $this->readyToLoad;
    $extraJsOptions = $this->extraJsOptions();

    $optionsHash = md5(json_encode($chartOptions));
    $chartIdWidget = $chartId . '_' . $optionsHash;
    
    $tableData = $this->getTableData();
    $rows = $tableData['rows'];
    $totalAll = $tableData['totalAll'];
    $chartColors = $chartOptions['colors'] ?? [];
@endphp
<x-filament-widgets::widget class="fi-wi-chart filament-widgets-chart-widget filament-apex-charts-widget">
    <x-filament::section class="filament-apex-charts-section" :description="$subheading" :heading="$heading"
        :collapsible="$isCollapsible">
        <div x-data="{ dropdownOpen: false }" @apexhcharts-dropdown.window="dropdownOpen = $event.detail.open">

            {{-- Filter Panel --}}
            <div class="mb-5 flex justify-between items-end">
                <form action="{{ route('export.incident-category-pdf') }}" method="POST" target="_blank" class="self-end">
                    @csrf
                    <input type="hidden" name="year" :value="$wire.selectedYear">
                    <input type="hidden" name="month" :value="$wire.selectedMonth">
                    <x-filament::button type="submit" color="gray" icon="heroicon-o-printer" size="sm">
                        Print Laporan
                    </x-filament::button>
                </form>

                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[120px]">
                        <label
                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tahun</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="selectedYear">
                                <option value="">Semua Tahun</option>
                                @foreach ($this->getAvailableYears() as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="min-w-[140px]">
                        <label
                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Bulan</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="selectedMonth">
                                <option value="">Semua Bulan</option>
                                @foreach ($this->getMonthOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                <div class="bg-white/0 p-2 rounded">
                    <h4 class="text-sm font-medium mb-2 text-center text-slate-700 dark:text-slate-300">Distribusi Kategori Insiden</h4>
                    <div wire:key="category-chart-{{ $optionsHash }}">
                        <x-filament-apex-charts::chart :chart-id="$chartIdWidget" :chart-options="$chartOptions"
                            :content-height="$this->pieChartHeight" :polling-interval="$pollingInterval"
                            :loading-indicator="$loadingIndicator" :dark-mode="$darkMode" :defer-loading="$deferLoading"
                            :ready-to-load="$readyToLoad" :extra-js-options="$extraJsOptions" />
                    </div>
                </div>

                <div class="bg-white/0 p-2 rounded">
                    <h4 class="text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Data Kategori Insiden</h4>
                    <x-report-table
                        scrollClass="max-w-full overflow-x-auto rounded-lg border border-slate-200 dark:border-white/10"
                    >
                        <x-slot:colgroup>
                            <colgroup>
                                <col class="w-[50%]">
                                <col class="w-[25%]">
                                <col class="w-[25%]">
                            </colgroup>
                        </x-slot:colgroup>
                        <x-slot:header>
                            <tr>
                                <x-report-table.th class="border-b border-slate-200 dark:border-white/10">Kategori Insiden</x-report-table.th>
                                <x-report-table.th align="center" class="border-b border-slate-200 dark:border-white/10">Jumlah</x-report-table.th>
                                <x-report-table.th align="center" class="border-b border-slate-200 dark:border-white/10">Persentase</x-report-table.th>
                            </tr>
                        </x-slot:header>
                        
                        @forelse ($rows as $index => $row)
                            <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40 transition-colors">
                                <x-report-table.td class="border-b border-slate-200 dark:border-white/10 font-medium text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $chartColors[$index % count($chartColors)] ?? '#ccc' }}"></span>
                                    {{ $row['kategori'] }}
                                </x-report-table.td>
                                <x-report-table.td align="center" class="border-b border-slate-200 dark:border-white/10">{{ $row['total'] }}</x-report-table.td>
                                <x-report-table.td align="center" class="border-b border-slate-200 dark:border-white/10">{{ $row['persentase'] }}%</x-report-table.td>
                            </tr>
                        @empty
                            <x-report-table.empty :colspan="3" title="Data tidak tersedia" description="Belum terdapat data kategori insiden pada periode ini." />
                        @endforelse
                        
                        @if ($totalAll > 0)
                            <tr class="bg-slate-50 font-bold text-slate-900 dark:bg-slate-800/80 dark:text-white border-t-2 border-slate-200 dark:border-white/10">
                                <x-report-table.td>TOTAL</x-report-table.td>
                                <x-report-table.td align="center">{{ $totalAll }}</x-report-table.td>
                                <x-report-table.td align="center">100%</x-report-table.td>
                            </tr>
                        @endif
                    </x-report-table>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
