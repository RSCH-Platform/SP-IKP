<?php

namespace App\Filament\Widgets\Helpers;

use Illuminate\Database\Eloquent\Builder;

/**
 * Membangun konfigurasi dan data untuk pie/donut chart
 */
class PieChartBuilder
{
    protected Builder $query;

    protected string $dataColumn;

    protected string $chartType;

    protected int $chartHeight;

    /** @var array<string, string> */
    protected array $colorMap;

    /** @var array<string> */
    protected array $defaultColors;

    public function __construct(
        Builder $query,
        string $dataColumn,
        array $defaultColors = [],
        array $colorMap = []
    ) {
        $this->query = $query;
        $this->dataColumn = $dataColumn;
        $this->defaultColors = $defaultColors ?: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6'];
        $this->colorMap = $colorMap;
        $this->chartType = 'donut';
        $this->chartHeight = 260;
    }

    /**
     * Set chart type (donut atau pie)
     */
    public function setChartType(string $type): self
    {
        $this->chartType = $type;

        return $this;
    }

    /**
     * Set chart height
     */
    public function setHeight(int $height): self
    {
        $this->chartHeight = $height;

        return $this;
    }

    /**
     * Get data untuk pie chart
     *
     * @return array{labels: array<string>, series: array<int>, colors: array<string>}
     */
    public function getData(): array
    {
        $rows = $this->query
            ->whereNotNull($this->dataColumn)
            ->selectRaw("{$this->dataColumn} as label, COUNT(*) as total")
            ->groupBy($this->dataColumn)
            ->orderByDesc('total')
            ->get();

        $labels = $rows->pluck('label')
            ->map(fn($v) => $v ?: 'Lainnya')
            ->toArray();

        $series = $rows->pluck('total')
            ->map(fn($v) => (int) $v)
            ->toArray();

        if (empty($labels)) {
            $labels = ['Tidak ada data'];
            $series = [0];
        }

        // Assign colors
        $colors = $this->assignColors($labels);

        return [
            'labels' => $labels,
            'series' => $series,
            'colors' => $colors,
        ];
    }

    /**
     * Assign colors untuk setiap label
     *
     * @param array<string> $labels
     * @return array<string>
     */
    protected function assignColors(array $labels): array
    {
        $colors = [];

        foreach ($labels as $label) {
            // Check apakah ada di color map
            if (isset($this->colorMap[$label])) {
                $colors[] = $this->colorMap[$label];
            } else {
                // Use default colors
                $colorIndex = count($colors) % count($this->defaultColors);
                $colors[] = $this->defaultColors[$colorIndex];
            }
        }

        return $colors;
    }

    /**
     * Build complete ApexCharts configuration
     *
     * @return array<string, mixed>
     */
    /**
     * @param array{labels: array<string>, series: array<int>, colors: array<string>}|null $data
     * @return array<string, mixed>
     */
    public function buildOptions(?array $data = null): array
    {
        $data = $data ?? $this->getData();

        return [
            'chart' => [
                'type' => $this->chartType,
                'height' => $this->chartHeight,
                'fontFamily' => 'inherit',

                'toolbar' => [
                    'show' => false,
                ],

                'animations' => [
                    'enabled' => true,
                    'speed' => 800,
                ],

                'dropShadow' => [
                    'enabled' => true,
                    'top' => 4,
                    'left' => 0,
                    'blur' => 10,
                    'opacity' => 0.10,
                ],
            ],

            'series' => $data['series'],

            'labels' => $data['labels'],

            'colors' => $data['colors'],

            'stroke' => [
                'width' => 2,
                'colors' => ['#ffffff'],
            ],

            'fill' => [
                'type' => 'solid',
            ],

            'plotOptions' => [
                'pie' => [
                    'expandOnClick' => true,

                    'offsetY' => 0,

                    'donut' => [
                        'size' => '40%',

                        'labels' => [
                            'show' => false,
                        ],
                    ],
                ],
            ],

            'dataLabels' => [
                'enabled' => true,

                'style' => [
                    'fontSize' => '10px',
                    'fontWeight' => 600,
                ],

                'dropShadow' => [
                    'enabled' => false,
                ],
            ],

            'legend' => [
                'show' => true,

                'position' => 'bottom',

                'horizontalAlign' => 'center',

                'fontSize' => '9px',

                'fontWeight' => 400,

                'labels' => [
                    'colors' => '#6b7280',
                ],

                'itemMargin' => [
                    'horizontal' => 12,
                    'vertical' => 8,
                ],

                'markers' => [
                    'width' => 10,
                    'height' => 10,
                    'radius' => 99,
                ],
            ],

            'tooltip' => [
                'theme' => 'dark',

                'fillSeriesColor' => false,
            ],

            'states' => [
                'hover' => [
                    'filter' => [
                        'type' => 'lighten',
                        'value' => 0.06,
                    ],
                ],

                'active' => [
                    'filter' => [
                        'type' => 'darken',
                        'value' => 0.10,
                    ],
                ],
            ],

            'responsive' => [
                [
                    'breakpoint' => 768,

                    'options' => [
                        'chart' => [
                            'height' => 320,
                        ],

                        'legend' => [
                            'fontSize' => '12px',
                        ],
                    ],
                ],
            ],
        ];
    }
}
