<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\RelationManagers\RolesRelationManager;
use App\Filament\Resources\Users\RelationManagers\UnitKerjaRelationManager;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            RelationManagerAction::make('unit-kerja')
                ->label('Unit Kerja')
                ->icon('heroicon-o-building-office')
                ->color('info')
                ->relationManager(UnitKerjaRelationManager::class)
                ->modalWidth('5xl'),
            RelationManagerAction::make('roles')
                ->label('Roles')
                ->icon('heroicon-o-shield-check')
                ->color('warning')
                ->relationManager(RolesRelationManager::class)
                ->modalWidth('4xl'),
        ];
    }
}
