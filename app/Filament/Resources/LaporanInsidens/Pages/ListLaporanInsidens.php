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
        return [
            // CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'draft' => Tab::make('Draft')
                ->badge(fn() => $this->getModel()::where('status', 'draft')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'draft')
                ),

            'dilaporkan' => Tab::make('Dilaporkan')
                ->badge(fn() => $this->getModel()::where('status', 'dilaporkan')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'dilaporkan')
                ),

            'revisi' => Tab::make('Perlu Revisi')
                ->badge(fn() => $this->getModel()::where('status', 'revisi')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'revisi')
                ),

            'diverifikasi' => Tab::make('Diverifikasi')
                ->badge(fn() => $this->getModel()::where('status', 'diverifikasi')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'diverifikasi')
                ),

            'revisi_unit' => Tab::make('Revisi Unit')
                ->badge(fn() => $this->getModel()::where('status', 'revisi_unit')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'revisi_unit')
                ),

            'investigasi' => Tab::make('Investigasi')
                ->badge(fn() => $this->getModel()::where('status', 'investigasi')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('status', 'investigasi')
                ),

            'semua' => Tab::make('Semua Laporan')
                ->badge(fn() => $this->getModel()::count())
                ->badgeColor('gray'),
        ];
    }
}
