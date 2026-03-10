<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DraftReportsStatsWidget;
use App\Filament\Widgets\DraftReportsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Beranda';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            DraftReportsStatsWidget::class,
            DraftReportsWidget::class,
        ];
    }

    public function getColumns(): array | int
    {
        return 12;
    }
}
