<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LaporanInsiden;
use Illuminate\Auth\Access\HandlesAuthorization;

class LaporanInsidenPolicy
{
    use HandlesAuthorization;

    protected function checkPermission(AuthUser $user, string $permission): bool
    {
        if (method_exists($user, 'checkPermissionTo')) {
            return $user->checkPermissionTo($permission);
        }

        return false;
    }

    public function viewAllData(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'ViewAllData:LaporanInsiden');
    }
    
    public function ForceEdit(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'ForceEdit:LaporanInsiden');
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'ViewAny:LaporanInsiden');
    }

    public function view(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        // Force edit users should always be able to view the record for editing
        if ($this->checkPermission($authUser, 'ForceEdit:LaporanInsiden')) {
            return true;
        }

        // Pembuat laporan (reporter) dapat melihat laporannya sendiri
        if ($laporanInsiden->reported_by === $authUser->id || $laporanInsiden->user_id === $authUser->id) {
            return true;
        }

        // Jika punya permission ViewAllData, bisa lihat semua laporan
        if ($this->checkPermission($authUser, 'ViewAllData:LaporanInsiden')) {
            return true;
        }

        // Jika punya View permission tapi tidak ViewAllData, hanya bisa lihat laporan dari unit kerja user
        if ($this->checkPermission($authUser, 'View:LaporanInsiden')) {
            $userUnitIds = $authUser->unitKerjas()->pluck('id');
            return $userUnitIds->contains($laporanInsiden->unit_kerja_id);
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'Create:LaporanInsiden');
    }

    public function update(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        // allow users with a force-edit permission to edit even when the normal
        // update/submit workflow would block them
        if ($this->checkPermission($authUser, 'ForceEdit:LaporanInsiden')) {
            return true;
        }

        return $this->checkPermission($authUser, 'Update:LaporanInsiden');
    }

    public function delete(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Delete:LaporanInsiden');
    }

    public function restore(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Restore:LaporanInsiden');
    }

    public function forceDelete(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'ForceDelete:LaporanInsiden');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'ForceDeleteAny:LaporanInsiden');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'RestoreAny:LaporanInsiden');
    }

    public function replicate(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Replicate:LaporanInsiden');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->checkPermission($authUser, 'Reorder:LaporanInsiden');
    }
    
    // --- Workflow permissions ---

    public function submit(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Submit:LaporanInsiden');
    }

    public function verifikasi(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Verifikasi:LaporanInsiden');
    }

    public function kembalikan(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Kembalikan:LaporanInsiden');
    }

    public function investigasi(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'Investigasi:LaporanInsiden');
    }

    public function kembalikanUnit(AuthUser $authUser, LaporanInsiden $laporanInsiden): bool
    {
        return $this->checkPermission($authUser, 'KembalikanUnit:LaporanInsiden');
    }
}
