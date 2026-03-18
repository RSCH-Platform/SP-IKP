<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenFormOptions;
use Filament\Schemas\Components\Section;

class StatusSection
{
    public static function make(): Section
    {
        return Section::make('📌 Status Laporan')
            ->icon('heroicon-o-check-circle')
            ->schema([
                LaporanInsidenFormOptions::makeSelect('status', 'Status Laporan', LaporanInsidenFormOptions::STATUS_OPTIONS)
                    ->default('draft')
                    ->required(),
            ])
            ->visibleOn('edit');
    }
}
