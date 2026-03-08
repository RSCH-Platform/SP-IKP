<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanInsiden extends CreateRecord
{
    protected static string $resource = LaporanInsidenResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('simpanDraft')
                ->label('Simpan sebagai Draft')
                ->color('gray')
                ->submit()
                ->icon('heroicon-o-document')
                ->action(function () {
                    $this->record->status = LaporanInsiden::STATUS_DRAFT;
                    $this->save();
                }),

            Action::make('submit')
                ->label('Simpan & Kirim Laporan')
                ->color('warning')
                ->submit()
                ->icon('heroicon-o-paper-airplane')
                ->action(function () {
                    $this->record->status = LaporanInsiden::STATUS_DRAFT;
                    $this->save();
                    $this->record->submitLaporan();
                    redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                }),
        ];
    }
}
