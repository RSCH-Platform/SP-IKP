<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function baseQuery(): Builder
    {
        // Reuse resource query so counts and tabs follow the same access scope.
        return UserResource::getEloquentQuery();
    }

    /**
     * Count users that belong to a given role.
     */
    protected function roleCount(string $role): int
    {
        return (clone $this->baseQuery())
            ->role($role)
            ->count();
    }

    public function getTabs(): array
    {
        $tabs = [];

        // always provide an "all users" tab if permitted
        if (auth()->user()?->can('ViewAny:User')) {
            $tabs['all'] = Tab::make('Semua Pengguna')
                ->badge(fn() => $this->baseQuery()->count())
                ->badgeColor('gray');
        }

        // generate a tab for every role defined in the system
        Role::orderBy('name')->get()->each(function (Role $role) use (&$tabs) {
            $tabs['role_' . $role->id] = Tab::make($role->name)
                ->badge(fn() => $this->roleCount($role->name))
                ->modifyQueryUsing(
                    fn(Builder $query) => $query->role($role->name)
                );
        });


        return $tabs;
    }
}
