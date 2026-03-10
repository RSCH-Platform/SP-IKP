<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DraftReportsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Query untuk mendapatkan total draft reports
        $query = LaporanInsiden::where('status', LaporanInsiden::STATUS_DRAFT);

        // Jika user tidak punya permission ViewAllData, filter berdasarkan unit kerja mereka
        if (!$user->can('viewAllData', LaporanInsiden::class)) {
            $userUnitIds = $user->unitKerja()->pluck('id');
            $query->whereIn('unit_kerja_id', $userUnitIds);
        }

        $totalDraft = $query->count();

        // Query untuk laporan yang dibuat hari ini (dalam status draft)
        $queryToday = LaporanInsiden::where('status', LaporanInsiden::STATUS_DRAFT)
            ->whereDate('created_at', today());

        if (!$user->can('viewAllData', LaporanInsiden::class)) {
            $userUnitIds = $user->unitKerja()->pluck('id');
            $queryToday->whereIn('unit_kerja_id', $userUnitIds);
        }

        $draftToday = $queryToday->count();

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
