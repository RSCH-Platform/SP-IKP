<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class ListLaporanInsidens extends ListRecords
{
    protected static string $resource = LaporanInsidenResource::class;

    protected function getHeaderActions(): array
    {
        // Avoid calling model-bound abilities (like `view`, `update`, etc.)
        // with the class string because that can route to the policy
        // method that expects a model instance and cause an
        // ArgumentCountError. Only check class-level abilities here.
        $modelClass = \App\Models\LaporanInsiden::class;
        $modelInstance = \App\Models\LaporanInsiden::first(); // atau dummy data
        $user = auth()->user();

        /**
         * Class-level abilities (tanpa instance)
         */
        $classAbilities = collect([
            'viewAny',
            'create',
            'deleteAny',
            'restoreAny',
            'forceDeleteAny',
            'reorder',
        ]);

        /**
         * Model-level abilities (butuh instance)
         */
        $modelAbilities = collect([
            'view',
            'update',
            'delete',
            'restore',
            'forceDelete',
            'replicate',

            // custom workflow kamu
            'submit',
            'verifikasi',
            'kembalikan',
            'investigasi',
            'kembalikanUnit',
        ]);

        $classResults = $classAbilities->mapWithKeys(fn($ability) => [
            $ability => Gate::forUser($user)->allows($ability, $modelClass),
        ]);

        $modelResults = $modelAbilities->mapWithKeys(fn($ability) => [
            $ability => $modelInstance
                ? Gate::forUser($user)->allows($ability, $modelInstance)
                : 'no_model_instance',
        ]);

        dd([
            'user' => $user->name,
            'model' => $modelClass,

            'class_level' => $classResults,
            'model_level' => $modelResults,

            'user_permissions_db' => $user->getAllPermissions()->pluck('name'),
        ]);

        // Use $abilityMap for debugging or conditional header actions.
        // For now, return the default header actions.
        return parent::getHeaderActions();
    }

    protected function baseQuery(): Builder
    {
        // Reuse resource query so counts and tabs follow the same access scope.
        return LaporanInsidenResource::getEloquentQuery();
    }

    protected function statusCount(string $status): int
    {
        return (clone $this->baseQuery())
            ->where('status', $status)
            ->count();
    }

    protected function canViewStatusTab(string $status): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return true;
        }

        return match ($status) {
            'draft', 'revisi' => ($user->can('Submit:LaporanInsiden') || $user->can('Create:LaporanInsiden')) && !$user->can('Verifikasi:LaporanInsiden'),
            'dilaporkan' => $user->can('Verifikasi:LaporanInsiden') || $user->can('Kembalikan:LaporanInsiden') || $user->can('Submit:LaporanInsiden'),
            'revisi_unit' => $user->can('Verifikasi:LaporanInsiden'),
            'diverifikasi', 'investigasi' => $user->can('Investigasi:LaporanInsiden') || $user->can('KembalikanUnit:LaporanInsiden'),
            default => $user->can('ViewAny:LaporanInsiden'),
        };
    }

    public function getTabs(): array
    {
        $statuses = [
            'draft' => ['label' => 'Draft', 'color' => 'gray'],
            'dilaporkan' => ['label' => 'Dilaporkan', 'color' => 'info'],
            'revisi_unit' => ['label' => 'Revisi Unit', 'color' => 'danger'],
            'revisi' => ['label' => 'Perlu Revisi', 'color' => 'warning'],
            'diverifikasi' => ['label' => 'Diverifikasi', 'color' => 'success'],
            'investigasi' => ['label' => 'Investigasi', 'color' => 'primary'],
        ];

        $tabs = [];

        foreach ($statuses as $status => $config) {
            if (! $this->canViewStatusTab($status)) {
                continue;
            }

            if (auth()->user()?->can('ViewAny:LaporanInsiden')) {
                $tabs['semua'] = Tab::make('Semua Laporan')
                    ->badge(fn() => $this->baseQuery()->count())
                    ->badgeColor('gray');
            }

            $tabs[$status] = Tab::make($config['label'])
                ->badge(fn() => $this->statusCount($status))
                ->badgeColor($config['color'])
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', $status)
                );
        }
        return $tabs;
    }
}
