<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Folder');
    }

    public function view(AuthUser $authUser, Folder $folder): bool
    {
        return $authUser->can('View:Folder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Folder');
    }

    public function update(AuthUser $authUser, Folder $folder): bool
    {
        return $authUser->can('Update:Folder');
    }

    public function delete(AuthUser $authUser, Folder $folder): bool
    {
        return $authUser->can('Delete:Folder');
    }

    public function restore(AuthUser $authUser, Folder $folder): bool
    {
        return $authUser->can('Restore:Folder');
    }

    public function forceDelete(AuthUser $authUser, Folder $folder): bool
    {
        return $authUser->can('ForceDelete:Folder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Folder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Folder');
    }

    public function replicate(AuthUser $authUser, Folder $folder): bool
    {
        return $authUser->can('Replicate:Folder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Folder');
    }

}