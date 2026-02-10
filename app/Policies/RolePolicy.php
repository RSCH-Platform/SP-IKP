<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(AuthUser $user): bool
    {
        return $user->can('ViewAny:Role');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(AuthUser $user, Role $role): bool
    {
        return $user->can('View:Role');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(AuthUser $user): bool
    {
        return $user->can('Create:Role');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(AuthUser $user, Role $role): bool
    {
        return $user->can('Update:Role');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(AuthUser $user, Role $role): bool
    {
        return $user->can('Delete:Role');
    }

    /**
     * Determine whether the user can restore the role.
     */
    public function restore(AuthUser $user, Role $role): bool
    {
        return $user->can('Restore:Role');
    }

    /**
     * Determine whether the user can permanently delete the role.
     */
    public function forceDelete(AuthUser $user, Role $role): bool
    {
        return $user->can('ForceDelete:Role');
    }

    /**
     * Determine whether the user can permanently delete any role.
     */
    public function forceDeleteAny(AuthUser $user): bool
    {
        return $user->can('ForceDeleteAny:Role');
    }

    /**
     * Determine whether the user can restore any role.
     */
    public function restoreAny(AuthUser $user): bool
    {
        return $user->can('RestoreAny:Role');
    }

    /**
     * Determine whether the user can replicate the role.
     */
    public function replicate(AuthUser $user, Role $role): bool
    {
        return $user->can('Replicate:Role');
    }

    /**
     * Determine whether the user can reorder roles.
     */
    public function reorder(AuthUser $user): bool
    {
        return $user->can('Reorder:Role');
    }
}
