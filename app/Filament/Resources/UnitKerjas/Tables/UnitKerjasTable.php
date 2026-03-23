<?php

namespace App\Filament\Resources\UnitKerjas\Tables;

use App\Filament\Resources\UnitKerjas\RelationManagers\UsersRelationManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\RelationManagerAction;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource\Tables\UnitKerjaResourceTable;

class UnitKerjasTable extends UnitKerjaResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::columns())
            ->filters(self::filters())
            ->actions(self::actions())
            ->bulkActions(self::bulkActions());
    }
}
