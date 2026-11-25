<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Helpers\PieChartBuilder;
use App\Models\LaporanInsiden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class IncidentCategoryMonitoringWidget extends ApexChartWidget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.incident-category-monitoring';

    protected static ?string $chartId = 'incidentCategoryMonitoringChart';

    protected static ?string $heading = 'Kategori Insiden';

    protected static ?string $subheading = 'Distribusi insiden berdasarkan kategori.';

    protected static bool $deferLoading = false;

    protected static ?int $sort = 10;

    public string $periodType = 'year';

    public ?int $selectedYear = null;

    public ?int $selectedMonth = null;

    public ?int $startMonth = null;

    public ?int $endMonth = null;

    public ?int $selectedQuarter = 1;

    public ?int $selectedSemester = 1;

    protected int|string|array $columnSpan = 'full';

    public int $pieChartHeight = 380;

    public function mount(): void
    {
        $this->periodType = 'year';
        $this->selectedYear = (int) now()->year;
        $this->selectedMonth = (int) now()->month;
        $this->startMonth = 1;
        $this->endMonth = (int) now()->month;
        $this->selectedQuarter = (int) ceil(now()->month / 3);
        $this->selectedSemester = now()->month <= 6 ? 1 : 2;

        parent::mount();
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user !== null && (
            $user->can('ViewAllData:LaporanInsiden') ||
            $user->can('ForceEdit:LaporanInsiden')
        );
    }

    public function updatedPeriodType(): void
    {
        if ($this->periodType === 'month_range') {
            $this->startMonth = $this->startMonth ?: 1;
            $this->endMonth = $this->endMonth ?: (int) now()->month;
        } elseif ($this->periodType === 'month') {
            $this->selectedMonth = $this->selectedMonth ?: (int) now()->month;
        } elseif ($this->periodType === 'quarter') {
            $this->selectedQuarter = $this->selectedQuarter ?: (int) ceil(now()->month / 3);
        } elseif ($this->periodType === 'semester') {
            $this->selectedSemester = $this->selectedSemester ?: (now()->month <= 6 ? 1 : 2);
        }

        $this->dispatch('refresh-charts');
    }

    public function updatedSelectedYear(): void
    {
        $this->dispatch('refresh-charts');
    }

    public function updatedSelectedMonth(): void
    {
        $this->dispatch('refresh-charts');
    }

    public function updatedStartMonth(): void
    {
        if ($this->startMonth && $this->endMonth && $this->startMonth > $this->endMonth) {
            $this->endMonth = $this->startMonth;
        }
        $this->dispatch('refresh-charts');
    }

    public function updatedEndMonth(): void
    {
        if ($this->startMonth && $this->endMonth && $this->endMonth < $this->startMonth) {
            $this->startMonth = $this->endMonth;
        }
        $this->dispatch('refresh-charts');
    }

    public function updatedSelectedQuarter(): void
    {
        $this->dispatch('refresh-charts');
    }

    public function updatedSelectedSemester(): void
    {
        $this->dispatch('refresh-charts');
    }

    protected function getBaseQuery(): Builder
    {
        $query = LaporanInsiden::query();

        if (filled($this->selectedYear)) {
            $query->whereYear('tanggal_insiden', (int) $this->selectedYear);
        }

        match ($this->periodType) {
            'month' => filled($this->selectedMonth)
                ? $query->whereMonth('tanggal_insiden', (int) $this->selectedMonth)
                : $query,
            'month_range' => (filled($this->startMonth) && filled($this->endMonth))
                ? $query->whereMonth('tanggal_insiden', '>=', min((int)$this->startMonth, (int)$this->endMonth))
                        ->whereMonth('tanggal_insiden', '<=', max((int)$this->startMonth, (int)$this->endMonth))
                : $query,
            'quarter' => match ((int) $this->selectedQuarter) {
                1 => $query->whereMonth('tanggal_insiden', '>=', 1)->whereMonth('tanggal_insiden', '<=', 3),
                2 => $query->whereMonth('tanggal_insiden', '>=', 4)->whereMonth('tanggal_insiden', '<=', 6),
                3 => $query->whereMonth('tanggal_insiden', '>=', 7)->whereMonth('tanggal_insiden', '<=', 9),
                4 => $query->whereMonth('tanggal_insiden', '>=', 10)->whereMonth('tanggal_insiden', '<=', 12),
                default => $query,
            },
            'semester' => match ((int) $this->selectedSemester) {
                1 => $query->whereMonth('tanggal_insiden', '>=', 1)->whereMonth('tanggal_insiden', '<=', 6),
                2 => $query->whereMonth('tanggal_insiden', '>=', 7)->whereMonth('tanggal_insiden', '<=', 12),
                default => $query,
            },
            default => $query,
        };

        $user = Auth::user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return $query;
        }

        if ($user->can('ForceEdit:LaporanInsiden')) {
            $unitIds = $user->unitKerjas()->pluck('id');
            return $query->where(function (Builder $q) use ($unitIds) {
                $q->whereIn('unit_kerja_id', $unitIds)
                  ->orWhere('status', LaporanInsiden::STATUS_SELESAI);
            });
        }

        if ($user->can('Investigasi:LaporanInsiden')) {
            return $query;
        }

        if ($user->can('Submit:LaporanInsiden')) {
            return $query->where('user_id', $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    public function getCategoryOptions(): array
    {
        $baseQuery = clone $this->getBaseQuery();
        
        $builder = new PieChartBuilder(
            $baseQuery,
            'kategori_insiden',
            ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16']
        );
        
        $builder->setHeight($this->pieChartHeight);

        $data = $builder->getData();
        return $builder->buildOptions($data);
    }
    
    protected function getOptions(): array
    {
        return [];
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

        return $years ?: [(int) now()->year];
    }

    public function getPeriodTypeOptions(): array
    {
        return [
            'year' => 'Tahunan',
            'month' => 'Bulanan',
            'month_range' => 'Rentang Bulan',
            'quarter' => 'Kuartal',
            'semester' => 'Semester',
        ];
    }

    public function getQuarterOptions(): array
    {
        return [
            1 => 'Kuartal 1 (Jan - Mar)',
            2 => 'Kuartal 2 (Apr - Jun)',
            3 => 'Kuartal 3 (Jul - Sep)',
            4 => 'Kuartal 4 (Okt - Des)',
        ];
    }

    public function getSemesterOptions(): array
    {
        return [
            1 => 'Semester 1 (Jan - Jun)',
            2 => 'Semester 2 (Jul - Des)',
        ];
    }

    public function getMonthOptions(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }
    
    public function getTableData(): array
    {
        $baseQuery = clone $this->getBaseQuery();
        
        $data = $baseQuery
            ->whereNotNull('kategori_insiden')
            ->selectRaw('kategori_insiden, COUNT(*) as total')
            ->groupBy('kategori_insiden')
            ->orderByDesc('total')
            ->get();
            
        $totalAll = $data->sum('total');
        
        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'kategori' => $row->kategori_insiden ?: 'Lainnya',
                'total' => $row->total,
                'persentase' => $totalAll > 0 ? round(($row->total / $totalAll) * 100, 1) : 0,
            ];
        }
        
        return [
            'rows' => $result,
            'totalAll' => $totalAll,
        ];
    }
}
