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
use Filament\Forms\Components\Select;
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

        return $table
            ->columns([
                TextColumn::make('nomor_laporan')
                    ->label('No. Laporan')
                    ->icon('heroicon-m-document-text')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('tanggal_insiden')
                    ->label('Tanggal Insiden')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jenis_insiden')
                    ->label('Jenis')
                    ->badge()
                    ->searchable(),

                TextColumn::make('kategori_insiden')
                    ->label('Kategori')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('lokasi_insiden')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('nama_pelapor')
                    ->label('Pelapor')
                    ->searchable(),

                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->formatStateUsing(fn($state, $record) => $state ?: ($record->unitKerja?->unit_name ?? '-'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('nomor_telepon')
                    ->label('No. Telepon')
                    ->copyable()
                    ->copyMessage('Nomor telepon berhasil disalin!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('dampak_insiden')
                    ->label('Dampak')
                    ->badge()
                    ->colors([
                        'success' => 'Tidak ada cedera',
                        'warning' => ['Cedera ringan', 'Cedera sedang'],
                        'danger' => ['Cedera berat', 'Meninggal'],
                    ]),

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
                /*
                |--------------------------------------------------------------------------
                | Workflow Pelapor
                |--------------------------------------------------------------------------
                */

                Action::make('submit_laporan')
                    ->label('Kirim Laporan')
                    ->icon('heroicon-o-paper-airplane')
                    ->button()
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
                    ->action(function ($record) use ($requiredFieldsForSubmit) {
                        $missingFields = collect($requiredFieldsForSubmit)
                            ->filter(fn($label, $field) => blank(data_get($record, $field)))
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

                        $record->submitLaporan();

                        $kepalaUnits = User::role('kepala_unit')->get();

                        Notification::make()
                            ->title('Laporan berhasil dikirim')
                            ->body("Laporan {$record->nomor_laporan} berhasil dikirim ke kepala unit.")
                            ->success()
                            ->send();

                        Notification::make()
                            ->title('Laporan Insiden Baru')
                            ->body("Ada laporan insiden baru dari {$record->nama_pelapor} - {$record->nomor_laporan}")
                            ->warning()
                            ->sendToDatabase($kepalaUnits);
                    }),

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
                                ->default(fn($record) => $record->grading_risiko),
                            Textarea::make('catatan_tambahan')
                                ->label('Catatan Verifikasi')
                                ->rows(3)
                                ->default(fn($record) => $record->catatan_tambahan),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'grading_risiko' => $data['grading_risiko'],
                                'catatan_tambahan' => $data['catatan_tambahan'] ?? $record->catatan_tambahan,
                            ]);

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
                        ->schema([
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
                    ->color('info')
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
                        ->action(function ($record) {
                            if (blank($record->grading_risiko)) {
                                Notification::make()
                                    ->title('Belum bisa investigasi')
                                    ->body('Grading risiko wajib diisi saat verifikasi sebelum memulai investigasi.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->mulaiInvestigasi(auth()->id());
                        }),

                    Action::make('kembalikan_ke_unit')
                        ->label('Kembalikan ke Kepala Unit')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->visible(
                            fn($record) =>
                            auth()->user()?->can('KembalikanUnit:LaporanInsiden') &&
                                $record->status === LaporanInsiden::STATUS_DIVERIFIKASI
                        )
                        ->schema([
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
                    ->color('success')
                    ->label('Tim Mutu')
                    ->icon('heroicon-o-shield-check'),

                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(function ($record) {
                            $user = auth()->user();

                            if (! $user || ! $user->can('Update:LaporanInsiden')) {
                                return false;
                            }

                            if (in_array($record->status, [LaporanInsiden::STATUS_DRAFT, LaporanInsiden::STATUS_REVISI], true)) {
                                return $user->can('Submit:LaporanInsiden');
                            }

                            if ($record->status === LaporanInsiden::STATUS_DILAPORKAN) {
                                return $user->can('Verifikasi:LaporanInsiden') || $user->can('Kembalikan:LaporanInsiden');
                            }

                            if ($record->status === LaporanInsiden::STATUS_REVISI_UNIT) {
                                return $user->can('Verifikasi:LaporanInsiden');
                            }

                            return false;
                        }),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->label('Aksi')
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
