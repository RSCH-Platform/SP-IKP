<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\RelationManagers\RolesRelationManager;
use App\Filament\Resources\Users\RelationManagers\UnitKerjaRelationManager;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()->record($this->getRecord()),
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
            DeleteAction::make()
                ->label('Hapus Pengguna'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }
}
