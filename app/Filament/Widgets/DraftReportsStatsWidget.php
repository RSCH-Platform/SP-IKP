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

    protected ?string $heading = 'Ringkasan Laporan Unit Kerja';

    protected ?string $description = 'Lihat ringkasan status laporan insiden unit kerja Anda: draft, proses, selesai, dan laporan yang perlu perhatian prioritas.';

    protected function scopedQuery(): Builder
    {
        $query = LaporanInsiden::query();
        $user = Auth::user();

        if (! $user) {
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

        if (! $user) {
            return [];
        }

        $now = Carbon::now();
        $pendingStatuses = [
            LaporanInsiden::STATUS_DILAPORKAN,
            LaporanInsiden::STATUS_REVISI,
            LaporanInsiden::STATUS_REVISI_UNIT,
        ];
        $inProgressStatuses = [
            LaporanInsiden::STATUS_DILAPORKAN,
            LaporanInsiden::STATUS_REVISI,
            LaporanInsiden::STATUS_REVISI_UNIT,
            LaporanInsiden::STATUS_INVESTIGASI,
        ];

        $draftCount = (clone $baseQuery)->where('status', LaporanInsiden::STATUS_DRAFT)->count();
        $reportedCount = (clone $baseQuery)->where('status', '!=', LaporanInsiden::STATUS_DRAFT)->count();
        $pendingCount = (clone $baseQuery)->whereIn('status', $pendingStatuses)->count();
        $verifiedCount = (clone $baseQuery)->where('status', LaporanInsiden::STATUS_DIVERIFIKASI)->count();
        $investigationCount = (clone $baseQuery)->where('status', LaporanInsiden::STATUS_INVESTIGASI)->count();
        $rejectedCount = (clone $baseQuery)->whereIn('status', [LaporanInsiden::STATUS_REVISI, LaporanInsiden::STATUS_REVISI_UNIT])->count();

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
                ->description('Saat ini masih dalam draft')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('success'),

            Stat::make('Dalam Proses', $pendingCount)
                ->description('Menunggu verifikasi / revisi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Selesai', $verifiedCount + $investigationCount)
                ->visible(!$user->can('Submit:LaporanInsiden') || $user->can('ForceEdit:LaporanInsiden'))
                ->description('Diverifikasi / Investigasi selesai')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),

            Stat::make('Overdue > 7 hari', $overdueCount)
                ->description('Pendekatan SLA, prioritaskan penyelesaian')
                ->visible(!$user->can('Submit:LaporanInsiden') || $user->can('ForceEdit:LaporanInsiden'))
                ->descriptionIcon('heroicon-m-flag')
                ->color('danger'),
        ];

        // Tetap tampilkan 4 metrik utama (semua role) agar lebih ringkas dan mudah dibaca.
        return $stats;
    }
}
