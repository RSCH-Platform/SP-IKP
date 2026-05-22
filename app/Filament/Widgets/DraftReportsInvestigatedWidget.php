<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class DraftReportsInvestigatedWidget extends DraftReportsWidget
{
    protected static ?string $heading = 'Laporan Sudah Mulai Investigasi';

    protected static ?int $sort = 3;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('Submit:LaporanInsiden')
            || $user->can('ForceEdit:LaporanInsiden')
            || $user->can('ViewAllData:LaporanInsiden')
        );
    }

    protected function getTableQuery(): Builder
    {
        return $this->scopedQuery()
            ->whereNotNull('investigation_started_at')
            ->whereNotNull('investigation_started_by')
            ->latest('created_at');
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada laporan yang sudah investigasi';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Belum ada laporan dengan data mulai investigasi yang sudah terisi.';
    }
}
