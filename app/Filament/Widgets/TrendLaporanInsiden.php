<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TrendLaporanInsiden extends ApexChartWidget implements HasForms
{
    use InteractsWithForms, HasFiltersSchema, HasWidgetShield;

    protected string $view = 'filament.widgets.trend-laporan-insiden';

    protected static ?string $chartId = 'trendLaporanInsiden';

    protected static ?string $heading = 'Analisis Tren Laporan Insiden';

    protected static ?string $subheading = 'Menyajikan perkembangan jumlah laporan insiden secara periodik berdasarkan filter yang dipilih.';

    protected static bool $deferLoading = false;

    protected static ?int $sort = 20;

    public string $tahun;

    public bool $showAverage = true;

    protected static ?string $footer = null;

    protected int|string|array $columnSpan = 'full';

    // Customizable parameters for easier tweaking
    // Trend chart uses warning color (kuning)
    public array $trendColors = ['#f59e0b'];

    // Default palette for categorical/pie charts
    public array $jenisColors = ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6'];

    // Grading colors map
    public array $gradingColors = [
        'Biru' => '#3b82f6',
        'Hijau' => '#10b981',
        'Kuning' => '#f59e0b',
        'Merah' => '#ef4444',
    ];

    public int $trendChartHeight = 350;
    public int $pieChartHeight = 260;

    public function mount(): void
    {
        $this->tahun = (string) now()->year;

        parent::mount();
    }

    public function filtersSchema(Schema $schema): Schema
    {
        $currentYear = (string) now()->year;

        return $schema->components([
            Select::make('tahun')
                ->label('Tahun Laporan')
                ->native(false)
                ->options([$currentYear => $currentYear])
                ->default($currentYear)
                ->live(),

            Toggle::make('showAverage')
                ->label('Tampilkan Garis Rata-rata')
                ->default(true)
                ->live(),
        ]);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return $query;
        }

        if ($user->can('ForceEdit:LaporanInsiden')) {
            $unitIds = $user->unitKerjas()->pluck('id');

            return $query->whereIn('unit_kerja_id', $unitIds);
        }

        if ($user->can('Submit:LaporanInsiden')) {
            return $query->where('user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    protected function getMonthlySeries(int $year): array
    {
        $monthlyCounts = $this->scopedQuery()
            ->whereYear('tanggal_insiden', $year)
            ->whereNotNull('tanggal_insiden')
            ->selectRaw('MONTH(tanggal_insiden) as month_number, COUNT(*) as total')
            ->groupBy('month_number')
            ->pluck('total', 'month_number');

        $series = [];

        for ($month = 1; $month <= 12; $month++) {
            $series[] = (int) ($monthlyCounts[$month] ?? 0);
        }

        return $series;
    }

    protected function getMonthCategories(): array
    {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    }

    protected function getOptions(): array
    {
        $selectedYear = (int) ($this->tahun ?: now()->year);
        $categories = $this->getMonthCategories();
        $series = $this->getMonthlySeries($selectedYear);

        $average = round(collect($series)->avg(), 1);
        $total = array_sum($series);
        $peakValue = max($series);
        $peakMonthIndex = array_search($peakValue, $series, true);
        $peakMonthName = $peakMonthIndex === false ? '-' : $categories[$peakMonthIndex];

        static::$footer = "<div class='mt-3 text-xs text-gray-600'>" .
            "<span>Total {$selectedYear}: <strong>{$total}</strong> laporan</span>" .
            "<span class='mx-2'>|</span>" .
            "<span>Rata-rata bulanan: <strong>{$average}</strong></span>" .
            "<span class='mx-2'>|</span>" .
            "<span>Puncak: <strong>{$peakMonthName}</strong> ({$peakValue})</span>" .
            "</div>";

        return [
            'chart' => [
                'type' => 'line',
                'height' => $this->trendChartHeight,
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
                'animations' => ['easing' => 'easeinout', 'speed' => 600],
            ],

            'series' => [['name' => 'Jumlah Insiden', 'data' => $series]],

            'stroke' => ['curve' => 'smooth', 'width' => 3],

            'markers' => ['size' => 5, 'hover' => ['sizeOffset' => 2]],

            'dataLabels' => ['enabled' => false],

            'grid' => ['borderColor' => '#e5e7eb', 'strokeDashArray' => 4],

            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['fontFamily' => 'inherit', 'fontSize' => '12px']],
                'title' => ['text' => "Bulan ({$selectedYear})"],
            ],

            'yaxis' => [
                'title' => ['text' => 'Jumlah Laporan'],
                'labels' => ['style' => ['fontFamily' => 'inherit', 'fontSize' => '12px']],
            ],

            'tooltip' => ['theme' => 'dark', 'x' => ['show' => true]],

            'colors' => $this->trendColors,

            'annotations' => [
                'yaxis' => $this->showAverage ? [[
                    'y' => $average,
                    'borderColor' => '#f59e0b',
                    'label' => ['borderColor' => '#f59e0b', 'style' => ['color' => '#fff', 'background' => '#f59e0b'], 'text' => 'Rata-rata: ' . $average],
                ]] : [],
            ],
        ];
    }

    protected function getJenisOptions(): array
    {
        $rows = $this->scopedQuery()
            ->whereNotNull('jenis_insiden')
            ->selectRaw('jenis_insiden as label, COUNT(*) as total')
            ->groupBy('jenis_insiden')
            ->orderByDesc('total')
            ->get();

        $labels = $rows->pluck('label')->map(fn($v) => $v ?: 'Lainnya')->toArray();
        $series = $rows->pluck('total')->map(fn($v) => (int) $v)->toArray();

        if (empty($labels)) {
            $labels = ['Tidak ada data'];
            $series = [0];
        }

        return [
            'chart' => ['type' => 'pie', 'height' => $this->pieChartHeight],
            'series' => $series,
            'labels' => $labels,
            'colors' => array_slice($this->jenisColors, 0, max(1, count($labels))),
            'tooltip' => ['theme' => 'dark'],
            'legend' => ['position' => 'bottom'],
        ];
    }

    protected function getGradingOptions(): array
    {
        $rows = $this->scopedQuery()
            ->whereNotNull('grading_risiko')
            ->selectRaw('grading_risiko as label, COUNT(*) as total')
            ->groupBy('grading_risiko')
            ->orderByDesc('total')
            ->get();

        $labels = $rows->pluck('label')->map(fn($v) => $v ?: 'Tidak diketahui')->toArray();
        $series = $rows->pluck('total')->map(fn($v) => (int) $v)->toArray();

        if (empty($labels)) {
            $labels = ['Tidak ada data'];
            $series = [0];
        }

        $colors = [];
        foreach ($labels as $label) {
            $colors[] = $this->gradingColors[$label] ?? '#9ca3af';
        }

        return [
            'chart' => ['type' => 'pie', 'height' => $this->pieChartHeight],
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors,
            'tooltip' => ['theme' => 'dark'],
            'legend' => ['position' => 'bottom'],
        ];
    }
}
