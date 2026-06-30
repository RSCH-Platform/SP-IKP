<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LaporanInsiden;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanInsidenPolicy
{
    use HandlesAuthorization;

    public function viewAllData(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAllData:LaporanInsiden');
    }
    
    public function ForceEdit(AuthUser $authUser): bool
    {
        return $authUser->can('ForceEdit:LaporanInsiden');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        // User harus punya unit kerja dan permission ViewAny
        return $authUser->can('ViewAny:LaporanInsiden');
    }

    public function view(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        // Force edit users should always be able to view the record for editing
        if ($authUser->can('ForceEdit:LaporanInsiden')) {
            return true;
        }

        // Pembuat laporan (reporter) dapat melihat laporannya sendiri
        if ($laporanInsiden->reported_by === $authUser->id || $laporanInsiden->user_id === $authUser->id) {
            return true;
        }

        // Jika punya permission ViewAllData, bisa lihat semua laporan
        if ($authUser->can('ViewAllData:LaporanInsiden')) {
            return true;
        }

        // Jika punya View permission tapi tidak ViewAllData, hanya bisa lihat laporan dari unit kerja user
        if ($authUser->can('View:LaporanInsiden')) {
            $userUnitIds = $authUser->unitKerjas()->pluck('id');
            return $userUnitIds->contains($laporanInsiden->unit_kerja_id);
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LaporanInsiden');
    }

    public function update(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        // allow users with a force-edit permission to edit even when the normal
        // update/submit workflow would block them
        if ($authUser->can('ForceEdit:LaporanInsiden')) {
            return true;
        }

        return $authUser->can('Update:LaporanInsiden');
    }

    public function delete(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Delete:LaporanInsiden');
    }


    public function restore(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Restore:LaporanInsiden');
    }

    public function forceDelete(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('ForceDelete:LaporanInsiden');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LaporanInsiden');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LaporanInsiden');
    }

    public function replicate(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Replicate:LaporanInsiden');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LaporanInsiden');
    }
    
    // --- Workflow permissions ---

    public function submit(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Submit:LaporanInsiden');
    }

    public function verifikasi(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Verifikasi:LaporanInsiden');
    }

    public function kembalikan(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Kembalikan:LaporanInsiden');
    }

    public function investigasi(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('Investigasi:LaporanInsiden');
    }

    public function kembalikanUnit(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $authUser->can('KembalikanUnit:LaporanInsiden');
    }
}
