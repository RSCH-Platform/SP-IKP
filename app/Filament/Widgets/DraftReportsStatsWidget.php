<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DraftReportsStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    /**
     * Return a query builder scoped to the current user's permissions/units.
     */
    protected function scopedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = LaporanInsiden::query();

        if (!auth()->user()?->can('viewAllData', LaporanInsiden::class)) {
            $unitIds = auth()->user()->unitKerja()->pluck('id');
            $query->whereIn('unit_kerja_id', $unitIds);
        }

        return $query;
    }

    protected function getStats(): array
    {
        // count reports that are already submitted/processed (anything except draft)
        $completedBase = $this->scopedQuery()->where('status', LaporanInsiden::STATUS_DRAFT);
        $totalCompleted = $completedBase->count();

        // count all reports
        $totalTerlaporkan = $this->scopedQuery()->where('status', '!=', LaporanInsiden::STATUS_DRAFT)->count();

        return [
            Stat::make('Total Draft Laporan', $totalCompleted)
                ->description('Semua laporan yang sedang dalam draft')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total IKP yang dilaporkan', $totalTerlaporkan)
                ->description('Jumlah keseluruhan laporan yang ada')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }
}
