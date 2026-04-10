<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        $panel = $panel
            ->default()
            ->id('')
            ->path('')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->viteTheme('resources/css/filament/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->sidebarCollapsibleOnDesktop(true)
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                Authenticate::class,
            ]);

        if (!$ssoEnabled) {
            $panel->login(\App\Filament\Pages\Auth\Login::class);
            $panel->registration(\App\Filament\Pages\Auth\Register::class);
        }

        return $panel;
    }
}
