<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Models
use App\Models\LaporanInsiden;
use App\Models\ProblemAction;
use App\Models\UnitKerja;
use App\Models\TimelineEntry;

// Observers
use App\Observers\LaporanInsidenObserver;
use App\Observers\ProblemActionObserver;
use App\Observers\UnitKerjaObserver;
use App\Observers\TimelineEntryObserver;

// Policies
use App\Policies\FolderPolicy;
use App\Policies\MediaPolicy;

// Third Party
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * =========================================================
     * REGISTER SERVICES
     * =========================================================
     */
    public function register(): void
    {
        //
    }

    /**
     * =========================================================
     * BOOTSTRAP SERVICES
     * =========================================================
     */
    public function boot(): void
    {
        $this->registerModelPolicies();
        $this->registerMediaLibraryConfig();
        $this->registerObservers();
    }

    /**
     * =========================================================
     * AUTO REGISTER MODEL POLICIES (WITH DEBUG)
     * =========================================================
     */
    protected function registerModelPolicies(): void
    {
        $modelsPath = app_path('Models');

        $results = collect(glob($modelsPath . '/*.php'))
            ->map(fn($file) => $this->resolveModelPolicyPair($file))
            ->map(function ($pair) {
                return [
                    ...$pair,
                    'model_exists' => class_exists($pair['model']),
                    'policy_exists' => class_exists($pair['policy']),
                ];
            })
            ->map(function ($pair) {
                if ($pair['model_exists'] && $pair['policy_exists']) {
                    Gate::policy($pair['model'], $pair['policy']);
                    $pair['registered'] = true;
                } else {
                    $pair['registered'] = false;
                }

                return $pair;
            });

        // // 🔥 DEBUG SEMUA HASIL
        // dd([
        //     'total_models_scanned' => $results->count(),
        //     'registered_policies' => $results->where('registered', true)->values(),
        //     'failed' => $results->where('registered', false)->values(),
        //     'raw' => $results,
        // ]);
    }

    /**
     * Resolve model & policy class from file
     */
    protected function resolveModelPolicyPair(string $file): array
    {
        $name = pathinfo($file, PATHINFO_FILENAME);

        return [
            'model' => "App\\Models\\{$name}",
            'policy' => "App\\Policies\\{$name}Policy",
        ];
    }

    /**
     * Validate existence of model & policy
     */
    protected function isValidPolicyPair(array $pair): bool
    {
        return class_exists($pair['model']) && class_exists($pair['policy']);
    }

    /**
     * =========================================================
     * MEDIA LIBRARY CONFIGURATION
     * =========================================================
     */
    protected function registerMediaLibraryConfig(): void
    {
        config([
            'media-library.custom_path_generators' => array_merge(
                config('media-library.custom_path_generators', []),
                [
                    ProblemAction::class => \App\Support\ProblemActionMediaPathGenerator::class,
                ]
            ),
        ]);
    }

    /**
     * =========================================================
     * MODEL OBSERVERS
     * =========================================================
     */
    protected function registerObservers(): void
    {
        ProblemAction::observe(ProblemActionObserver::class);
        UnitKerja::observe(UnitKerjaObserver::class);
        LaporanInsiden::observe(LaporanInsidenObserver::class);
        TimelineEntry::observe(TimelineEntryObserver::class);
    }
}
