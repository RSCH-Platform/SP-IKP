<?php

namespace App\Filament\Resources\UnitKerjas;

use App\Filament\Resources\UnitKerjas\Pages\CreateUnitKerja;
use App\Filament\Resources\UnitKerjas\Pages\EditUnitKerja;
use App\Filament\Resources\UnitKerjas\Pages\ListUnitKerjas;
use App\Filament\Resources\UnitKerjas\Pages\ViewUnitKerja;
use App\Filament\Resources\UnitKerjas\RelationManagers\UsersRelationManager;
use App\Filament\Resources\UnitKerjas\Schemas\UnitKerjaForm;
use App\Filament\Resources\UnitKerjas\Schemas\UnitKerjaInfolist;
use App\Filament\Resources\UnitKerjas\Tables\UnitKerjasTable;
use App\Models\UnitKerja;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BezhanSalleh\PluginEssentials\Concerns\Resource as Essentials;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource as ResourcesUnitKerjaResource;
use UnitEnum;

class UnitKerjaResource extends ResourcesUnitKerjaResource
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;

    protected static ?string $model = UnitKerja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $recordTitleAttribute = 'unit_name';

    // must match parent signature exactly (order and imported type)
    protected static UnitEnum|string|null $navigationGroup = 'Administration';

    public static function getNavigationGroup(): ?string
    {
        if (static::$navigationGroup instanceof UnitEnum) {
            return (string) static::$navigationGroup->value;
        }

        return is_string(static::$navigationGroup)
            ? static::$navigationGroup
            : parent::getNavigationGroup();
    }

    public static function form(Schema $schema): Schema
    {
        return UnitKerjaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UnitKerjaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitKerjasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnitKerjas::route('/'),
            'create' => CreateUnitKerja::route('/create'),
            'view' => ViewUnitKerja::route('/{record}'),
            'edit' => EditUnitKerja::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
