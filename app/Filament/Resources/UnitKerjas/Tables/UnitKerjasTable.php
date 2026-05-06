<?php

namespace App\Filament\Resources\UnitKerjas\Tables;

use App\Filament\Resources\UnitKerjas\RelationManagers\UsersRelationManager;
use App\Models\UnitKerja;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;

class UnitKerjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('unit_name')
                ->label(__('filament-forms::unit-kerja.fields.unit_name'))
                ->description(fn(UnitKerja $record) => $record->description)
                ->wrap()
                ->grow()
                ->weight(FontWeight::Bold)
                ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()->hidden(),
                EditAction::make()->hidden(),
                RelationManagerAction::make('users')
                    ->label('Anggota')
                    ->icon('heroicon-m-users')
                    ->color('info')
                    ->slideOver()
                    ->relationManager(UsersRelationManager::class)
                    ->modalWidth('5xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
