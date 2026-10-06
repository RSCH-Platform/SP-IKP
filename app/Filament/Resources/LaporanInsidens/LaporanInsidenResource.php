<?php

namespace App\Filament\Resources\LaporanInsidens;

use App\Filament\Resources\LaporanInsidens\Pages\CreateLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\EditLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\ListLaporanInsidens;
use App\Filament\Resources\LaporanInsidens\Pages\ViewLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\PreviewLaporanInsiden;
use App\Filament\Resources\LaporanInsidens\Pages\PreviewInvestigasiLaporanInsiden;
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

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->can('ViewAny:LaporanInsiden');
    }

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
            'preview' => PreviewLaporanInsiden::route('/{record}/preview'),
            'preview-investigasi' => PreviewInvestigasiLaporanInsiden::route('/{record}/preview-investigasi'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with([
                'reporter',
                'verifier',
                'user',
                'unitKerja',
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Eager load unitKerja to prevent N+1 queries
        $query->with('unitKerja');

        $user = Auth::user();
        if (! $user) {
            return $query;
        }

        $canForceEdit = method_exists($user, 'checkPermissionTo')
            ? $user->checkPermissionTo('ForceEdit:LaporanInsiden')
            : $user->can('ForceEdit:LaporanInsiden');

        $canViewAll = method_exists($user, 'checkPermissionTo')
            ? $user->checkPermissionTo('ViewAllData:LaporanInsiden')
            : $user->can('ViewAllData:LaporanInsiden');

        if ($canForceEdit && $canViewAll) {
            return $query;
        }

        // if the currently authenticated user only has submit-rights (no ability to view
        // lists of reports) then limit the query to their own rows. this covers the case
        // where a 'pelapor' can submit but shouldn't see other people's drafts.
        $canSubmit = method_exists($user, 'checkPermissionTo')
            ? $user->checkPermissionTo('Submit:LaporanInsiden')
            : $user->can('Submit:LaporanInsiden');

        $canVerify = method_exists($user, 'checkPermissionTo')
            ? $user->checkPermissionTo('Verifikasi:LaporanInsiden')
            : $user->can('Verifikasi:LaporanInsiden');

        if ($canSubmit && ! $canVerify && ! $canViewAll) {
            return $query->where('user_id', $user->getKey());
        }

        // existing unit‑based scoping when the user may view reports but not everything
        $canView = method_exists($user, 'checkPermissionTo')
            ? $user->checkPermissionTo('View:LaporanInsiden')
            : false;

        $canViewAny = method_exists($user, 'checkPermissionTo')
            ? $user->checkPermissionTo('ViewAny:LaporanInsiden')
            : false;

        if ($canView && $canViewAny) {
            $unitKerjaIds = $user->unitKerjas()->pluck('id');
            $query->whereIn('unit_kerja_id', $unitKerjaIds);
        }

        return $query;
    }
}
