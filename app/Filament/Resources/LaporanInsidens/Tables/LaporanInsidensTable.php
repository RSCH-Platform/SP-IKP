<?php

namespace App\Filament\Resources\LaporanInsidens\Tables;

use App\Models\LaporanInsiden;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LaporanInsidensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('nomor_laporan')
                //     ->label('No. Laporan')
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('tanggal_insiden')
                    ->label('Tanggal Insiden')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jenis_insiden')
                    ->label('Jenis')
                    ->searchable(),

                TextColumn::make('kategori_insiden')
                    ->label('Kategori')
                    ->searchable(),

                TextColumn::make('lokasi_insiden')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft'         => 'gray',
                        'dilaporkan'    => 'warning',
                        'revisi'        => 'danger',
                        'diverifikasi'  => 'info',
                        'revisi_unit'   => 'danger',
                        'investigasi'   => 'success',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft'         => 'Draft',
                        'dilaporkan'    => 'Dilaporkan',
                        'revisi'        => 'Perlu Revisi',
                        'diverifikasi'  => 'Diverifikasi',
                        'revisi_unit'   => 'Perlu Revisi (Unit)',
                        'investigasi'   => 'Investigasi',
                        default         => $state,
                    }),

                TextColumn::make('dampak_insiden')
                    ->label('Dampak')
                    ->badge()
                    ->colors([
                        'success' => 'Tidak ada cedera',
                        'warning' => ['Cedera ringan', 'Cedera sedang'],
                        'danger' => ['Cedera berat', 'Meninggal'],
                    ]),

                TextColumn::make('nama_pelapor')
                    ->label('Pelapor')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'        => 'Draft',
                        'dilaporkan'   => 'Dilaporkan',
                        'revisi'       => 'Perlu Revisi',
                        'diverifikasi' => 'Diverifikasi',
                        'revisi_unit'  => 'Perlu Revisi (Unit)',
                        'investigasi'  => 'Investigasi',
                    ]),

                SelectFilter::make('jenis_insiden')
                    ->label('Jenis Insiden')
                    ->options([
                        'KNC (Kejadian Nyaris Cedera)' => 'KNC',
                        'KTD (Kejadian Tidak Diharapkan)' => 'KTD',
                        'KTC (Kejadian Tidak Cedera)' => 'KTC',
                        'Sentinel' => 'Sentinel',
                    ]),

                SelectFilter::make('dampak_insiden')
                    ->label('Dampak')
                    ->options([
                        'Tidak ada cedera' => 'Tidak ada cedera',
                        'Cedera ringan' => 'Cedera ringan',
                        'Cedera sedang' => 'Cedera sedang',
                        'Cedera berat' => 'Cedera berat',
                        'Meninggal' => 'Meninggal',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => in_array($record->status, [
                        LaporanInsiden::STATUS_DRAFT,
                        LaporanInsiden::STATUS_REVISI,
                        LaporanInsiden::STATUS_REVISI_UNIT,
                    ])),

                // --- Workflow: Pelapor ---

                Action::make('submit_laporan')
                    ->label('Kirim Laporan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn($record) =>
                        auth()->user()?->can('Submit:LaporanInsiden') &&
                        in_array($record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Laporan Insiden?')
                    ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi. Pastikan semua data sudah lengkap.')
                    ->action(function ($record) {
                        $record->submitLaporan();

                        $kepalaUnits = User::role('kepala_unit')->get();
                        if ($kepalaUnits->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Insiden Baru')
                                ->body("Ada laporan insiden baru dari {$record->nama_pelapor} yang perlu diverifikasi.")
                                ->warning()
                                ->sendToDatabase($kepalaUnits);
                        }

                        Notification::make()
                            ->title('Laporan berhasil dikirim')
                            ->body('Laporan Anda telah dikirim ke kepala unit untuk diverifikasi.')
                            ->success()
                            ->send();
                    }),

                // --- Workflow: Kepala Unit ---

                Action::make('verifikasi_laporan')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) =>
                        auth()->user()?->can('Verifikasi:LaporanInsiden') &&
                        $record->status === LaporanInsiden::STATUS_DILAPORKAN
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Laporan?')
                    ->modalDescription('Laporan akan diverifikasi dan diteruskan ke tim mutu untuk investigasi. Pastikan grading dan analisis sudah diisi.')
                    ->action(function ($record) {
                        $record->verifikasiLaporan(auth()->id());

                        $timMutu = User::role(['tim_mutu', 'admin'])->get();
                        if ($timMutu->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Siap Investigasi')
                                ->body("Laporan dari {$record->nama_pelapor} telah diverifikasi oleh " . auth()->user()->name . ' dan siap untuk investigasi.')
                                ->success()
                                ->sendToDatabase($timMutu);
                        }

                        Notification::make()
                            ->title('Laporan berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),

                Action::make('kembalikan_ke_pelapor')
                    ->label('Kembalikan ke Pelapor')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) =>
                        auth()->user()?->can('Kembalikan:LaporanInsiden') &&
                        $record->status === LaporanInsiden::STATUS_DILAPORKAN
                    )
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Pengembalian')
                            ->placeholder('Jelaskan apa yang perlu diperbaiki oleh pelapor...')
                            ->required()
                            ->minLength(10)
                            ->rows(4),
                    ])
                    ->modalHeading('Kembalikan Laporan ke Pelapor')
                    ->modalSubmitActionLabel('Kembalikan')
                    ->action(function ($record, array $data) {
                        $record->kembalikanKePelapor(auth()->id(), $data['rejection_reason']);

                        if ($record->user) {
                            Notification::make()
                                ->title('Laporan Perlu Diperbaiki')
                                ->body("Laporan insiden Anda perlu diperbaiki. Alasan: {$data['rejection_reason']}")
                                ->danger()
                                ->sendToDatabase([$record->user]);
                        }

                        Notification::make()
                            ->title('Laporan dikembalikan ke pelapor')
                            ->success()
                            ->send();
                    }),

                // --- Workflow: Tim Mutu ---

                Action::make('mulai_investigasi')
                    ->label('Mulai Investigasi')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->visible(fn($record) =>
                        auth()->user()?->can('Investigasi:LaporanInsiden') &&
                        $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Investigasi Sederhana?')
                    ->modalDescription('Laporan akan masuk ke tahap investigasi sederhana.')
                    ->action(function ($record) {
                        $record->mulaiInvestigasi(auth()->id());

                        Notification::make()
                            ->title('Investigasi dimulai')
                            ->success()
                            ->send();
                    }),

                Action::make('kembalikan_ke_unit')
                    ->label('Kembalikan ke Kepala Unit')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) =>
                        auth()->user()?->can('KembalikanUnit:LaporanInsiden') &&
                        $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                    )
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Pengembalian')
                            ->placeholder('Jelaskan apa yang perlu diperbaiki oleh kepala unit...')
                            ->required()
                            ->minLength(10)
                            ->rows(4),
                    ])
                    ->modalHeading('Kembalikan Laporan ke Kepala Unit')
                    ->modalSubmitActionLabel('Kembalikan')
                    ->action(function ($record, array $data) {
                        $record->kembalikanKeKepalaUnit(auth()->id(), $data['rejection_reason']);

                        $kepalaUnits = User::role('kepala_unit')->get();
                        if ($kepalaUnits->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Perlu Diperbaiki')
                                ->body("Laporan dari {$record->nama_pelapor} dikembalikan. Alasan: {$data['rejection_reason']}")
                                ->danger()
                                ->sendToDatabase($kepalaUnits);
                        }

                        Notification::make()
                            ->title('Laporan dikembalikan ke kepala unit')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
