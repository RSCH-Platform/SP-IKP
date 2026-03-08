<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UnitKerja;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitKerjaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UnitKerja');
    }

    public function view(AuthUser $authUser, UnitKerja $unitKerja): bool
    {
        return $authUser->can('View:UnitKerja');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UnitKerja');
    }

    public function update(AuthUser $authUser, UnitKerja $unitKerja): bool
    {
        return $authUser->can('Update:UnitKerja');
    }

    public function delete(AuthUser $authUser, UnitKerja $unitKerja): bool
    {
        return $authUser->can('Delete:UnitKerja');
    }

    public function restore(AuthUser $authUser, UnitKerja $unitKerja): bool
    {
        return $authUser->can('Restore:UnitKerja');
    }

    public function forceDelete(AuthUser $authUser, UnitKerja $unitKerja): bool
    {
        return $authUser->can('ForceDelete:UnitKerja');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UnitKerja');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UnitKerja');
    }

    public function replicate(AuthUser $authUser, UnitKerja $unitKerja): bool
    {
        return $authUser->can('Replicate:UnitKerja');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UnitKerja');
    }

}