<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLaporanInsiden extends EditRecord
{
    protected static string $resource = LaporanInsidenResource::class;

    protected string $view = 'filament.resources.laporan-insidens.pages.edit-laporan-insiden';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->visible(fn() => $this->record->created_by === auth()->id() && in_array($this->record->status, [
                'draft',
                'revisi',
            ])),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        $actions = [];

        // Tombol simpan biasa (untuk status draft, revisi, revisi_unit)
        if (in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI, LaporanInsiden::STATUS_REVISI_UNIT])) {
            $actions[] = Action::make('save')
                ->label('Simpan Perubahan')
                ->color('gray')
                ->icon('heroicon-o-check');
        }

        // Workflow actions sesuai status
        if ($this->record->status === LaporanInsiden::STATUS_DRAFT && auth()->user()->can('Submit:LaporanInsiden')) {
            $actions[] = Action::make('submitLaporan')
                ->label('Kirim Laporan')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan Insiden?')
                ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi. Pastikan semua data sudah lengkap.')
                ->icon('heroicon-o-paper-airplane')
                ->action(function () {
                    $this->save();
                    $this->record->submitLaporan();

                    $kepalaUnits = User::role('kepala_unit')->get();
                    if ($kepalaUnits->isNotEmpty()) {
                        Notification::make()
                            ->title('Laporan Insiden Baru')
                            ->body("Ada laporan insiden baru dari {$this->record->nama_pelapor} yang perlu diverifikasi.")
                            ->warning()
                            ->sendToDatabase($kepalaUnits);
                    }

                    Notification::make()
                        ->title('Laporan berhasil dikirim')
                        ->success()
                        ->send();

                    redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                });
        }

        if ($this->record->status === LaporanInsiden::STATUS_REVISI && auth()->user()->can('Submit:LaporanInsiden')) {
            $actions[] = Action::make('resubmit')
                ->label('Kirim Ulang Laporan')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Kirim Ulang Laporan Insiden?')
                ->modalDescription('Laporan yang sudah diperbaiki akan dikirim kembali ke kepala unit.')
                ->icon('heroicon-o-paper-airplane')
                ->action(function () {
                    $this->save();
                    $this->record->submitLaporan();

                    $kepalaUnits = User::role('kepala_unit')->get();
                    if ($kepalaUnits->isNotEmpty()) {
                        Notification::make()
                            ->title('Laporan Insiden Diperbaiki')
                            ->body("Laporan dari {$this->record->nama_pelapor} telah diperbaiki dan dikirim kembali.")
                            ->warning()
                            ->sendToDatabase($kepalaUnits);
                    }

                    Notification::make()
                        ->title('Laporan berhasil dikirim')
                        ->success()
                        ->send();

                    redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                });
        }

        return $actions;
    }
}
