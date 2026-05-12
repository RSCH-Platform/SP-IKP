<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use App\Models\LaporanInsiden;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\Auth;

class ManagerUnitKerjaAnalytics extends Widget
{
    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Allow access if user has permission and is manager/admin
        return $user->hasAnyPermission(['ForceEdit:LaporanInsiden']);
    }

    protected static ?int $sort = 30;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = '📊 Unit Kerja - Analisis Insiden Manager';

    protected ?string $description = 'Analisis mendalam performa unit kerja dengan breakdown jenis insiden dan risk stratification';

    protected string $view = 'filament.widgets.manager-unit-kerja-analytics';

    public ?int $year = null;

    public string $grouping = 'quarter';

    public ?int $period = null;

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

    // ===== TABLE 1: UNIT KERJA PERFORMANCE =====
    /**
     * Get performance data for all units
     */
    public function getTable1UnitPerformance(): array
    {
        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($this->year) {
            $query->whereYear('tanggal_insiden', $this->year);
        }

        // Get all units dengan data incidents
        $units = UnitKerja::query()
            ->whereHas('laporanInsiden', function ($q) use ($query) {
                $q->whereIn('id', $query->pluck('id'));
            })
            ->get();

        $rows = $units->map(function ($unit) {
            $unitQuery = LaporanInsiden::query()
                ->where('unit_kerja_id', $unit->id)
                ->whereNotNull('tanggal_insiden');

            if ($this->year) {
                $unitQuery->whereYear('tanggal_insiden', $this->year);
            }

            $total = $unitQuery->count();

            // Skip units with no data
            if ($total === 0) {
                return null;
            }

            $draft = (clone $unitQuery)->where('status', 'draft')->count();
            $proses = (clone $unitQuery)->whereIn('status', ['dilaporkan', 'diverifikasi', 'investigasi', 'revisi', 'revisi_unit'])->count();
            $selesai = (clone $unitQuery)->where('status', 'selesai')->count();

            $closeRate = $total > 0 ? round(($selesai / $total) * 100, 0) : 0;

            // Determine risk level
            if ($closeRate >= 85) {
                $riskLevel = '🟢 Low';
            } elseif ($closeRate >= 70) {
                $riskLevel = '🟡 Medium';
            } else {
                $riskLevel = '🔴 High';
            }

            return [
                'unit_name' => $unit->unit_name,
                'total' => $total,
                'draft' => $draft,
                'proses' => $proses,
                'selesai' => $selesai,
                'close_rate' => $closeRate,
                'risk_level' => $riskLevel,
            ];
        })->filter()->sortByDesc('total')->values()->toArray();

        return $rows;
    }

    // ===== TABLE 2: UNIT KERJA x JENIS INSIDEN (By Grading) =====
    /**
     * Get breakdown by unit, jenis insiden, and grading
     */
    public function getTable2UnitJenisGrading(): array
    {
        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($this->year) {
            $query->whereYear('tanggal_insiden', $this->year);
        }

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

            if ($this->year) {
                $unitQuery->whereYear('tanggal_insiden', $this->year);
            }

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
        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($this->year) {
            $query->whereYear('tanggal_insiden', $this->year);
        }

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
    public function getTable4PriorityRisk(): array
    {
        $query = LaporanInsiden::query()
            ->whereNotNull('tanggal_insiden');

        if ($this->year) {
            $query->whereYear('tanggal_insiden', $this->year);
        }

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

            if ($this->year) {
                $unitQuery->whereYear('tanggal_insiden', $this->year);
            }

            $total = $unitQuery->count();
            if ($total === 0) continue;

            // Calculate metrics
            $sentinel = (clone $unitQuery)->where('jenis_insiden', 'like', 'Sentinel%')->count();
            $merah = (clone $unitQuery)->where('grading_risiko', 'like', 'Merah%')->count();
            $selesai = (clone $unitQuery)->where('status', 'selesai')->count();
            $closeRate = round(($selesai / $total) * 100, 0);

            // Calculate average resolve days (simplified - using created/updated timestamps)
            $avgResolveDays = (clone $unitQuery)
                ->where('status', 'selesai')
                ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
                ->value('avg_days') ?? 0;
            $avgResolveDays = round($avgResolveDays, 1);

            // Count overdue (> 7 days without status selesai)
            $overdue = (clone $unitQuery)
                ->where('status', '!=', 'selesai')
                ->whereRaw('DATEDIFF(NOW(), created_at) > 7')
                ->count();

            // Risk Score Formula
            // (Sentinel × 10) + (Merah × 5) + (Overdue × 4) + (Avg Resolve Days × 2) - (Close Rate % × 0.5)
            $riskScore = ($sentinel * 10) + ($merah * 5) + ($overdue * 4) + ($avgResolveDays * 2) - ($closeRate * 0.5);
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
                'sentinel' => $sentinel,
                'merah' => $merah,
                'overdue' => $overdue,
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
                $item['rank'] = '🥇';
            } elseif ($rank === 2) {
                $item['rank'] = '🥈';
            } elseif ($rank === 3) {
                $item['rank'] = '🥉';
            } else {
                $item['rank'] = $rank;
            }
        }

        return $unitRisks;
    }
}
