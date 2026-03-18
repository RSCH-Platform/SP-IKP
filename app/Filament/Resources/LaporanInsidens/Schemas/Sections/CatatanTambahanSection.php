<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class CatatanTambahanSection
{
    public static function make(): Section
    {
        return Section::make('📎 CATATAN TAMBAHAN')
            ->description('Informasi tambahan (opsional)')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Textarea::make('catatan_tambahan')
                    ->label('Catatan Tambahan')
                    ->rows(5)
                    ->helperText('(Opsional) Informasi tambahan yang belum tercakup di bagian sebelumnya')
                    ->placeholder('Tuliskan informasi tambahan jika diperlukan')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->compact();
    }
}
