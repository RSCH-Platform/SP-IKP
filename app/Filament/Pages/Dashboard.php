<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AccountWidget;
use App\Filament\Widgets\DraftReportsStatsWidget;
use App\Filament\Widgets\DraftReportsWidget;
use App\Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\IncidentProblemReportGroupsWidget;
use App\Filament\Widgets\ManagerUnitKerjaAnalytics;
use App\Filament\Widgets\TrendLaporanInsiden;
use App\Filament\Widgets\UnitKerjaInfo;
use App\Models\LaporanInsiden;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    #[Url(as: 'dashboard-tab')]
    public string $dashboardTab = 'umum';

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    public function getGeneralWidgetsSchema(): Schema
    {
        return Schema::make($this)
            ->components([
                Grid::make($this->getColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getGeneralWidgets())),
            ]);
    }

    public function getIncidentWidgetsSchema(): Schema
    {
        return Schema::make($this)
            ->components([
                Grid::make($this->getColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getIncidentWidgets())),
            ]);
    }

    public function getInvestigationWidgetsSchema(): Schema
    {
        return Schema::make($this)
            ->components([
                Grid::make($this->getColumns())
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getInvestigationWidgets())),
            ]);
    }

    public function getIncidentUninvestigatedCount(): int
    {
        return (clone $this->scopedQuery())
            ->where('status', LaporanInsiden::STATUS_DIVERIFIKASI)
            ->whereNull('investigation_started_at')
            ->count();
    }

    public function getInvestigationInProgressCount(): int
    {
        return (clone $this->scopedQuery())
            ->where('status', LaporanInsiden::STATUS_INVESTIGASI)
            ->whereNotNull('investigation_started_at')
            ->whereNull('investigation_completed_at')
            ->count();
    }

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
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

    /** 
     * @return array<class-string>
     */
    protected function getGeneralWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class,
            DraftReportsStatsWidget::class,
            UnitKerjaInfo::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getIncidentWidgets(): array
    {
        return [
            DraftReportsWidget::class,
            IncidentProblemReportGroupsWidget::class,
            TrendLaporanInsiden::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getInvestigationWidgets(): array
    {
        return [
            ManagerUnitKerjaAnalytics::class,
        ];
    }
}