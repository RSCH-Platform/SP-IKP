<?php

namespace App\Filament\Widgets\Helpers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

/**
 * Menyediakan filter components untuk chart widgets
 */
class ChartFilterProvider
{
    /**
     * Build filter components untuk chart
     *
     * @param array<string> $defaultStatusFilter
     * @return array<\Filament\Forms\Components\Component>
     */
    public static function buildFilterComponents(
        array $defaultStatusFilter = ['investigasi', 'selesai_investigasi']
    ): array {
        $currentYear = (string) now()->year;

        return [
            Select::make('tahun')
                ->label('Tahun Laporan')
                ->native(false)
                ->options([$currentYear => $currentYear])
                ->default($currentYear)
                ->live(),

            Select::make('statusFilter')
                ->label('Status Laporan')
                ->native(false)
                ->multiple()
                ->options([
                    'draft' => 'Draft',
                    'dilaporkan' => 'Dilaporkan',
                    'revisi' => 'Perlu Revisi',
                    'revisi_unit' => 'Revisi Unit',
                    'diverifikasi' => 'Diverifikasi',
                    'investigasi' => 'Sedang Investigasi',
                    'selesai_investigasi' => 'Selesai Investigasi',
                ])
                ->default($defaultStatusFilter)
                ->live(),

            Toggle::make('showAverage')
                ->label('Tampilkan Garis Rata-rata')
                ->default(true)
                ->live(),
        ];
    }

    /**
     * Build simplified filter components (tanpa status filter)
     *
     * @return array<\Filament\Forms\Components\Component>
     */
    public static function buildSimpleFilterComponents(): array
    {
        $currentYear = (string) now()->year;

        return [
            Select::make('tahun')
                ->label('Tahun Laporan')
                ->native(false)
                ->options([$currentYear => $currentYear])
                ->default($currentYear)
                ->live(),

            Toggle::make('showAverage')
                ->label('Tampilkan Garis Rata-rata')
                ->default(true)
                ->live(),
        ];
    }
}
