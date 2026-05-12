<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Helpers\ChartQueryBuilder;
use App\Filament\Widgets\Helpers\PieChartBuilder;
use App\Filament\Widgets\Helpers\TrendChartBuilder;
use App\Filament\Widgets\Helpers\TrendFooterGenerator;
use App\Models\LaporanInsiden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\On;

class TrendLaporanInsiden extends ApexChartWidget implements HasForms
{
    use InteractsWithForms;

    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Allow access if user has permission to view incident reports
        return Gate::forUser($user)->allows('ForceEdit:LaporanInsiden');
    }

    protected string $view = 'filament.widgets.trend-laporan-insiden';

    protected static ?string $chartId = 'trendLaporanInsiden';

    protected static ?string $heading = 'Analisis Tren Laporan Insiden';

    protected static ?string $subheading = 'Menyajikan perkembangan jumlah laporan insiden secara periodik berdasarkan filter yang dipilih.';

    protected static bool $deferLoading = false;

    protected static ?int $sort = 20;

    public ?int $tahun = null;

    public string $grouping = 'none';

    public ?int $period = 0;

    public bool $showAverage = true;

    /** @var array<string> */
    public array $statusFilter = ['dilaporkan', 'diverifikasi', 'investigasi', 'selesai_investigasi'];

    protected static ?string $footer = null;

    protected int|string|array $columnSpan = 'full';

    // Chart heights
    public int $trendChartHeight = 350;

    public int $pieChartHeight = 260;

    public bool $showDebug = true;

    public array $debugState = [];

    protected string $lastAction = 'mount';

    // Grading colors map
    protected array $gradingColors = [
        'Biru' => '#3b82f6',
        'Hijau' => '#10b981',
        'Kuning' => '#f59e0b',
        'Merah' => '#ef4444',
    ];

    public function mount(): void
    {
        $this->tahun = (int) now()->year;
        $this->period = 0;

        parent::mount();
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->refreshWidgetState('schema-updated');
    }

    public function updatedTahun(): void
    {
        $this->refreshWidgetState('tahun-updated');
    }

    public function updatedGrouping(): void
    {
        // When grouping changes: 'none' stays at period 0 (full year),
        // 'quarter' or 'semester' defaults to period 1 (first segment).
        $this->period = ($this->grouping === 'none') ? 0 : 1;

        $this->refreshWidgetState('grouping-updated');

        // Force chart update by changing options hash
        $this->dispatch('refresh-charts');
    }

    public function updatedPeriod(): void
    {
        // Validate period is valid for current grouping
        if ($this->grouping === 'none') {
            $this->period = 0;
        } elseif ($this->grouping === 'semester' && $this->period > 2) {
            $this->period = 1;
        } elseif ($this->grouping === 'quarter' && $this->period > 4) {
            $this->period = 1;
        }

        $this->refreshWidgetState('period-updated');
    }

    #[On('status-filter-changed')]
    public function onStatusFilterChanged(array $statuses): void
    {
        $this->statusFilter = $statuses;
        $this->refreshWidgetState('status-filter-changed');
    }

    public function updatedStatusFilter(): void
    {
        $this->refreshWidgetState('status-filter-updated');
    }

    /**
     * Get base query dengan permission dan status filter
     */
    protected function getBaseQuery()
    {
        return (new ChartQueryBuilder())
            ->withStatusFilter($this->statusFilter)
            ->withYearFilter($this->tahun)
            ->withPeriodFilter($this->grouping, $this->period)
            ->build();
    }

    protected function getTrendOptions(): array
    {
        $trendSnapshot = $this->buildTrendSnapshot();
        static::$footer = TrendFooterGenerator::generate($trendSnapshot['stats']);

        return $trendSnapshot['options'];
    }

    protected function getJenisOptions(): array
    {
        $baseQuery = $this->getBaseQuery();

        $jenisBuilder = new PieChartBuilder(
            clone $baseQuery,
            'jenis_insiden',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6']
        );

        $jenisData = $jenisBuilder->getData();

        return $jenisBuilder->buildOptions($jenisData);
    }

    protected function getGradingOptions(): array
    {
        $baseQuery = $this->getBaseQuery();

        $gradingBuilder = new PieChartBuilder(
            clone $baseQuery,
            'grading_risiko',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
            $this->gradingColors
        );

        $gradingData = $gradingBuilder->getData();

        return $gradingBuilder->buildOptions($gradingData);
    }

    public function getDebugState(): array
    {
        $selectedYear = (int) ($this->tahun ?: now()->year);
        [$startMonth, $endMonth] = $this->resolveMonthRange();

        $baseQuery = $this->getBaseQuery();

        $trendBuilder = new TrendChartBuilder(clone $baseQuery, $selectedYear, $this->showAverage, $startMonth, $endMonth);
        $trendSeries = $trendBuilder->getMonthlySeries();
        $trendStats = $trendBuilder->calculateStats($trendSeries);
        $trendDebug = $trendBuilder->getDebugPayload($trendSeries);

        $jenisBuilder = new PieChartBuilder(
            clone $baseQuery,
            'jenis_insiden',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6']
        );

        $jenisData = $jenisBuilder->getData();

        $gradingBuilder = new PieChartBuilder(
            clone $baseQuery,
            'grading_risiko',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
            $this->gradingColors
        );

        $gradingData = $gradingBuilder->getData();

        return [
            'action' => $this->lastAction,
            'updated_at' => now()->toDateTimeString(),
            'filters' => [
                'tahun' => $selectedYear,
                'grouping' => $this->grouping,
                'period' => $this->period,
                'period_label' => $this->getSelectedPeriodLabel(),
                'status_filter' => $this->statusFilter,
                'month_range' => [$startMonth, $endMonth],
            ],
            'trend' => array_merge($trendDebug, [
                'stats' => $trendStats,
            ]),
            'pie' => [
                'jenis' => $jenisData,
                'grading' => $gradingData,
            ],
        ];
    }

    public function getAvailableYears(): array
    {
        $years = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden')
            ->selectRaw('YEAR(tanggal_insiden) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($year) => (int) $year)
            ->toArray();

        if (empty($years)) {
            $years[] = (int) now()->year;
        }

        return $years;
    }

    public function getPeriodOptions(): array
    {
        if ($this->grouping === 'none') {
            return [0 => 'Tahun'];
        }

        if ($this->grouping === 'semester') {
            return [
                1 => 'Semester 1',
                2 => 'Semester 2',
            ];
        }

        return [
            1 => 'Quartal 1',
            2 => 'Quartal 2',
            3 => 'Quartal 3',
            4 => 'Quartal 4',
        ];
    }

    public function getSelectedPeriodLabel(): string
    {
        if (!$this->grouping || $this->grouping === 0) {
            return 'Tahun';
        }

        if (!$this->period || $this->period === 0) {
            return 'Tahun';
        }

        if ($this->grouping === 'semester') {
            return 'Semester ' . $this->period;
        }

        return 'Quartal ' . $this->period;
    }

    protected function refreshWidgetState(string $action): void
    {
        $this->lastAction = $action;
    }

    protected function resolveMonthRange(): array
    {
        if (!$this->period || $this->period <= 0) {
            return [1, 12];
        }

        if ($this->grouping === 'semester') {
            return $this->period === 2 ? [7, 12] : [1, 6];
        }

        return match ($this->period) {
            2 => [4, 6],
            3 => [7, 9],
            4 => [10, 12],
            default => [1, 3],
        };
    }

    /**
     * Build trend chart data snapshot tanpa cache.
     *
     * @return array{options: array<string, mixed>, stats: array<string, mixed>}
     */
    protected function buildTrendSnapshot(): array
    {
        $selectedYear = (int) ($this->tahun ?: now()->year);
        [$startMonth, $endMonth] = $this->resolveMonthRange();

        $baseQuery = $this->getBaseQuery();

        $trendBuilder = new TrendChartBuilder(clone $baseQuery, $selectedYear, $this->showAverage, $startMonth, $endMonth);
        $trendSeries = $trendBuilder->getMonthlySeries();
        $trendStats = $trendBuilder->calculateStats($trendSeries);

        return [
            'options' => $trendBuilder->buildOptions($this->trendChartHeight, $trendSeries),
            'stats' => $trendStats,
        ];
    }
}
