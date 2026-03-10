<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DraftReportsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static function shouldRender(): bool
    {
        return auth()->check() && auth()->user()->can('viewWidget:LaporanStatsWidget');
    }

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
        $base = $this->scopedQuery()->where('status', LaporanInsiden::STATUS_DRAFT);

        $totalDraft = (clone $base)->count();
        $draftToday = (clone $base)->whereDate('created_at', today())->count();

        return [
            Stat::make('Total Laporan Draft', $totalDraft)
                ->description('Laporan menunggu untuk dilaporkan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->icon('heroicon-m-clipboard-document-list'),

            Stat::make('Draft Hari Ini', $draftToday)
                ->description('Laporan baru dibuat hari ini')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info')
                ->icon('heroicon-m-calendar'),
        ];
    }
}
