<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\ConditionalAuthenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        $panel = $panel
            ->default()
            ->id('ikp-application')
            ->path('ikp-application')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->viteTheme('resources/css/filament/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->globalSearch(false)
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->sidebarCollapsibleOnDesktop(true)
            ->widgets([
                \App\Filament\Widgets\AccountWidget::class,
                \App\Filament\Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\UnitKerjaInfo::class,
                \App\Filament\Widgets\LaporanInsidenReport::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentApexChartsPlugin::make(),
                \Juniyasyos\FilamentMediaManager\FilamentMediaManagerPlugin::make(),
                FilamentShieldPlugin::make()
                    ->navigationLabel('Manajemen Peran & Izin')
                    ->navigationIcon('heroicon-o-shield-check')
                    ->activeNavigationIcon('heroicon-s-shield-check')
                    ->navigationGroup('Administration')
                    ->navigationSort(100)
                    ->modelLabel('Peran')
                    ->pluralModelLabel('Peran')
                    ->recordTitleAttribute('name')
                    ->titleCaseModelLabel(false)
                    ->globallySearchable(true)
                    ->globalSearchResultsLimit(50)
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->registerNavigation(true),
            ])
            ->authMiddleware([
                ConditionalAuthenticate::class,
            ]);

        if (!$ssoEnabled) {
            $panel->login(\App\Filament\Pages\Auth\Login::class);
            $panel->registration(\App\Filament\Pages\Auth\Register::class);
        }

        return $panel;
    }
}
