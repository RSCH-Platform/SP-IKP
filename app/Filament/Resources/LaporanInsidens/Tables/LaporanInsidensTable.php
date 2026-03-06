<?php

namespace App\Filament\Resources\LaporanInsidens\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
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
                    ->searchable()
                    ->wrap(),

                TextColumn::make('kategori_insiden')
                    ->label('Kategori')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('lokasi_insiden')
                    ->label('Lokasi')
                    ->searchable()
                    ->wrap()
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'submitted',
                        'info' => 'reviewed',
                        'success' => 'closed',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Disubmit',
                        'reviewed' => 'Direview',
                        'closed' => 'Selesai',
                        default => $state,
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
                        'draft' => 'Draft',
                        'submitted' => 'Disubmit',
                        'reviewed' => 'Direview',
                        'closed' => 'Selesai',
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
                EditAction::make(),
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
