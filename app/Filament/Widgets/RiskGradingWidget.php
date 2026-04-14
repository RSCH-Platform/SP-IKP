<?php

namespace App\Filament\Widgets;

use App\Services\DashboardChartService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class RiskGradingWidget extends Widget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Distribusi Grading Risiko';

    protected static ?string $description = 'Klasifikasi risiko berdasarkan dampak potensial';

    protected string $view = 'filament.widgets.risk-grading-widget';

    protected static ?int $sort = 3;

    public function getData()
    {
        $service = new DashboardChartService();
        return $service->getRiskGradingDistribution();
    }
}
