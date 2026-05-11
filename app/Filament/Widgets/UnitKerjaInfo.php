<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class UnitKerjaInfo extends Widget
{
    use HasWidgetShield {
        canView as protected canViewShield;
    }

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.unit-kerja-info';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user
            && $user->unitKerjas()->exists();
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $unitKerja = $user->unitKerjas()->first();

        if (!$unitKerja) {
            return [
                'unitKerja' => null,
                'stats' => null,
            ];
        }

        // Get kepala unit (head of department)
        $kepalauUnit = $unitKerja->users()
            ->whereHas('roles', fn($q) => $q->where('name', 'kepala_unit'))
            ->first();

        // Count staff unit (excluding kepala_unit)
        $totalStaffUnit = $unitKerja->users()
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'kepala_unit'))
            ->count();

        // Get additional statistics
        $stats = [
            'kepala_unit_name' => $kepalauUnit?->name ?? '-',
            'total_staff_unit' => $totalStaffUnit,
            'total_users' => $unitKerja->users()->count(),
        ];

        return compact('unitKerja', 'stats');
    }
}
