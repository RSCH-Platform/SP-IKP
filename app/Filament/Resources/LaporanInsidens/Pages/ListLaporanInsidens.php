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
        $user = auth()->user();
        $unitKerja = $user->unitKerja;

        $query = $this->getModel()::query();

        return $query;
    }

    protected function statusCount(string $status): int
    {
        return (clone $this->baseQuery())
            ->where('status', $status)
            ->count();
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
            $tabs[$status] = Tab::make($config['label'])
                ->badge(fn() => $this->statusCount($status))
                ->badgeColor($config['color'])
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', $status)
                );
        }

        $tabs['semua'] = Tab::make('Semua Laporan')
            ->badge(fn() => $this->baseQuery()->count())
            ->badgeColor('gray');

        return $tabs;
    }
}
