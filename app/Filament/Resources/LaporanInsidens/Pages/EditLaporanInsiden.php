<?php

namespace App\Filament\Resources\LaporanInsidens\Pages;

use App\Filament\Resources\LaporanInsidens\LaporanInsidenResource;
use App\Models\LaporanInsiden;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

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
        $user = auth()->user();

        // Field wajib untuk submit laporan
        $requiredFieldsForSubmit = [
            'nama_pelapor' => 'Nama Pelapor',
            'unit_kerja_id' => 'Unit Kerja',
            'tanggal_lapor' => 'Tanggal Lapor',
            'jenis_insiden' => 'Jenis Insiden',
            'tanggal_insiden' => 'Tanggal Insiden',
            'waktu_insiden' => 'Waktu Insiden',
            'lokasi_insiden' => 'Lokasi Insiden',
            'kronologi' => 'Kronologi',
            'insiden_terjadi_pada' => 'Insiden Terjadi Pada',
            'kategori_insiden' => 'Kategori Insiden',
            'deskripsi_kategori_insiden' => 'Deskripsi Kategori Insiden',
            'dampak_insiden' => 'Dampak Insiden',
            'tindakan_dilakukan' => 'Tindakan Dilakukan',
        ];

        // Tombol simpan biasa (untuk status draft, revisi, revisi_unit)
        if (
            ($user?->can('Submit:LaporanInsiden') && in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI], true))
            || ($user?->can('Verifikasi:LaporanInsiden') && $this->record->status === LaporanInsiden::STATUS_REVISI_UNIT)
        ) {
            $actions[] = Action::make('save')
                ->label('Simpan Perubahan')
                ->color('gray')
                ->icon('heroicon-o-check')
                ->action(function () {
                    try {
                        $this->save();

                        Notification::make()
                            ->title('Data berhasil disimpan')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal menyimpan data')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        ######################################
        ### Workflow actions sesuai status ### 
        ######################################

        // Tombol submit laporan (untuk status draft, revisi)
        if (in_array($this->record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI], true) && $user?->can('Submit:LaporanInsiden')) {
            $actions[] = Action::make('submitLaporan')
                ->label($this->record->status === LaporanInsiden::STATUS_REVISI ? 'Kirim Ulang Laporan' : 'Kirim Laporan')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Kirim Laporan Insiden?')
                ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi. Pastikan semua data sudah lengkap.')
                ->icon('heroicon-o-paper-airplane')
                ->action(function () use ($requiredFieldsForSubmit) {
                    try {
                        $this->save();

                        $missingFields = collect($requiredFieldsForSubmit)
                            ->filter(fn($label, $field) => blank(data_get($this->record, $field)))
                            ->values()
                            ->all();

                        if (! empty($missingFields)) {
                            Notification::make()
                                ->title('Laporan belum bisa dikirim')
                                ->body('Lengkapi field wajib berikut: ' . implode(', ', $missingFields))
                                ->danger()
                                ->send();

                            return;
                        }

                        $this->record->refresh();
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

                        $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal mengirim laporan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        // Tombol verifikasi laporan (untuk status dilaporkan)
        if ($this->record->status === LaporanInsiden::STATUS_DILAPORKAN && $user?->can('Verifikasi:LaporanInsiden')) {
            $actions[] = Action::make('verifikasiLaporan')
                ->label('Verifikasi & Kirim ke Tim Mutu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Laporan?')
                ->modalDescription('Setelah diverifikasi, laporan akan dikirim ke tim mutu untuk investigasi.')
                ->action(function (array $data) {
                    $this->save();

                    $this->record->update([
                        'grading_risiko' => $data['grading_risiko'],
                        'catatan_tambahan' => $data['catatan_tambahan'] ?? $this->record->catatan_tambahan,
                    ]);

                    $this->record->verifikasiLaporan(auth()->id());

                    $timMutu = User::role(['tim_mutu', 'admin'])->get();

                    Notification::make()
                        ->title('Laporan berhasil diverifikasi')
                        ->success()
                        ->send();

                    if ($timMutu->isNotEmpty()) {
                        Notification::make()
                            ->title('Laporan Siap Investigasi')
                            ->body("Laporan dari {$this->record->nama_pelapor} telah diverifikasi dan siap untuk investigasi.")
                            ->info()
                            ->sendToDatabase($timMutu);
                    }

                    $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                });

            $actions[] = Action::make('kembalikanLaporan')
                ->label('Kembalikan untuk Revisi')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Kembalikan Laporan?')
                ->modalDescription('Laporan akan dikembalikan ke pelapor untuk diperbaiki.')
                ->schema([
                    Textarea::make('alasan_pengembalian')
                        ->label('Alasan Pengembalian')
                        ->required()
                        ->rows(4)
                        ->placeholder('Jelaskan alasan pengembalian dan apa yang perlu diperbaiki...'),
                ])
                ->action(function (array $data) {
                    $this->save();

                    $this->record->kembalikanKePelapor(auth()->id(), $data['alasan_pengembalian']);

                    $pelapor = User::find($this->record->created_by);

                    if ($pelapor) {
                        Notification::make()
                            ->title('Laporan Dikembalikan untuk Revisi')
                            ->body("Laporan Anda perlu diperbaiki. Alasan: {$data['alasan_pengembalian']}")
                            ->warning()
                            ->sendToDatabase($pelapor);
                    }

                    Notification::make()
                        ->title('Laporan dikembalikan untuk revisi')
                        ->success()
                        ->send();

                    $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                });
        }

        // Tombol verifikasi ulang (untuk status revisi_unit)
        if ($this->record->status === LaporanInsiden::STATUS_REVISI_UNIT && $user?->can('Verifikasi:LaporanInsiden')) {
            $actions[] = Action::make('verifikasiUlang')
                ->label('Verifikasi Ulang & Kirim ke Tim Mutu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Ulang Laporan?')
                ->schema([
                    Select::make('grading_risiko')
                        ->label('Grading Risiko')
                        ->required()
                        ->options([
                            'Biru' => 'Biru',
                            'Hijau' => 'Hijau',
                            'Kuning' => 'Kuning',
                            'Merah' => 'Merah',
                            'Hitam' => 'Hitam',
                        ])
                        ->native(false)
                        ->default(fn() => $this->record->grading_risiko),
                    Textarea::make('catatan_tambahan')
                        ->label('Catatan Verifikasi')
                        ->rows(3)
                        ->default(fn() => $this->record->catatan_tambahan),
                ])
                ->action(function (array $data) {
                    $this->save();

                    $this->record->update([
                        'grading_risiko' => $data['grading_risiko'],
                        'catatan_tambahan' => $data['catatan_tambahan'] ?? $this->record->catatan_tambahan,
                    ]);

                    $this->record->verifikasiLaporan(auth()->id());

                    $timMutu = User::role(['tim_mutu', 'admin'])->get();

                    Notification::make()
                        ->title('Laporan diverifikasi ulang')
                        ->success()
                        ->send();

                    if ($timMutu->isNotEmpty()) {
                        Notification::make()
                            ->title('Laporan siap investigasi')
                            ->sendToDatabase($timMutu);
                    }

                    $this->redirect(LaporanInsidenResource::getUrl('view', ['record' => $this->record->id]));
                });
        }

        return $actions;
    }
}
