<?php

namespace App\Filament\Resources\UnitKerjas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Anggota Unit';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->placeholder('Belum diverifikasi'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No HP'),
            ])
            ->headerActions([
                \Filament\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    // ->hidden()
                    ->recordSelectSearchColumns(['name', 'nip']),
            ])
            ->recordActions([
                \Filament\Actions\DetachAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\DetachBulkAction::make(),
            ]);
    }
}
