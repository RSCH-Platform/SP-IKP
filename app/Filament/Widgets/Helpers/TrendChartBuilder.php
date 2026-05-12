<?php

namespace App\Filament\Widgets\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Membangun konfigurasi dan data untuk trend chart
 */
class TrendChartBuilder
{
    protected Builder $query;

    protected int $year;

    protected bool $showAverage;

    protected int $startMonth;

    protected int $endMonth;

    protected array $trendColors;

    public function __construct(
        Builder $query,
        int $year,
        bool $showAverage = true,
        int $startMonth = 1,
        int $endMonth = 12
    ) {
        $this->query = $query;
        $this->year = $year;
        $this->showAverage = $showAverage;
        $this->startMonth = max(1, min(12, $startMonth));
        $this->endMonth = max($this->startMonth, min(12, $endMonth));
        $this->trendColors = ['#f59e0b'];
    }

    /**
     * Set custom colors untuk trend
     *
     * @param array<string> $colors
     */
    public function setColors(array $colors): self
    {
        $this->trendColors = $colors;

        return $this;
    }

    /**
     * Get monthly series data
     *
     * @return array<int>
     */
    public function getMonthlySeries(): array
    {
        $monthlyCounts = $this->query
            ->whereNotNull('tanggal_insiden')
            ->selectRaw('MONTH(tanggal_insiden) as month_number, COUNT(*) as total')
            ->groupBy('month_number')
            ->pluck('total', 'month_number');

        $series = [];

        for ($month = $this->startMonth; $month <= $this->endMonth; $month++) {
            $series[] = (int) ($monthlyCounts[$month] ?? 0);
        }

        return $series;
    }

    /**
     * Get month category labels
     *
     * @return array<string>
     */
    public function getMonthCategories(): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return array_slice($labels, $this->startMonth - 1, $this->endMonth - $this->startMonth + 1);
    }

    /**
     * Calculate statistics dari series
     *
     * @param array<int> $series
     * @return array<string, mixed>
     */
    public function calculateStats(array $series): array
    {
        $average = round(collect($series)->avg(), 1);
        $total = array_sum($series);

        $peakValue = max($series);
        $peakMonthIndex = array_search($peakValue, $series, true);
        $categories = $this->getMonthCategories();
        $peakMonthName = $peakMonthIndex === false ? '-' : $categories[$peakMonthIndex];

        $growth = count($series) > 1
            ? round((end($series) - $series[0]) / max($series[0], 1) * 100, 1)
            : 0;

        return [
            'average' => $average,
            'total' => $total,
            'peakValue' => $peakValue,
            'peakMonthName' => $peakMonthName,
            'growth' => $growth,
            'increase' => $growth > 0 ? abs($growth) : 0,
            'decrease' => $growth < 0 ? abs($growth) : 0,
        ];
    }

    /**
     * Build annotation untuk average line
     *
     * @return array<mixed>
     */
    public function buildAverageAnnotation(float $average): array
    {
        if (! $this->showAverage) {
            return [];
        }

        return [
            [
                'y' => $average,
                'borderColor' => '#f59e0b',
                'strokeDashArray' => 6,

                'label' => [
                    'borderColor' => '#f59e0b',

                    'style' => [
                        'background' => '#f59e0b',
                        'color' => '#fff',
                        'fontSize' => '11px',
                        'fontWeight' => 600,
                    ],

                    'text' => 'Rata-rata: ' . $average,
                ],
            ],
        ];
    }

    /**
     * Build complete ApexCharts configuration
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<int>|null $series
     * @return array<string, mixed>
     */
    public function buildOptions(int $height = 350, ?array $series = null): array
    {
        $series = $series ?? $this->getMonthlySeries();
        $categories = $this->getMonthCategories();
        $stats = $this->calculateStats($series);
        $annotations = $this->buildAverageAnnotation($stats['average']);

        return [
            'chart' => [
                'type' => 'area',
                'height' => $height,
                'fontFamily' => 'inherit',

                'toolbar' => [
                    'show' => true,
                    'tools' => [
                        'download' => true,
                        'selection' => false,
                        'zoom' => false,
                        'zoomin' => false,
                        'zoomout' => false,
                        'pan' => false,
                        'reset' => false,
                    ],
                ],

                'zoom' => [
                    'enabled' => false,
                ],

                'animations' => [
                    'enabled' => true,
                    'speed' => 900,

                    'animateGradually' => [
                        'enabled' => true,
                        'delay' => 120,
                    ],

                    'dynamicAnimation' => [
                        'enabled' => true,
                        'speed' => 400,
                    ],
                ],

                'dropShadow' => [
                    'enabled' => true,
                    'top' => 6,
                    'left' => 0,
                    'blur' => 10,
                    'opacity' => 0.12,
                ],
            ],

            'series' => [
                [
                    'name' => 'Jumlah Insiden',
                    'data' => $series,
                ],
            ],

            'colors' => $this->trendColors,

            'fill' => [
                'type' => 'gradient',

                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [0, 90, 100],
                ],
            ],

            'stroke' => [
                'curve' => 'smooth',
                'width' => 4,
                'lineCap' => 'round',
            ],

            'markers' => [
                'size' => 4,

                'strokeWidth' => 2,

                'hover' => [
                    'size' => 7,
                ],
            ],

            'dataLabels' => [
                'enabled' => false,
            ],

            'grid' => [
                'show' => true,
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 5,

                'padding' => [
                    'left' => 12,
                    'right' => 12,
                ],
            ],

            'xaxis' => [
                'categories' => $categories,

                'axisBorder' => [
                    'show' => false,
                ],

                'axisTicks' => [
                    'show' => false,
                ],

                'crosshairs' => [
                    'show' => true,

                    'stroke' => [
                        'color' => '#9ca3af',
                        'width' => 1,
                        'dashArray' => 4,
                    ],
                ],

                'labels' => [
                    'style' => [
                        'fontSize' => '12px',
                        'fontWeight' => 500,
                        'colors' => '#6b7280',
                    ],
                ],
            ],

            'yaxis' => [
                'min' => 0,

                'tickAmount' => 5,

                'decimalsInFloat' => 0,

                'labels' => [
                    'style' => [
                        'fontSize' => '12px',
                        'colors' => '#6b7280',
                    ],
                ],
            ],

            'tooltip' => [
                'theme' => 'dark',
                'shared' => true,
                'intersect' => false,

                'x' => [
                    'show' => true,
                ],

                'marker' => [
                    'show' => true,
                ],
            ],

            'legend' => [
                'show' => false,
            ],

            'annotations' => [
                'yaxis' => $annotations,
            ],
        ];
    }

    /**
     * Get debug payload untuk menampilkan snapshot filter dan data.
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<int>|null $series
     * @return array<string, mixed>
     */
    public function getDebugPayload(?array $series = null): array
    {
        $series = $series ?? $this->getMonthlySeries();

        return [
            'year' => $this->year,
            'monthRange' => [$this->startMonth, $this->endMonth],
            'categories' => $this->getMonthCategories(),
            'series' => $series,
            'stats' => $this->calculateStats($series),
        ];
    }
}
