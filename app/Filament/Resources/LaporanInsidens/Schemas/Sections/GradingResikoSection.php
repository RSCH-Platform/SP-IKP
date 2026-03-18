<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Forms\Components\ToggleButtons;

class GradingResikoSection
{
    public static function make(): ToggleButtons
    {
        return ToggleButtons::make('grading_risiko')
            ->label('Grading Risiko')
            ->required()
            ->options([
                'Biru'   => '🔵 Biru (Tidak signifikan)',
                'Hijau'  => '🟢 Hijau (Minor)',
                'Kuning' => '🟡 Kuning (Moderat)',
                'Merah'  => '🔴 Merah (Mayor)',
                'Hitam'  => '⚫ Hitam (Katastropik)',
            ])
            ->colors([
                'Biru'   => 'info',
                'Hijau'  => 'success',
                'Kuning' => 'warning',
                'Merah'  => 'danger',
                'Hitam'  => 'gray',
            ])
            ->inline()
            ->helperText('Hanya diisi oleh Validator / Tim IKP')
            ->visibleOn('edit');
    }
}
