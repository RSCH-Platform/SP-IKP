<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use App\Models\LaporanInsiden;
use Illuminate\Support\Facades\Auth;

class LaporanInsidenReport extends Widget
{
    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Allow access if user has permission to view incident reports
        return $user->hasAnyPermission(['ForceEdit:LaporanInsiden']);
    }

    protected array $jenisInsidenColumns = [
        'KPC' => 'KPC (Kondisi Potensial Cedera)',
        'KNC' => 'KNC (Kejadian Nyaris Cedera)',
        'KTC' => 'KTC (Kejadian Tidak Cedera)',
        'KTD' => 'KTD (Kejadian Tidak Diharapkan)',
        'Sentinel' => 'Sentinel',
    ];

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Ringkasan Laporan Unit Kerja';

    protected ?string $description = 'Ringkasan status laporan insiden unit kerja Anda, termasuk draft, proses, selesai, dan laporan yang perlu tindak lanjut prioritas.';

    protected string $view = 'filament.widgets.laporan-insiden-report';

    public ?int $year = null;

    public string $grouping = 'quarter'; // 'quarter' or 'semester'

    // selected quarter (1..4) or semester (1..2); null = all
    public ?int $period = null;

    // selected statuses for filtering; null or empty = all
    public ?array $statuses = null;

    public function mount(): void
    {
        $this->year = $this->year ?? (int)date('Y');
        $this->period = $this->getDefaultPeriodForGrouping();
        $this->statuses = $this->statuses ?? [];
    }

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
    public function getReportDataJenisInsiden(): array
    {
        $yearFilter = $this->year ? intval($this->year) : null;
        $grouping = $this->grouping === 'semester' ? 'semester' : 'quarter';
        $periodFilter = $this->period ? intval($this->period) : $this->getDefaultPeriodForGrouping();

        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($yearFilter) {
            $query->whereYear('tanggal_insiden', $yearFilter);
        }

        // Filter by selected statuses if provided
        if (!empty($this->statuses) && is_array($this->statuses)) {
            $query->whereIn('status', $this->statuses);
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

        // Map incidents into period/month/type buckets
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
                'jenis_insiden_key' => $this->normalizeJenisInsidenKey($record->jenis_insiden),
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

        $countByMonthAndJenis = $items
            ->groupBy(fn(array $item) => $item['month'] . '|' . ($item['jenis_insiden_key'] ?? ''))
            ->map->count();

        $reportRows = collect(range($startMonth, $endMonth))->map(function (int $month) use ($countByMonthAndJenis, $monthsNames, $items) {
            $monthItems = $items->where('month', $month);

            $row = [
                'month' => $month,
                'month_label' => $monthsNames[$month - 1] ?? '',
                'total_count' => $monthItems->count(),
            ];

            foreach (array_keys($this->jenisInsidenColumns) as $jenisKey) {
                $countKey = $month . '|' . $jenisKey;
                $count = (int) ($countByMonthAndJenis[$countKey] ?? 0);

                $row[$jenisKey] = $count;
                $row['total_count'] += $count;
            }

            return $row;
        })->values();

        // Calculate summary totals
        $summary = [
            'KPC' => 0,
            'KNC' => 0,
            'KTC' => 0,
            'KTD' => 0,
            'Sentinel' => 0,
            'total_count' => 0,
        ];

        foreach ($reportRows as $row) {
            foreach (array_keys($this->jenisInsidenColumns) as $jenisKey) {
                $summary[$jenisKey] += $row[$jenisKey] ?? 0;
            }
            $summary['total_count'] += $row['total_count'] ?? 0;
        }

        return [
            'rows' => $reportRows->toArray(),
            'summary' => $summary,
        ];
    }

    protected function normalizeJenisInsidenKey(?string $jenisInsiden): string
    {
        return match (true) {
            is_string($jenisInsiden) && str_starts_with($jenisInsiden, 'KPC') => 'KPC',
            is_string($jenisInsiden) && str_starts_with($jenisInsiden, 'KNC') => 'KNC',
            is_string($jenisInsiden) && str_starts_with($jenisInsiden, 'KTC') => 'KTC',
            is_string($jenisInsiden) && str_starts_with($jenisInsiden, 'KTD') => 'KTD',
            is_string($jenisInsiden) && str_starts_with($jenisInsiden, 'Sentinel') => 'Sentinel',
            default => 'UNKNOWN',
        };
    }

    public function getJenisInsidenColumns(): array
    {
        return $this->jenisInsidenColumns;
    }

    /**
     * Build and return report rows grouped by quarter or semester for grading risk.
     */
    public function getReportDataGrading(): array
    {
        $yearFilter = $this->year ? intval($this->year) : null;
        $grouping = $this->grouping === 'semester' ? 'semester' : 'quarter';
        $periodFilter = $this->period ? intval($this->period) : $this->getDefaultPeriodForGrouping();

        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($yearFilter) {
            $query->whereYear('tanggal_insiden', $yearFilter);
        }

        if (!empty($this->statuses) && is_array($this->statuses)) {
            $query->whereIn('status', $this->statuses);
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
                'grading_key' => $this->normalizeGradingKey($record->grading_risiko),
            ];
        });

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

        $countByMonthAndGrading = $items
            ->groupBy(fn(array $item) => $item['month'] . '|' . ($item['grading_key'] ?? ''))
            ->map->count();

        $gradingKeys = ['Biru', 'Hijau', 'Kuning', 'Merah', 'Hitam'];

        $reportRows = collect(range($startMonth, $endMonth))->map(function (int $month) use ($countByMonthAndGrading, $monthsNames, $items, $gradingKeys) {
            $monthItems = $items->where('month', $month);

            $row = [
                'month' => $month,
                'month_label' => $monthsNames[$month - 1] ?? '',
                'total_count' => $monthItems->count(),
            ];

            foreach ($gradingKeys as $g) {
                $countKey = $month . '|' . $g;
                $count = (int) ($countByMonthAndGrading[$countKey] ?? 0);
                $row[$g] = $count;
                $row['total_count'] += $count;
            }

            return $row;
        })->values();

        $summary = array_fill_keys($gradingKeys, 0) + ['total_count' => 0];

        foreach ($reportRows as $row) {
            foreach ($gradingKeys as $g) {
                $summary[$g] += $row[$g] ?? 0;
            }
            $summary['total_count'] += $row['total_count'] ?? 0;
        }

        return [
            'rows' => $reportRows->toArray(),
            'summary' => $summary,
        ];
    }

    protected function normalizeGradingKey(?string $grading): string
    {
        if (!is_string($grading)) {
            return 'UNKNOWN';
        }

        return match (true) {
            str_contains($grading, 'Biru') => 'Biru',
            str_contains($grading, 'Hijau') => 'Hijau',
            str_contains($grading, 'Kuning') => 'Kuning',
            str_contains($grading, 'Merah') => 'Merah',
            str_contains($grading, 'Hitam') => 'Hitam',
            default => 'UNKNOWN',
        };
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
