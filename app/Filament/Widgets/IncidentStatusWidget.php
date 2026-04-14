<?php

namespace App\Filament\Widgets;

use App\Services\DashboardChartService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class IncidentStatusWidget extends Widget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Status Laporan';

    protected static ?string $description = 'Distribusi status laporan insiden';

    protected string $view = 'filament.widgets.incident-status-widget';

    protected static ?int $sort = 1;

    public function getData()
    {
        $service = new DashboardChartService();
        return $service->getStatusDistribution();
    }
}
