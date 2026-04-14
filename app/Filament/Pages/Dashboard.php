<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\IncidentStatusWidget;
use App\Filament\Widgets\IncidentCategoryWidget;
use App\Filament\Widgets\RiskGradingWidget;
use App\Filament\Widgets\IncidentTrendWidget;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasPageShield;

    public function getWidgets(): array
    {
        return [
            IncidentStatusWidget::class,
            IncidentCategoryWidget::class,
            RiskGradingWidget::class,
            IncidentTrendWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'md' => 2,
            'lg' => 4,
        ];
    }
}
