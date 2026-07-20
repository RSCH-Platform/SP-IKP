<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DraftReportsInvestigatedWidget;
use App\Filament\Widgets\IncidentProblemReportGroupsWidget;
use App\Filament\Widgets\InvestigatedReportsTableWidget;
use App\Filament\Widgets\ManagerUnitKerjaAnalytics;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class InvestigasiPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'Investigasi';

    protected static ?string $title = 'Investigasi';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.investigasi-page';

    #[Url(as: 'tab', except: 'laporan_aktif')]
    public string $investigasiTab = 'laporan_aktif';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && (
            $user->can('ViewAllData:LaporanInsiden')
            || $user->can('ForceEdit:LaporanInsiden')
            || $user->can('Investigasi:LaporanInsiden')
            || $user->can('Submit:LaporanInsiden')
        );
    }

    public function getAvailableTabs(): array
    {
        $tabs = [
            'laporan_aktif' => [
                'label'       => 'Laporan Aktif',
                'description' => 'Laporan yang sudah mulai investigasi.',
                'icon'        => 'heroicon-o-arrow-path',
                'tone'        => 'blue',
                'widget'      => DraftReportsInvestigatedWidget::class,
                'visible'     => DraftReportsInvestigatedWidget::canView(),
            ],
            'pemantauan' => [
                'label'       => 'Pemantauan',
                'description' => 'Tabel investigasi beserta akar masalah dan rekomendasi.',
                'icon'        => 'heroicon-o-table-cells',
                'tone'        => 'violet',
                'widget'      => InvestigatedReportsTableWidget::class,
                'visible'     => InvestigatedReportsTableWidget::canView(),
            ],
            'kelompok_masalah' => [
                'label'       => 'Kelompok Masalah',
                'description' => 'Pengelompokan masalah dan tindakan unit kerja.',
                'icon'        => 'heroicon-o-puzzle-piece',
                'tone'        => 'amber',
                'widget'      => IncidentProblemReportGroupsWidget::class,
                'visible'     => IncidentProblemReportGroupsWidget::canView(),
            ],
            'analitik_unit' => [
                'label'       => 'Analitik Unit',
                'description' => 'Analisis mendalam performa dan risiko unit kerja.',
                'icon'        => 'heroicon-o-chart-bar',
                'tone'        => 'green',
                'widget'      => ManagerUnitKerjaAnalytics::class,
                'visible'     => ManagerUnitKerjaAnalytics::canView(),
            ],
        ];

        return array_filter($tabs, fn(array $tab) => $tab['visible']);
    }
}
