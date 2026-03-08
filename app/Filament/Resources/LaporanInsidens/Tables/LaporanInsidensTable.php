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
                        'draft'              => 'secondary',
                        'dilaporkan'         => 'warning',
                        'diverifikasi_unit'  => 'success',
                        'revisi'             => 'danger',
                        // legacy statuses (backward compat)
                        'submitted'          => 'warning',
                        'reviewed'           => 'info',
                        'closed'             => 'success',
                        default              => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft'              => 'Draft',
                        'dilaporkan'         => 'Dilaporkan',
                        'diverifikasi_unit'  => 'Diverifikasi Unit',
                        'revisi'             => 'Perlu Revisi',
                        // legacy
                        'submitted'          => 'Disubmit',
                        'reviewed'           => 'Direview',
                        'closed'             => 'Selesai',
                        default              => $state,
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
                        'draft'             => 'Draft',
                        'dilaporkan'        => 'Dilaporkan',
                        'diverifikasi_unit' => 'Diverifikasi Unit',
                        'revisi'            => 'Perlu Revisi',
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
                    ])),

                // --- Approval actions ---

                Action::make('submit_laporan')
                    ->label('Kirim Laporan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Submit:LaporanInsiden') &&
                            $record->status === LaporanInsiden::STATUS_DRAFT
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

                Action::make('verifikasi_laporan')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn($record) =>
                        auth()->user()?->can('Verifikasi:LaporanInsiden') &&
                            $record->status === LaporanInsiden::STATUS_DILAPORKAN
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Laporan Insiden?')
                    ->modalDescription('Laporan akan diverifikasi dan diteruskan ke tim mutu.')
                    ->action(function ($record) {
                        $record->verifikasiLaporan(auth()->id());

                        $notifyUsers = User::role(['tim_mutu', 'admin'])->get();
                        if ($notifyUsers->isNotEmpty()) {
                            Notification::make()
                                ->title('Laporan Insiden Diverifikasi')
                                ->body("Laporan dari {$record->nama_pelapor} telah diverifikasi oleh " . auth()->user()->name . '.')
                                ->success()
                                ->sendToDatabase($notifyUsers);
                        }

                        Notification::make()
                            ->title('Laporan berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),

                Action::make('kembalikan_laporan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(
                        fn($record) =>
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
                        $record->kembalikanLaporan(auth()->id(), $data['rejection_reason']);

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
