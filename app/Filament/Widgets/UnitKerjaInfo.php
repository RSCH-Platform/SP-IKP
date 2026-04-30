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

        // Get additional statistics
        $stats = [
            'total_users' => $unitKerja->users()->count(),
        ];

        return compact('unitKerja', 'stats');
    }
}
