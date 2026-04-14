<?php

namespace App\Filament\Widgets;

use App\Services\DashboardChartService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class IncidentTrendWidget extends Widget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Trend Insiden (12 Bulan Terakhir)';

    protected static ?string $description = 'Pola dan perubahan jumlah insiden setiap bulannya';

    protected string $view = 'filament.widgets.incident-trend-widget';

    protected static ?int $sort = 4;

    public function getData()
    {
        $service = new DashboardChartService();
        return $service->getMonthlyTrend();
    }
}
