<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Helpers\ChartFilterProvider;
use App\Filament\Widgets\Helpers\ChartQueryBuilder;
use App\Filament\Widgets\Helpers\PieChartBuilder;
use App\Filament\Widgets\Helpers\TrendChartBuilder;
use App\Filament\Widgets\Helpers\TrendFooterGenerator;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\On;

class TrendLaporanInsiden extends ApexChartWidget implements HasForms
{
    use InteractsWithForms, HasFiltersSchema;

    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Allow access if user has permission to view incident reports
        return $user->hasAnyPermission(['ForceEdit:LaporanInsiden']);
    }

    protected string $view = 'filament.widgets.trend-laporan-insiden';

    protected static ?string $chartId = 'trendLaporanInsiden';

    protected static ?string $heading = 'Analisis Tren Laporan Insiden';

    protected static ?string $subheading = 'Menyajikan perkembangan jumlah laporan insiden secara periodik berdasarkan filter yang dipilih.';

    protected static bool $deferLoading = false;

    protected static ?int $sort = 20;

    public string $tahun;

    public bool $showAverage = true;

    /** @var array<string> */
    public array $statusFilter = ['investigasi', 'selesai_investigasi'];

    protected static ?string $footer = null;

    protected int|string|array $columnSpan = 'full';

    // Chart heights
    public int $trendChartHeight = 350;

    public int $pieChartHeight = 260;

    // Grading colors map
    protected array $gradingColors = [
        'Biru' => '#3b82f6',
        'Hijau' => '#10b981',
        'Kuning' => '#f59e0b',
        'Merah' => '#ef4444',
    ];

    public function mount(): void
    {
        $this->tahun = (string) now()->year;

        parent::mount();
    }

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components(
            ChartFilterProvider::buildFilterComponents($this->statusFilter)
        );
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    #[On('status-filter-changed')]
    public function onStatusFilterChanged(array $statuses): void
    {
        $this->statusFilter = $statuses;
        $this->updateOptions();
    }

    public function updatedStatusFilter(): void
    {
        $this->updateOptions();
    }

    /**
     * Get base query dengan permission dan status filter
     */
    protected function getBaseQuery()
    {
        return (new ChartQueryBuilder())
            ->withStatusFilter($this->statusFilter)
            ->build();
    }

    protected function getTrendOptions(): array
    {
        $selectedYear = (int) ($this->tahun ?: now()->year);
        $query = $this->getBaseQuery();

        $trendBuilder = new TrendChartBuilder($query, $selectedYear, $this->showAverage);
        $series = $trendBuilder->getMonthlySeries();
        $stats = $trendBuilder->calculateStats($series);

        // Generate footer
        static::$footer = TrendFooterGenerator::generate($stats);

        return $trendBuilder->buildOptions(350);
    }

    protected function getJenisOptions(): array
    {
        $query = $this->getBaseQuery();

        $pieBuilder = new PieChartBuilder(
            $query,
            'jenis_insiden',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6']
        );

        return $pieBuilder->buildOptions();
    }

    protected function getGradingOptions(): array
    {
        $query = $this->getBaseQuery();

        $pieBuilder = new PieChartBuilder(
            $query,
            'grading_risiko',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
            $this->gradingColors
        );

        return $pieBuilder->buildOptions();
    }
}
