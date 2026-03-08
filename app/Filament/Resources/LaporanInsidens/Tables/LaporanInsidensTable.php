<?php

namespace App\Filament\Resources\LaporanInsidens\Tables;

use App\Models\LaporanInsiden;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn($record) => in_array($record->status, [
                            LaporanInsiden::STATUS_DRAFT,
                            LaporanInsiden::STATUS_REVISI,
                            LaporanInsiden::STATUS_REVISI_UNIT,
                        ])),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->label('Aksi'),

                /*
                |--------------------------------------------------------------------------
                | Workflow Pelapor
                |--------------------------------------------------------------------------
                */

                ActionGroup::make([
                    Action::make('submit_laporan')
                        ->label('Kirim Laporan')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(
                            fn($record) =>
                            auth()->user()?->can('Submit:LaporanInsiden') &&
                                in_array($record->status, [
                                    LaporanInsiden::STATUS_DRAFT,
                                    LaporanInsiden::STATUS_REVISI
                                ])
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Laporan Insiden?')
                        ->modalDescription('Laporan akan dikirim ke kepala unit untuk diverifikasi.')
                        ->action(function ($record) {

                            $record->submitLaporan();

                            $kepalaUnits = User::role('kepala_unit')->get();

                            Notification::make()
                                ->title('Laporan berhasil dikirim')
                                ->success()
                                ->send();

                            Notification::make()
                                ->title('Laporan Insiden Baru')
                                ->body("Ada laporan insiden baru dari {$record->nama_pelapor}.")
                                ->warning()
                                ->sendToDatabase($kepalaUnits);
                        }),

                ])
                    ->button()
                    ->visible(fn() => auth()->user()?->can('Submit:LaporanInsiden'))
                    ->label('Pelaporan')
                    ->icon('heroicon-o-user'),

                /*
                |--------------------------------------------------------------------------
                | Workflow Kepala Unit
                |--------------------------------------------------------------------------
                */

                ActionGroup::make([

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
                        ->modalHeading('Verifikasi Laporan')
                        ->action(function ($record) {

                            $record->verifikasiLaporan(auth()->id());

                            $timMutu = User::role(['tim_mutu', 'admin'])->get();

                            Notification::make()
                                ->title('Laporan diverifikasi')
                                ->success()
                                ->send();

                            Notification::make()
                                ->title('Laporan siap investigasi')
                                ->sendToDatabase($timMutu);
                        }),

                    Action::make('kembalikan_ke_pelapor')
                        ->label('Kembalikan ke Pelapor')
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
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {

                            $record->kembalikanKePelapor(auth()->id(), $data['rejection_reason']);

                            Notification::make()
                                ->title('Laporan dikembalikan ke pelapor')
                                ->danger()
                                ->send();
                        }),

                ])
                    ->visible(fn() => auth()->user()?->can('Verifikasi:LaporanInsiden'))
                    ->label('Kepala Unit')
                    ->button()
                    ->icon('heroicon-o-building-office'),

                /*
                |--------------------------------------------------------------------------
                | Workflow Tim Mutu
                |--------------------------------------------------------------------------
                */

                ActionGroup::make([

                    Action::make('mulai_investigasi')
                        ->label('Mulai Investigasi')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('info')
                        ->visible(
                            fn($record) =>
                            auth()->user()?->can('Investigasi:LaporanInsiden') &&
                                $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                        )
                        ->requiresConfirmation()
                        ->action(fn($record) => $record->mulaiInvestigasi(auth()->id())),

                    Action::make('kembalikan_ke_unit')
                        ->label('Kembalikan ke Kepala Unit')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->visible(
                            fn($record) =>
                            auth()->user()?->can('KembalikanUnit:LaporanInsiden') &&
                                $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                        )
                        ->form([
                            Textarea::make('rejection_reason')
                                ->label('Alasan Pengembalian')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {

                            $record->kembalikanKeKepalaUnit(auth()->id(), $data['rejection_reason']);

                            Notification::make()
                                ->title('Laporan dikembalikan ke kepala unit')
                                ->danger()
                                ->send();
                        }),

                ])
                    ->visible(fn() => auth()->user()?->can('Investigasi:LaporanInsiden'))
                    ->button()
                    ->label('Tim Mutu')
                    ->icon('heroicon-o-shield-check'),
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
