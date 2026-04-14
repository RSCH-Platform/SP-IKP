<?php

namespace App\Filament\Widgets;

use App\Services\DashboardChartService;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class IncidentCategoryWidget extends Widget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Kategori Insiden Terbanyak';

    protected static ?string $description = 'Top 8 kategori insiden yang terjadi';

    protected string $view = 'filament.widgets.incident-category-widget';

    protected static ?int $sort = 2;

    public function getData()
    {
        $service = new DashboardChartService();
        return $service->getCategoryRanking();
    }
}
