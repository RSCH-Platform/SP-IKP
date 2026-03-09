<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLaporanInsidens extends ListRecords
{
    protected static string $resource = LaporanInsidenResource::class;

    protected function getHeaderActions(): array
    {
        return [];
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
            'draft', 'revisi' => $user->can('Submit:LaporanInsiden') || $user->can('Create:LaporanInsiden'),
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

            $tabs[$status] = Tab::make($config['label'])
                ->badge(fn() => $this->statusCount($status))
                ->badgeColor($config['color'])
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', $status)
                );
        }

        if (auth()->user()?->can('ViewAny:LaporanInsiden')) {
            $tabs['semua'] = Tab::make('Semua Laporan')
                ->badge(fn() => $this->baseQuery()->count())
                ->badgeColor('gray');
        }

        return $tabs;
    }
}
