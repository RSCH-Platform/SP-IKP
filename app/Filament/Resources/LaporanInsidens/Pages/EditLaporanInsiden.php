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
use Illuminate\Support\Facades\Log;

class EditLaporanInsiden extends EditRecord
{
    protected static string $resource = LaporanInsidenResource::class;

    // protected string $view = 'filament.resources.laporan-insidens.pages.edit-laporan-insiden';

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
                ->icon('heroicon-o-check')
                ->action(function () {
                    try {
                        Log::info('[EditLaporanInsiden] Save action triggered', [
                            'record_id' => $this->record->id,
                            'status' => $this->record->status,
                            'user_id' => auth()->id(),
                        ]);

                        $this->save();

                        Log::info('[EditLaporanInsiden] Data saved successfully', [
                            'record_id' => $this->record->id,
                        ]);

                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Log::error('[EditLaporanInsiden] Save error: ' . $e->getMessage(), [
                            'record_id' => $this->record->id,
                            'error' => $e,
                        ]);

                        Notification::make()
                            ->title('Gagal menyimpan data')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
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
                ->before(function () {
                    file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] submitLaporan BEFORE called\n", FILE_APPEND);
                })
                ->action(function () {
                    try {
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] submitLaporan ACTION started\n", FILE_APPEND);

                        Log::info('[EditLaporanInsiden] submitLaporan action triggered', [
                            'record_id' => $this->record->id,
                            'status' => $this->record->status,
                            'user_id' => auth()->id(),
                            'user_name' => auth()->user()->name,
                        ]);

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Before save\n", FILE_APPEND);
                        $this->save();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] After save\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] Form data saved');

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Before refresh\n", FILE_APPEND);
                        $this->record->refresh();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] After refresh\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] Record refreshed');

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Before submitLaporan\n", FILE_APPEND);
                        $this->record->submitLaporan();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] After submitLaporan, new status: " . $this->record->status . "\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] submitLaporan() executed', [
                            'new_status' => $this->record->status,
                        ]);

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Fetching kepalaUnits\n", FILE_APPEND);
                        $kepalaUnits = User::role('kepala_unit')->get();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] kepalaUnits count: " . $kepalaUnits->count() . "\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] kepalaUnits fetched', [
                            'count' => $kepalaUnits->count(),
                        ]);

                        if ($kepalaUnits->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Insiden Baru')
                                ->body("Ada laporan insiden baru dari {$this->record->nama_pelapor} yang perlu diverifikasi.")
                                ->warning()
                                ->sendToDatabase($kepalaUnits);

                            file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Notification sent to database\n", FILE_APPEND);
                            Log::info('[EditLaporanInsiden] Notification sent to kepala_unit', [
                                'count' => $kepalaUnits->count(),
                            ]);
                        }

                        Notification::make()
                            ->title('Laporan berhasil dikirim')
                            ->success()
                            ->send();

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Success notification sent\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] Success notification sent to user');

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] Before redirect\n", FILE_APPEND);
                        redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                    } catch (\Exception $e) {
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
                        Log::error('[EditLaporanInsiden] submitLaporan error: ' . $e->getMessage(), [
                            'record_id' => $this->record->id,
                            'trace' => $e->getTraceAsString(),
                        ]);

                        Notification::make()
                            ->title('Gagal mengirim laporan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
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
                    try {
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit ACTION started\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] resubmit action triggered', [
                            'record_id' => $this->record->id,
                            'status' => $this->record->status,
                            'user_id' => auth()->id(),
                        ]);

                        $this->save();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit: Form data saved\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] Form data saved for resubmit');

                        $this->record->refresh();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit: Record refreshed\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] Record refreshed for resubmit');

                        $this->record->submitLaporan();
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit: submitLaporan executed, new status: " . $this->record->status . "\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] resubmit submitLaporan() executed', [
                            'new_status' => $this->record->status,
                        ]);

                        $kepalaUnits = User::role('kepala_unit')->get();
                        if ($kepalaUnits->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Insiden Diperbaiki')
                                ->body("Laporan dari {$this->record->nama_pelapor} telah diperbaiki dan dikirim kembali.")
                                ->warning()
                                ->sendToDatabase($kepalaUnits);

                            file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit: Notification sent\n", FILE_APPEND);
                            Log::info('[EditLaporanInsiden] Resubmit notification sent to kepala_unit', [
                                'count' => $kepalaUnits->count(),
                            ]);
                        }

                        Notification::make()
                            ->title('Laporan berhasil dikirim')
                            ->success()
                            ->send();

                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit: Success\n", FILE_APPEND);
                        Log::info('[EditLaporanInsiden] Resubmit success notification sent');

                        redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                    } catch (\Exception $e) {
                        file_put_contents(storage_path('logs/debug-action.log'), "[" . now() . "] resubmit ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
                        Log::error('[EditLaporanInsiden] resubmit error: ' . $e->getMessage(), [
                            'record_id' => $this->record->id,
                            'trace' => $e->getTraceAsString(),
                        ]);

                        Notification::make()
                            ->title('Gagal mengirim ulang laporan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        return $actions;
    }
}
