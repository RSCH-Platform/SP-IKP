<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use App\Models\LaporanInsiden;

class LaporanInsidenReport extends Widget
{
    use HasWidgetShield;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ringkasan Laporan Unit Kerja';

    protected ?string $description = 'Ringkasan status laporan insiden unit kerja Anda, termasuk draft, proses, selesai, dan laporan yang perlu tindak lanjut prioritas.';

    protected string $view = 'filament.widgets.laporan-insiden-report';

    public ?int $year = null;

    public string $grouping = 'quarter'; // 'quarter' or 'semester'

    public function mount(): void
    {
        $this->year = $this->year ?? (int)date('Y');
        $this->period = $this->getDefaultPeriodForGrouping();
    }

    // selected quarter (1..4) or semester (1..2); null = all
    public ?int $period = null;

    public function updatedGrouping(): void
    {
        $this->period = $this->getDefaultPeriodForGrouping();
    }

    protected function getDefaultPeriodForGrouping(): int
    {
        $month = (int) date('n');

        if ($this->grouping === 'semester') {
            return (int) ceil($month / 6);
        }

        return (int) ceil($month / 3);
    }

    public function getAvailableYears(): array
    {
        return LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden')
            ->selectRaw('YEAR(tanggal_insiden) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn($y) => (int) $y)
            ->toArray();
    }

    /**
     * Build and return report rows grouped by quarter or semester using Eloquent.
     * Returns array of ['period' => string, 'count' => int]
     */
    public function getReportData(): array
    {
        $yearFilter = $this->year ? intval($this->year) : null;
        $grouping = $this->grouping === 'semester' ? 'semester' : 'quarter';
        $periodFilter = $this->period ? intval($this->period) : $this->getDefaultPeriodForGrouping();

        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($yearFilter) {
            $query->whereYear('tanggal_insiden', $yearFilter);
        }

        $monthsNames = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        // Map incidents into period/month buckets
        $items = $query->get()->map(function ($record) use ($grouping) {
            $month = $record->tanggal_insiden->month;
            $year = $record->tanggal_insiden->year;

            if ($grouping === 'quarter') {
                $period_num = (int)ceil($month / 3);
                $period = "{$year}-Q{$period_num}";
            } else {
                $period_num = (int)ceil($month / 6);
                $period = "{$year}-S{$period_num}";
            }

            return [
                'year' => $year,
                'period' => $period,
                'period_num' => $period_num,
                'month' => $month,
            ];
        });

        // Filter by selected period (quarter/semester) if provided
        if ($periodFilter) {
            $items = $items->filter(fn($it) => $it['period_num'] === $periodFilter);
        }

        $startMonth = 1;
        $endMonth = 12;

        if ($periodFilter) {
            if ($grouping === 'quarter') {
                $startMonth = (($periodFilter - 1) * 3) + 1;
                $endMonth = $startMonth + 2;
            } else {
                $startMonth = (($periodFilter - 1) * 6) + 1;
                $endMonth = $startMonth + 5;
            }
        }

        $countByMonth = $items->groupBy('month')->map->count();

        $reportRows = collect(range($startMonth, $endMonth))->map(function (int $month) use ($countByMonth, $monthsNames) {
            return [
                'month' => $month,
                'month_label' => $monthsNames[$month - 1] ?? '',
                'count' => (int) ($countByMonth[$month] ?? 0),
            ];
        })->values();

        return $reportRows->toArray();
    }

    /**
     * Get a formatted label for the current period
     */ 
    public function periodeLabel(): string
    {
        if ($this->grouping === 'quarter') {
            return "Quartal {$this->period}";
        }

        return "Semester {$this->period}";
    }
}
