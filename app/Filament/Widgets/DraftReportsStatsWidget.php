<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DraftReportsStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;
    protected ?string $heading = 'Status Laporan Insiden';
    protected ?string $description = 'Ringkasan laporan berdasarkan tahap penanganan dan prioritas tindak lanjut.';
    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // no access
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

        // fallback: no data
        return $query->whereRaw('1 = 0');
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $baseQuery = $this->scopedQuery();

        if (!$user) {
            return [];
        }

        $now = Carbon::now();
        $pendingStatuses = [
            LaporanInsiden::STATUS_DILAPORKAN,
            LaporanInsiden::STATUS_REVISI,
            LaporanInsiden::STATUS_REVISI_UNIT,
        ];

        $draftCount = (clone $baseQuery)->where('status', LaporanInsiden::STATUS_DRAFT)->count();
        $pendingCount = (clone $baseQuery)->whereIn('status', $pendingStatuses)->count();
        $verifiedCount = (clone $baseQuery)->where('status', LaporanInsiden::STATUS_DIVERIFIKASI)->count();
        
        $overdueCount = (clone $baseQuery)
            ->whereIn('status', $pendingStatuses)
            ->whereNotNull('reported_at')
            ->where('reported_at', '<', $now->copy()->subDays(7))
            ->count();

        $avgResolutionDays = (clone $baseQuery)
            ->whereNotNull('reported_at')
            ->whereNotNull('investigation_completed_at')
            ->where('investigation_completed_at', '>', 'reported_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, reported_at, investigation_completed_at)) AS avg_days')
            ->value('avg_days');

        $stats = [
            Stat::make('Draft', $draftCount)
                ->description('Belum dikirim untuk diproses')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('success'),

            Stat::make('Perlu Tindak Lanjut', $pendingCount)
                ->description('Menunggu verifikasi atau revisi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Terverifikasi', $verifiedCount)
                ->description('Laporan sudah melewati verifikasi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),

            Stat::make('Lewat 7 Hari', $overdueCount)
                ->description('Prioritaskan agar tidak tertunda')
                ->descriptionIcon('heroicon-m-flag')
                ->color('danger'),
        ];

        return $stats;
    }
}
