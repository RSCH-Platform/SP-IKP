<?php

namespace App\Filament\Widgets;

use App\Models\LaporanInsiden;
use App\Models\ProblemAction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvestigationStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Status Investigasi';

    protected ?string $description = 'Ringkasan progres investigasi dan tindakan yang masih harus dikerjakan.';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('ViewAllData:LaporanInsiden')
            || $user->can('ForceEdit:LaporanInsiden')
            || $user->can('Submit:LaporanInsiden')
            || $user->can('Investigasi:LaporanInsiden')
        );
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

        if ($user->can('Investigasi:LaporanInsiden')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    protected function getStats(): array
    {
        if (! Auth::user()) {
            return [];
        }

        $ongoingCount = (clone $this->scopedQuery())
            ->where('status', LaporanInsiden::STATUS_INVESTIGASI)
            ->whereNotNull('investigation_started_at')
            ->whereNull('investigation_completed_at')
            ->count();

        $completedCount = (clone $this->scopedQuery())
            ->where('status', LaporanInsiden::STATUS_SELESAI)
            ->whereNotNull('investigation_completed_at')
            ->count();

        $actionRequiredCount = ProblemAction::query()
            ->where('status', ['pending', 'ongoing'])
            ->whereHas('problem.incident', function (Builder $query): void {
                $this->applyAccessScope($query);
            })
            ->count();

        return [
            Stat::make('Investigasi Berjalan', $ongoingCount)
                ->description('Sedang diproses dan belum selesai')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('Investigasi Selesai', $completedCount)
                ->description('Sudah ditutup dan tercatat selesai')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Tindakan Harus Dilakukan', $actionRequiredCount)
                ->description('Tindakan investigasi masih berstatus pending/ongoing')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),
        ];
    }

    protected function applyAccessScope(Builder $query): Builder
    {
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

        if ($user->can('Investigasi:LaporanInsiden')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }
}