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

    public ?int $selectedYear = null;

    public ?int $selectedMonth = null;

    protected int|string|array $columnSpan = 'full';

    public int $pieChartHeight = 380;

    public function mount(): void
    {
        $this->selectedYear = (int) now()->year;
        $this->selectedMonth = null;

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

    public function updatedSelectedYear(): void
    {
        $this->dispatch('refresh-charts');
    }

    public function updatedSelectedMonth(): void
    {
        $this->dispatch('refresh-charts');
    }

    protected function getBaseQuery(): Builder
    {
        $query = LaporanInsiden::query()
            ->when(
                filled($this->selectedYear),
                fn(Builder $query): Builder => $query->whereYear('tanggal_insiden', (int) $this->selectedYear)
            )
            ->when(
                filled($this->selectedMonth),
                fn(Builder $query): Builder => $query->whereMonth('tanggal_insiden', (int) $this->selectedMonth)
            );

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
