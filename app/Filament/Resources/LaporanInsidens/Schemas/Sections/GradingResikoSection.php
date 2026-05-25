<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;

class GradingResikoSection
{
    public static function make(): Section
    {
        return Section::make('BAGIAN F: GRADING RESIKO')
            ->description('Penilaian tingkat risiko insiden berdasarkan dampak yang terjadi.')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema([
                ToggleButtons::make('grading_risiko')
                    ->label('Pilih Grading Risiko')
                    ->required()
                    ->options([
                        'Biru' => '🔵 Biru (Tidak signifikan)',
                        'Hijau' => '🟢 Hijau (Minor)',
                        'Kuning' => '🟡 Kuning (Moderat)',
                        'Merah' => '🔴 Merah (Mayor)',
                        'Hitam' => '⚫ Hitam (Katastropik)',
                    ])
                    ->colors([
                        'Biru' => 'info',
                        'Hijau' => 'success',
                        'Kuning' => 'warning',
                        'Merah' => 'danger',
                        'Hitam' => 'gray',
                    ])
                    ->helperText('Hanya dapat diisi oleh Validator atau Tim IKP.')
                    ->inline()
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->collapsible()
            ->compact()
            ->visibleOn('edit');
    }
}