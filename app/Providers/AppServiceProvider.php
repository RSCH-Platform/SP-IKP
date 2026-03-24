<?php

namespace App\Providers;

use App\Models\LaporanInsiden;
use App\Models\ProblemAction;
use App\Models\UnitKerja;
use App\Observers\LaporanInsidenObserver;
use App\Observers\ProblemActionObserver;
use App\Observers\UnitKerjaObserver;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Redirect role policy to use the built-in RolePolicy
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            return str_replace('Models', 'Policies', $modelClass) . 'Policy';
        });

        // Spatie MediaLibrary paths for ProblemAction files:
        // {Unit Kerja}/Laporan Insiden/{YYYY-MM}/{Nomor laporan}/...
        config([
            'media-library.custom_path_generators' => array_merge(
                config('media-library.custom_path_generators', []),
                [\App\Models\ProblemAction::class => \App\Support\ProblemActionMediaPathGenerator::class]
            ),
        ]);

        ProblemAction::observe(ProblemActionObserver::class);
        UnitKerja::observe(UnitKerjaObserver::class);
        LaporanInsiden::observe(LaporanInsidenObserver::class);
    }
}
