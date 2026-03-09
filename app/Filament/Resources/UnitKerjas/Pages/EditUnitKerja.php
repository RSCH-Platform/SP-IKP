<?php

namespace App\Filament\Resources\UnitKerjas\Pages;

use App\Filament\Resources\UnitKerjas\RelationManagers\UsersRelationManager;
use App\Filament\Resources\UnitKerjas\UnitKerjaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;

class EditUnitKerja extends EditRecord
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            RelationManagerAction::make('users')
                ->label('Anggota Unit')
                ->icon('heroicon-o-users')
                ->color('info')
                ->relationManager(UsersRelationManager::class)
                ->modalWidth('5xl'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
