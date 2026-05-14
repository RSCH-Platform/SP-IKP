<?php

namespace App\Http\Controllers;

use App\Filament\Widgets\Helpers\ChartQueryBuilder;
use App\Filament\Widgets\Helpers\PieChartBuilder;
use App\Filament\Widgets\Helpers\TrendChartBuilder;
use App\Filament\Widgets\LaporanInsidenReport;
use App\Filament\Widgets\ManagerUnitKerjaAnalytics;
use Illuminate\Http\Request;

class CustomLaporanInsidenDashboardController extends Controller
{
    /**
     * Render custom standalone incident dashboard page.
     */
    public function __invoke(Request $request)
    {
        $grouping = $request->string('grouping', 'quarter')->toString();
        if (!in_array($grouping, ['none', 'quarter', 'semester'], true)) {
            $grouping = 'quarter';
        }

        $period = (int) $request->integer('period', $this->defaultPeriod($grouping));
        if ($grouping === 'none') {
            $period = 0;
        }
        if ($grouping === 'semester') {
            $period = max(1, min(2, $period));
        }
        if ($grouping === 'quarter') {
            $period = max(1, min(4, $period));
        }

        $selectedStatuses = $request->input('statuses', []);
        if (!is_array($selectedStatuses)) {
            $selectedStatuses = [];
        }

        $reportWidget = app(LaporanInsidenReport::class);
        $availableYears = $reportWidget->getAvailableYears();
        if (empty($availableYears)) {
            $availableYears = [(int) now()->year];
        }

        $selectedYear = (int) $request->integer('year', (int) $availableYears[0]);
        if (!in_array($selectedYear, $availableYears, true)) {
            $selectedYear = (int) $availableYears[0];
        }

        $reportWidget->year = $selectedYear;
        $reportWidget->grouping = $grouping === 'semester' ? 'semester' : 'quarter';
        $reportWidget->period = $period;
        $reportWidget->statuses = $selectedStatuses;

        $jenisReport = $reportWidget->getReportDataJenisInsiden();
        $gradingReport = $reportWidget->getReportDataGrading();

        $managerWidget = app(ManagerUnitKerjaAnalytics::class);
        $managerWidget->year = $selectedYear;
        $managerWidget->grouping = $grouping === 'semester' ? 'semester' : 'quarter';
        $managerWidget->period = $period;
        $managerWidget->breakdownMode = $request->string('breakdown', 'period')->toString() === 'monthly'
            ? 'monthly'
            : 'period';
        $managerWidget->statuses = $this->defaultStatuses();

        $unitPerformanceRows = $managerWidget->getTable1UnitPerformance();
        $priorityRiskTables = $managerWidget->getTable4PriorityRiskBreakdowns();
        $priorityJenisColumns = $managerWidget->getTable4JenisColumns();
        $priorityGradingColumns = $managerWidget->getTable4GradingColumns();

        [$startMonth, $endMonth] = $this->resolveMonthRange($grouping, $period);

        $trendStatuses = !empty($selectedStatuses)
            ? $selectedStatuses
            : ['dilaporkan', 'diverifikasi', 'investigasi', 'selesai_investigasi'];

        $baseQuery = (new ChartQueryBuilder())
            ->withStatusFilter($trendStatuses)
            ->withYearFilter($selectedYear)
            ->withPeriodFilter($grouping, $period)
            ->build();

        $trendBuilder = new TrendChartBuilder(clone $baseQuery, $selectedYear, true, $startMonth, $endMonth);
        $trendSeries = $trendBuilder->getMonthlySeries();
        $trendCategories = $trendBuilder->getMonthCategories();
        $trendStats = $trendBuilder->calculateStats($trendSeries);

        $jenisPie = (new PieChartBuilder(
            clone $baseQuery,
            'jenis_insiden',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6']
        ))->getData();

        $gradingPie = (new PieChartBuilder(
            clone $baseQuery,
            'grading_risiko',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
            [
                'Biru' => '#3b82f6',
                'Hijau' => '#10b981',
                'Kuning' => '#f59e0b',
                'Merah' => '#ef4444',
            ]
        ))->getData();

        return view('pages.laporan-insiden-custom', [
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'grouping' => $grouping,
            'period' => $period,
            'periodOptions' => $this->periodOptions($grouping),
            'periodLabel' => $this->periodLabel($grouping, $period),
            'breakdown' => $managerWidget->breakdownMode,
            'statusOptions' => $this->defaultStatuses(),
            'selectedStatuses' => $selectedStatuses,
            'jenisReport' => $jenisReport,
            'gradingReport' => $gradingReport,
            'unitPerformanceRows' => $unitPerformanceRows,
            'priorityRiskTables' => $priorityRiskTables,
            'priorityJenisColumns' => $priorityJenisColumns,
            'priorityGradingColumns' => $priorityGradingColumns,
            'trendSeries' => $trendSeries,
            'trendCategories' => $trendCategories,
            'trendStats' => $trendStats,
            'jenisPie' => $jenisPie,
            'gradingPie' => $gradingPie,
        ]);
    }

    private function defaultStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'dilaporkan' => 'Dilaporkan',
            'diverifikasi' => 'Verifikasi',
            'investigasi' => 'Investigasi',
            'selesai_investigasi' => 'Selesai',
        ];
    }

    private function defaultPeriod(string $grouping): int
    {
        $month = (int) now()->month;

        if ($grouping === 'none') {
            return 0;
        }

        if ($grouping === 'semester') {
            return (int) ceil($month / 6);
        }

        return (int) ceil($month / 3);
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveMonthRange(string $grouping, int $period): array
    {
        if ($grouping === 'none' || $period <= 0) {
            return [1, 12];
        }

        if ($grouping === 'semester') {
            return $period === 2 ? [7, 12] : [1, 6];
        }

        return match ($period) {
            2 => [4, 6],
            3 => [7, 9],
            4 => [10, 12],
            default => [1, 3],
        };
    }

    private function periodLabel(string $grouping, int $period): string
    {
        if ($grouping === 'none' || $period === 0) {
            return 'Tahun Penuh';
        }

        if ($grouping === 'semester') {
            return 'Semester ' . $period;
        }

        return 'Quartal ' . $period;
    }

    private function periodOptions(string $grouping): array
    {
        if ($grouping === 'none') {
            return [0 => 'Tahun Penuh'];
        }

        if ($grouping === 'semester') {
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
}
