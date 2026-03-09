<?php

namespace App\Filament\Resources\UnitKerjas\Pages;

use App\Filament\Resources\UnitKerjas\RelationManagers\UsersRelationManager;
use App\Filament\Resources\UnitKerjas\UnitKerjaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;

class ViewUnitKerja extends ViewRecord
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            RelationManagerAction::make('users')
                ->label('Anggota Unit')
                ->icon('heroicon-o-users')
                ->color('info')
                ->relationManager(UsersRelationManager::class)
                ->modalWidth('5xl'),
        ];
    }
}
