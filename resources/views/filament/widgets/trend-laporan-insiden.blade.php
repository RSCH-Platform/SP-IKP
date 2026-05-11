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
$chartOptions = $this->getOptions();
$jenisOptions = $this->getJenisOptions();
$gradingOptions = $this->getGradingOptions();
$loadingIndicator = $this->getLoadingIndicator();
$contentHeight = $this->getContentHeight();
$deferLoading = $this->getDeferLoading();
$footer = $this->getFooter();
$readyToLoad = $this->readyToLoad;
$extraJsOptions = $this->extraJsOptions();
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

            <x-filament-apex-charts::chart
                :chart-id="$chartId"
                :chart-options="$chartOptions"
                :content-height="$this->trendChartHeight"
                :polling-interval="$pollingInterval"
                :loading-indicator="$loadingIndicator"
                :dark-mode="$darkMode"
                :defer-loading="$deferLoading"
                :ready-to-load="$readyToLoad"
                :extra-js-options="$extraJsOptions" />

            <div class="grid grid-cols-2 gap-4 mt-4">
                @php
                $chartIdJenis = $chartId . '_jenis';
                $chartIdGrading = $chartId . '_grading';
                @endphp

                <div class="col-span-1 bg-white/0 p-2 rounded">
                    <h4 class="text-sm font-medium mb-2">Jenis Insiden</h4>
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

                <div class="col-span-1 bg-white/0 p-2 rounded">
                    <h4 class="text-sm font-medium mb-2">Grading Risiko</h4>
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

            @if ($footer)
            <div class="relative">
                {!! $footer !!}
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>