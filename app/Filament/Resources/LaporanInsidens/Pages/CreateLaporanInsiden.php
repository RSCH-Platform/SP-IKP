<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateLaporanInsiden extends CreateRecord
{
    protected static string $resource = LaporanInsidenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        
        if (isset($data['user_id'])) {
            $user = \App\Models\User::find($data['user_id']);
            $data['nama_pelapor'] = $user?->name;
            $data['unit_kerja'] = \App\Models\UnitKerja::find($data['unit_kerja_id'] ?? null)?->unit_name;
        }
        
        $data['tanggal_lapor'] = $data['tanggal_lapor'] ?? now()->toDateString();

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('simpanDraft')
                ->label('Simpan sebagai Draft')
                ->color('gray')
                ->icon('heroicon-o-document')
                ->action(function () {
                    $this->record->status = LaporanInsiden::STATUS_DRAFT;
                    $this->save();
                }),

            Action::make('submit')
                ->label('Simpan & Kirim Laporan')
                ->color('warning')
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
