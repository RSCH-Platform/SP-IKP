<?php

namespace App\Filament\Resources\UnitKerjas\Pages;

use App\Filament\Resources\UnitKerjas\UnitKerjaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource\Pages\ListUnitKerja;

class ListUnitKerjas extends ListUnitKerja
{
    protected static string $resource = UnitKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
