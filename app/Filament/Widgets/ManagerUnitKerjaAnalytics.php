<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use App\Models\LaporanInsiden;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ManagerUnitKerjaAnalytics extends Widget
{
    use WithPagination;
    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Allow access if user has permission and is manager/admin
        return $user->hasAnyPermission(['ViewAllData:LaporanInsiden']);
    }

    protected static ?int $sort = 30;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = '📊 Unit Kerja - Analisis Insiden Manager';

    protected ?string $description = 'Analisis mendalam performa unit kerja dengan breakdown jenis insiden dan risk stratification';

    protected string $view = 'filament.widgets.manager-unit-kerja-analytics';

    public ?int $year = null;

    public string $grouping = 'quarter';

    public ?int $period = null;

    public string $breakdownMode = 'period';

    public ?array $statuses = null;

    public int $unitPerPage = 10;
    public bool $showEmptyUnits = true;

    /**
     * Default statuses mapping (label only, no icons)
     * key => label
     */
    protected array $defaultStatuses = [
        'draft' => 'Draft',
        'dilaporkan' => 'Dilaporkan',
        'diverifikasi' => 'Verifikasi',
        'investigasi' => 'Investigasi',
        'selesai_investigasi' => 'Selesai',
    ];

    public function getTable4JenisColumns(): array
    {
        return [
            'KPC' => 'KPC',
            'KNC' => 'KNC',
            'KTC' => 'KTC',
            'KTD' => 'KTD',
            'Sentinel' => 'Sentinel',
        ];
    }

    public function getTable4GradingColumns(): array
    {
        return [
            'Biru' => 'Biru',
            'Hijau' => 'Hijau',
            'Kuning' => 'Kuning',
            'Merah' => 'Merah',
        ];
    }

    protected function getTable4OverdueThresholds(): array
    {
        return [
            'Biru' => 7,
            'Hijau' => 14,
            'Kuning' => 45,
            'Merah' => 45,
        ];
    }

    public function mount(): void
    {
        $this->year = $this->year ?? (int) date('Y');
        $this->period = $this->getDefaultPeriodForGrouping();
        $this->statuses = $this->statuses ?? $this->defaultStatuses;
    }

    public function updatedGrouping(): void
    {
        $this->period = $this->getDefaultPeriodForGrouping();
        $this->resetPage('unitPage');
    }

    public function updatedPeriod(): void
    {
        $this->resetPage('unitPage');
    }

    public function updatedBreakdownMode(): void
    {
        // No-op. Included so Livewire tracks the mode cleanly.
    }

    public function updatedUnitPerPage(): void
    {
        $this->resetPage('unitPage');
    }

    public function updatedShowEmptyUnits(): void
    {
        $this->resetPage('unitPage');
    }

    protected function getDefaultPeriodForGrouping(): int
    {
        $month = (int) date('n');

        if ($this->grouping === 'month') {
            return $month;
        }

        if ($this->grouping === 'semester') {
            return (int) ceil($month / 6);
        }

        return (int) ceil($month / 3);
    }

    public function getTable4PeriodMonths(): array
    {
        if ($this->grouping === 'semester') {
            return (int) $this->period === 2
                ? [
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                ]
                : [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                ];
        }

        return match ((int) $this->period) {
            2 => [
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
            ],
            3 => [
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
            ],
            4 => [
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ],
            default => [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
            ],
        };
    }

    protected function applyPeriodFilter($query, ?int $month = null): void
    {
        if ($this->year) {
            $query->whereYear('tanggal_insiden', $this->year);
        }

        if (!$this->period || $this->period <= 0) {
            return;
        }

        [$startMonth, $endMonth] = $this->resolveMonthRange();
        
        $query->where(function ($q) use ($startMonth, $endMonth) {
            for ($m = $startMonth; $m <= $endMonth; $m++) {
                $q->orWhereMonth('tanggal_insiden', $m);
            }
        });

        if ($month !== null) {
            $query->whereMonth('tanggal_insiden', $month);
        }
    }

    protected function resolveMonthRange(): array
    {
        if ($this->grouping === 'month') {
            return [(int) $this->period, (int) $this->period];
        }

        if ($this->grouping === 'semester') {
            return (int) $this->period === 2 ? [7, 12] : [1, 6];
        }

        return match ((int) $this->period) {
            2 => [4, 6],
            3 => [7, 9],
            4 => [10, 12],
            default => [1, 3],
        };
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

    // ===== TABLE 1: UNIT KERJA PERFORMANCE =====
    /**
     * Get performance data for all units
     */
    public function getTable1UnitPerformance(): array
    {
        $query = LaporanInsiden::query();
        $this->applyPeriodFilter($query);

        $unitQuery = UnitKerja::query()
            ->withCount(['laporanInsiden as total_insiden' => function ($q) {
                $q->whereNotNull('tanggal_insiden');
                $this->applyPeriodFilter($q);
            }])
            ->orderByDesc('total_insiden')
            ->orderBy('unit_name');

        if (!$this->showEmptyUnits) {
            $unitQuery->having('total_insiden', '>', 0);
        }

        $paginator = $unitQuery->paginate($this->unitPerPage, ['*'], 'unitPage');

        $rows = collect($paginator->items())->map(function ($unit) {
            $incidentsQuery = LaporanInsiden::query()
                ->where('unit_kerja_id', $unit->id)
                ->whereNotNull('tanggal_insiden');

            $this->applyPeriodFilter($incidentsQuery);
            $total = $incidentsQuery->count();

            // Calculate counts for each configured status key
            $statusCounts = [];
            foreach (array_keys($this->statuses) as $statusKey) {
                if ($statusKey === 'investigasi') {
                    $statusCounts[$statusKey] = (clone $incidentsQuery)->where('status', LaporanInsiden::STATUS_INVESTIGASI)->count();
                    continue;
                }

                if ($statusKey === 'selesai_investigasi') {
                    $statusCounts[$statusKey] = (clone $incidentsQuery)->where('status', LaporanInsiden::STATUS_SELESAI)->count();
                    continue;
                }

                $statusCounts[$statusKey] = (clone $incidentsQuery)->where('status', $statusKey)->count();
            }

            $selesaiCount = $statusCounts['selesai_investigasi'] ?? 0;
            $closeRate = $total > 0 ? round(($selesaiCount / $total) * 100, 0) : 0;

            $row = array_merge([
                'unit_name' => $unit->unit_name,
                'total' => $total,
            ], $statusCounts);
            $row['close_rate'] = $closeRate;

            return $row;
        })->toArray();

        return [
            'rows' => $rows,
            'paginator' => $paginator,
        ];
    }

    // ===== TABLE 2: UNIT KERJA x JENIS INSIDEN (By Grading) =====
    /**
     * Get breakdown by unit, jenis insiden, and grading
     */
    public function getTable2UnitJenisGrading(): array
    {
        $query = LaporanInsiden::query();
        $this->applyPeriodFilter($query);

        $gradingLevels = ['Biru', 'Hijau', 'Kuning', 'Merah'];
        $jenisTypes = ['KPC', 'KNC', 'KTC', 'KTD', 'Sentinel'];

        $data = [];

        $units = UnitKerja::query()
            ->whereHas('laporanInsiden', function ($q) use ($query) {
                $q->whereIn('id', $query->pluck('id'));
            })
            ->get();

        foreach ($units as $unit) {
            $unitQuery = LaporanInsiden::query()
                ->where('unit_kerja_id', $unit->id)
                ->whereNotNull('tanggal_insiden');

            $this->applyPeriodFilter($unitQuery);

            $unitData = [
                'unit_name' => $unit->unit_name,
                'items' => [],
                'subtotal' => ['Biru' => 0, 'Hijau' => 0, 'Kuning' => 0, 'Merah' => 0, 'total' => 0],
            ];

            foreach ($jenisTypes as $jenis) {
                $jenisQuery = (clone $unitQuery)->where('jenis_insiden', 'like', $jenis . '%');

                $gradingCounts = [];
                foreach ($gradingLevels as $grading) {
                    $count = (clone $jenisQuery)
                        ->where('grading_risiko', 'like', $grading . '%')
                        ->count();
                    $gradingCounts[$grading] = $count;
                    $unitData['subtotal'][$grading] += $count;
                }

                $total = array_sum($gradingCounts);

                // Skip jenis types with no data
                if ($total === 0) {
                    continue;
                }

                $unitData['subtotal']['total'] += $total;

                $unitData['items'][] = [
                    'jenis' => $jenis,
                    'Biru' => $gradingCounts['Biru'],
                    'Hijau' => $gradingCounts['Hijau'],
                    'Kuning' => $gradingCounts['Kuning'],
                    'Merah' => $gradingCounts['Merah'],
                    'total' => $total,
                ];
            }

            if ($unitData['subtotal']['total'] > 0) {
                $data[] = $unitData;
            }
        }

        return $data;
    }

    // ===== TABLE 3: JENIS INSIDEN x GRADING (All Units) =====
    /**
     * Get agregat distribution by jenis insiden and grading
     */
    public function getTable3JenisGradingAgregat(): array
    {
        $query = LaporanInsiden::query();
        $this->applyPeriodFilter($query);

        $gradingLevels = ['Biru', 'Hijau', 'Kuning', 'Merah'];
        $jenisTypes = ['KPC', 'KNC', 'KTC', 'KTD', 'Sentinel'];

        $rows = [];
        $totals = ['Biru' => 0, 'Hijau' => 0, 'Kuning' => 0, 'Merah' => 0, 'total' => 0];

        foreach ($jenisTypes as $jenis) {
            $jenisQuery = (clone $query)->where('jenis_insiden', 'like', $jenis . '%');

            $gradingCounts = [];
            foreach ($gradingLevels as $grading) {
                $count = (clone $jenisQuery)
                    ->where('grading_risiko', 'like', $grading . '%')
                    ->count();
                $gradingCounts[$grading] = $count;
                $totals[$grading] += $count;
            }

            $total = array_sum($gradingCounts);

            // Skip jenis types with no data
            if ($total === 0) {
                continue;
            }

            $totals['total'] += $total;
            $rows[] = [
                'jenis' => $jenis,
                'Biru' => $gradingCounts['Biru'],
                'Hijau' => $gradingCounts['Hijau'],
                'Kuning' => $gradingCounts['Kuning'],
                'Merah' => $gradingCounts['Merah'],
                'total' => $total,
                'percentage' => 0, // Will be calculated after loop
            ];
        }

        // Calculate percentages for each row after knowing grand total
        foreach ($rows as &$row) {
            $row['percentage'] = $totals['total'] > 0 ? round(($row['total'] / $totals['total']) * 100, 1) : 0;
        }

        // Calculate percentages for totals
        $totalPercentage = 100;

        return [
            'rows' => $rows,
            'totals' => [
                'Biru' => $totals['Biru'],
                'Hijau' => $totals['Hijau'],
                'Kuning' => $totals['Kuning'],
                'Merah' => $totals['Merah'],
                'total' => $totals['total'],
                'percentage' => $totalPercentage,
            ]
        ];
    }

    // ===== TABLE 4: PRIORITY RISK & ESCALATION =====
    /**
     * Calculate risk score and return priority ranking
     */
    public function getTable4PriorityRisk(?int $month = null): array
    {
        $query = LaporanInsiden::query();
        $this->applyPeriodFilter($query, $month);

        $units = UnitKerja::query()
            ->whereHas('laporanInsiden', function ($q) use ($query) {
                $q->whereIn('id', $query->pluck('id'));
            })
            ->get();

        $unitRisks = [];

        foreach ($units as $unit) {
            $unitQuery = LaporanInsiden::query()
                ->where('unit_kerja_id', $unit->id)
                ->whereNotNull('tanggal_insiden');

            $this->applyPeriodFilter($unitQuery, $month);

            $total = $unitQuery->count();
            if ($total === 0)
                continue;

            // Calculate metrics
            $jenisColumns = $this->getTable4JenisColumns();
            $gradingColumns = $this->getTable4GradingColumns();

            $jenisCounts = [];
            foreach (array_keys($jenisColumns) as $jenisKey) {
                $jenisCounts[$jenisKey] = (clone $unitQuery)->where('jenis_insiden', 'like', $jenisKey . '%')->count();
            }

            $gradingCounts = [];
            foreach (array_keys($gradingColumns) as $gradingKey) {
                $gradingCounts[$gradingKey] = (clone $unitQuery)->where('grading_risiko', 'like', $gradingKey . '%')->count();
            }

            $severeImpact = (clone $unitQuery)
                ->whereIn('dampak_insiden', ['Cedera berat', 'Meninggal'])
                ->count();
            $selesai = (clone $unitQuery)->where('status', LaporanInsiden::STATUS_SELESAI)->count();
            $closeRate = round(($selesai / $total) * 100, 0);

            // Calculate average resolve days based on investigation timestamps
            $avgResolveDays = (clone $unitQuery)
                ->join('investigations', 'laporan_insidens.id', '=', 'investigations.laporan_insiden_id')
                ->whereNotNull('investigations.investigation_started_at')
                ->whereNotNull('investigations.investigation_completed_at')
                ->selectRaw('AVG(DATEDIFF(investigations.investigation_completed_at, investigations.investigation_started_at)) as avg_days')
                ->value('avg_days') ?? 0;
            $avgResolveDays = round($avgResolveDays, 1);

            // Count overdue with SLA per grading risk
            // Biru: > 7 days, Hijau: > 14 days, Kuning/Merah: > 45 days
            $overdueThresholds = $this->getTable4OverdueThresholds();
            $overdueBreakdown = [];
            foreach ($overdueThresholds as $grading => $thresholdDays) {
                $overdueBreakdown[$grading] = (clone $unitQuery)
                    ->whereHas('investigation', function ($q) use ($thresholdDays) {
                        $q->whereNotNull('investigation_started_at')
                            ->whereNull('investigation_completed_at')
                            ->whereRaw('DATEDIFF(NOW(), investigation_started_at) > ?', [$thresholdDays]);
                    })
                    ->where('grading_risiko', 'like', $grading . '%')
                    ->count();
            }
            $overdue = array_sum($overdueBreakdown);

            $sentinel = $jenisCounts['Sentinel'] ?? 0;
            $ktd = $jenisCounts['KTD'] ?? 0;
            $merah = $gradingCounts['Merah'] ?? 0;

            // Risk Score Formula
            // (Sentinel × 10) + (KTD × 6) + (Merah × 5) + (Dampak Berat/Meninggal × 8) + (Overdue × 4) + (Avg Resolve Days × 2) - (Close Rate % × 0.5)
            $riskScore = ($sentinel * 10)
                + ($ktd * 6)
                + ($merah * 5)
                + ($severeImpact * 8)
                + ($overdue * 4)
                + ($avgResolveDays * 2)
                - ($closeRate * 0.5);
            $riskScore = max(0, round($riskScore, 0)); // Ensure non-negative

            // Determine risk level and action
            if ($riskScore >= 90) {
                $riskLevel = '🚨 Critical';
                $action = 'Immediate Review';
            } elseif ($riskScore >= 70) {
                $riskLevel = '🔴 High';
                $action = 'Escalation';
            } elseif ($riskScore >= 40) {
                $riskLevel = '🟡 Medium';
                $action = 'Monitoring';
            } else {
                $riskLevel = '🟢 Low';
                $action = 'Stable';
            }

            $unitRisks[] = [
                'unit_name' => $unit->unit_name,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'jenis_counts' => $jenisCounts,
                'grading_counts' => $gradingCounts,
                'severe_impact' => $severeImpact,
                'overdue' => $overdue,
                'overdue_breakdown' => $overdueBreakdown,
                'avg_resolve_days' => $avgResolveDays,
                'close_rate' => $closeRate,
                'action' => $action,
            ];
        }

        // Sort by risk score descending
        usort($unitRisks, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        // Add rank
        foreach ($unitRisks as $key => &$item) {
            $rank = $key + 1;
            if ($rank === 1) {
                $item['rank'] = '1';
            } elseif ($rank === 2) {
                $item['rank'] = '2';
            } elseif ($rank === 3) {
                $item['rank'] = '3';
            } else {
                $item['rank'] = $rank;
            }
        }

        return $unitRisks;
    }

    public function getTable4PriorityRiskBreakdowns(): array
    {
        if ($this->breakdownMode !== 'monthly') {
            return [
                [
                    'title' => 'Akumulasi Periode',
                    'month' => null,
                    'rows' => $this->getTable4PriorityRisk(),
                ]
            ];
        }

        $tables = [];
        foreach ($this->getTable4PeriodMonths() as $monthValue => $monthLabel) {
            $tables[] = [
                'title' => $monthLabel,
                'month' => $monthValue,
                'rows' => $this->getTable4PriorityRisk($monthValue),
            ];
        }

        return $tables;
    }

    public function exportCSV()
    {
        $fileName = 'unit_performance_' . date('Ymd_His') . '.csv';
        
        $query = LaporanInsiden::query();
        $this->applyPeriodFilter($query);

        $unitQuery = UnitKerja::query()
            ->withCount(['laporanInsiden as total_insiden' => function ($q) {
                $q->whereNotNull('tanggal_insiden');
                $this->applyPeriodFilter($q);
            }])
            ->orderByDesc('total_insiden')
            ->orderBy('unit_name');

        if (!$this->showEmptyUnits) {
            $unitQuery->having('total_insiden', '>', 0);
        }

        $units = $unitQuery->get();
        
        $data = $units->map(function ($unit) {
            $incidentsQuery = LaporanInsiden::query()
                ->where('unit_kerja_id', $unit->id)
                ->whereNotNull('tanggal_insiden');

            $this->applyPeriodFilter($incidentsQuery);
            $total = $incidentsQuery->count();

            // Calculate counts for each configured status key
            $statusCounts = [];
            foreach (array_keys($this->statuses) as $statusKey) {
                if ($statusKey === 'investigasi') {
                    $statusCounts[$statusKey] = (clone $incidentsQuery)->where('status', LaporanInsiden::STATUS_INVESTIGASI)->count();
                    continue;
                }
                if ($statusKey === 'selesai_investigasi') {
                    $statusCounts[$statusKey] = (clone $incidentsQuery)->where('status', LaporanInsiden::STATUS_SELESAI)->count();
                    continue;
                }
                $statusCounts[$statusKey] = (clone $incidentsQuery)->where('status', $statusKey)->count();
            }

            $investigasiCount = $statusCounts['investigasi'] ?? 0;
            $closeRate = $total > 0 ? round(($investigasiCount / $total) * 100, 0) : 0;

            $row = array_merge([
                'unit_name' => $unit->unit_name,
                'total' => $total,
            ], $statusCounts);
            $row['close_rate'] = $closeRate;

            return $row;
        })->toArray();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Unit Kerja', 'Total Laporan', 'Draft', 'Dilaporkan', 'Grading', 'Investigasi', 'Selesai', 'Close Rate %'];

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['unit_name'] ?? '',
                    $row['total'] ?? 0,
                    $row['draft'] ?? 0,
                    $row['dilaporkan'] ?? 0,
                    $row['diverifikasi'] ?? 0,
                    $row['investigasi'] ?? 0,
                    $row['selesai_investigasi'] ?? 0,
                    $row['close_rate'] ?? 0
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
