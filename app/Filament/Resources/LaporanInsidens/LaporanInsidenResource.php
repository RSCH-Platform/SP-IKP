<?php

namespace App\Filament\Resources\LaporanInsidens;

use App\Filament\Resources\LaporanInsidens\Pages\CreateLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\EditLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\ListLaporanInsidens;
use App\Filament\Resources\LaporanInsidens\Pages\ViewLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenForm;
use App\Filament\Resources\LaporanInsidens\Tables\LaporanInsidensTable;
use App\Models\LaporanInsiden;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class LaporanInsidenResource extends Resource
{
    protected static ?string $model = LaporanInsiden::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Daftar Laporan Insiden';

    protected static ?string $modelLabel = 'Laporan Insiden';

    protected static ?string $pluralModelLabel = 'Laporan Insiden';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return LaporanInsidenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LaporanInsidensTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaporanInsidens::route('/'),
            'create' => CreateLaporanInsiden::route('/create'),
            'view' => ViewLaporanInsiden::route('/{record}'),
            'edit' => EditLaporanInsiden::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->can('ViewAllData:LaporanInsiden')) {
            return $query;
        }

        // if the currently authenticated user only has submit-rights (no ability to view
        // lists of reports) then limit the query to their own rows. this covers the case
        // where a 'pelapor' can submit but shouldn't see other people's drafts.
        
        if ($user->can('Submit:LaporanInsiden') && ! $user->can('ViewAllData:LaporanInsiden')) {
            return $query->where('user_id', $user->getKey());
        }

        // existing unit‑based scoping when the user may view reports but not everything
        if ($user && $user->hasPermissionTo('View:LaporanInsiden') && $user->hasPermissionTo('ViewAny:LaporanInsiden')) {
            $unitKerjaIds = $user->unitKerja()->pluck('id');
            $query->whereIn('unit_kerja_id', $unitKerjaIds);
        }

        return $query;
    }
}
