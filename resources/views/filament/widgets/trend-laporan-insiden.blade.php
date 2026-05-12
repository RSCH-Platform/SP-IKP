@php
$plugin = (function_exists('filament') && filament()->isServing()) ? \Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin::get() : null;
$heading = $this->getHeading();
$subheading = $this->getSubheading();
$filters = $this->getFilters();
$isCollapsible = $this->isCollapsible();
$darkMode = $this->getDarkMode();
$width = $this->getFilterFormWidth();
$pollingInterval = $this->getPollingInterval();
$chartId = $this->getChartId();
$chartOptions = $this->getTrendOptions();
$jenisOptions = $this->getJenisOptions();
$gradingOptions = $this->getGradingOptions();
$loadingIndicator = $this->getLoadingIndicator();
$contentHeight = $this->getContentHeight();
$deferLoading = $this->getDeferLoading();
$footer = $this->getFooter();
$readyToLoad = $this->readyToLoad;
$extraJsOptions = $this->extraJsOptions();

$trendOptionsHash = md5(json_encode($chartOptions));
$jenisOptionsHash = md5(json_encode($jenisOptions));
$gradingOptionsHash = md5(json_encode($gradingOptions));

$chartIdTrend = $chartId . '_trend_' . $trendOptionsHash;
$chartIdJenis = $chartId . '_jenis_' . $jenisOptionsHash;
$chartIdGrading = $chartId . '_grading_' . $gradingOptionsHash;

$trendCategoriesDebug = $chartOptions['xaxis']['categories'] ?? [];
$trendSeriesDebug = $chartOptions['series'][0]['data'] ?? [];
$jenisLabelsDebug = $jenisOptions['labels'] ?? [];
$jenisSeriesDebug = $jenisOptions['series'] ?? [];
$gradingLabelsDebug = $gradingOptions['labels'] ?? [];
$gradingSeriesDebug = $gradingOptions['series'] ?? [];
@endphp
<x-filament-widgets::widget class="fi-wi-chart filament-widgets-chart-widget filament-apex-charts-widget">
    <x-filament::section
        class="filament-apex-charts-section"
        :description="$subheading"
        :heading="$heading"
        :collapsible="$isCollapsible">
        <div x-data="{ dropdownOpen: false }" @apexhcharts-dropdown.window="dropdownOpen = $event.detail.open">

            @if ($filters || method_exists($this, 'getFiltersSchema'))
            <x-slot name="afterHeader">
                @if ($filters)
                <x-filament::input.wrapper
                    inline-prefix
                    wire:target="filter"
                    class="fi-wi-chart-filter">
                    <x-filament::input.select
                        inline-prefix
                        wire:model.live="filter">
                        @foreach ($filters as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                @endif

                @if (method_exists($this, 'getFiltersSchema'))
                <x-filament::dropdown
                    placement="bottom-end"
                    shift
                    width="xs"
                    class="fi-wi-chart-filter">
                    <x-slot name="trigger">
                        {{ $this->getFiltersTriggerAction() }}
                    </x-slot>

                    <div class="fi-wi-chart-filter-content">
                        {{ $this->getFiltersSchema() }}
                    </div>
                </x-filament::dropdown>
                @endif
            </x-slot>
            @endif

            {{-- Filter Panel --}}
            <div class="mb-5 flex justify-end">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[120px]">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tahun</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="tahun">
                                @foreach ($this->getAvailableYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="min-w-[140px]">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe Periode</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="grouping">
                                <option value="none">Tahun</option>
                                <option value="quarter">Quartal</option>
                                <option value="semester">Semester</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    @if ($this->grouping !== 'none')
                    <div class="min-w-[140px]">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</label>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="period">
                                @foreach ($this->getPeriodOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950/40">
                <livewire:widgets.status-filter
                    :status-filter="$this->statusFilter"
                    :key="'trend-status-filter-' . md5(json_encode($this->statusFilter))" />
            </div>

            <div wire:key="trend-chart-{{ $trendOptionsHash }}">
                <x-filament-apex-charts::chart
                    :chart-id="$chartIdTrend"
                    :chart-options="$chartOptions"
                    :content-height="$this->trendChartHeight"
                    :polling-interval="$pollingInterval"
                    :loading-indicator="$loadingIndicator"
                    :dark-mode="$darkMode"
                    :defer-loading="$deferLoading"
                    :ready-to-load="$readyToLoad"
                    :extra-js-options="$extraJsOptions" />
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="col-span-1 bg-white/0 p-2 rounded">
                    <h4 class="text-sm font-medium mb-2">Jenis Insiden</h4>
                    <div wire:key="jenis-chart-{{ $jenisOptionsHash }}">
                        <x-filament-apex-charts::chart
                            :chart-id="$chartIdJenis"
                            :chart-options="$jenisOptions"
                            :content-height="$this->pieChartHeight"
                            :polling-interval="$pollingInterval"
                            :loading-indicator="$loadingIndicator"
                            :dark-mode="$darkMode"
                            :defer-loading="$deferLoading"
                            :ready-to-load="$readyToLoad"
                            :extra-js-options="$extraJsOptions" />
                    </div>
                </div>

                <div class="col-span-1 bg-white/0 p-2 rounded">
                    <h4 class="text-sm font-medium mb-2">Grading Risiko</h4>
                    <div wire:key="grading-chart-{{ $gradingOptionsHash }}">
                        <x-filament-apex-charts::chart
                            :chart-id="$chartIdGrading"
                            :chart-options="$gradingOptions"
                            :content-height="$this->pieChartHeight"
                            :polling-interval="$pollingInterval"
                            :loading-indicator="$loadingIndicator"
                            :dark-mode="$darkMode"
                            :defer-loading="$deferLoading"
                            :ready-to-load="$readyToLoad"
                            :extra-js-options="$extraJsOptions" />
                    </div>
                </div>
            </div>

            @if ($footer)
            <div class="relative">
                {!! $footer !!}
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>