<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        /*
        |--------------------------------------------------------------------------
        | Permission Actions (standar Filament Shield)
        |--------------------------------------------------------------------------
        */

        $actions = [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'Restore',
            'ForceDelete',
            'ForceDeleteAny',
            'RestoreAny',
            'Replicate',
            'Reorder',
        ];

        /*
        |--------------------------------------------------------------------------
        | Workflow Actions (custom, bukan dari Shield)
        |--------------------------------------------------------------------------
        */

        $workflowPermissions = [
            'Submit:LaporanInsiden',
            'Verifikasi:LaporanInsiden',
            'Kembalikan:LaporanInsiden',
        ];

        /*
        |--------------------------------------------------------------------------
        | Resources
        |--------------------------------------------------------------------------
        */

        $resources = [
            'LaporanInsiden',
            'Role',
        ];

        /*
        |--------------------------------------------------------------------------
        | Generate Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = collect($resources)
            ->flatMap(function ($resource) use ($actions) {
                return collect($actions)->map(fn($action) => "{$action}:{$resource}");
            });

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        foreach ($workflowPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = [
            'super_admin',
            'admin',
            'tim_mutu',
            'kepala_unit',
            'pelapor',
            'manajemen',
        ];

        $roleInstances = [];

        foreach ($roles as $role) {
            $roleInstances[$role] = Role::firstOrCreate([
                'name' => $role,
                'guard_name' => $guard,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Assign Permissions
        |--------------------------------------------------------------------------
        */

        $allPermissions = Permission::all();

        // Super Admin → semua permission
        $roleInstances['super_admin']->syncPermissions($allPermissions);

        // Admin → semua laporan + manajemen role
        $adminPermissions = collect($actions)
            ->map(fn($action) => "{$action}:LaporanInsiden")
            ->merge([
                'ViewAny:Role',
                'View:Role',
                'Create:Role',
                'Update:Role',
                'Delete:Role',
                'Verifikasi:LaporanInsiden',
                'Kembalikan:LaporanInsiden',
            ]);

        $roleInstances['admin']->syncPermissions($adminPermissions);

        // Kepala unit → verifikasi & kembalikan
        $roleInstances['kepala_unit']->syncPermissions([
            'Verifikasi:LaporanInsiden',
            'Kembalikan:LaporanInsiden',
        ]);

        // Pelapor → submit
        $roleInstances['pelapor']->syncPermissions([
            'Submit:LaporanInsiden',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default User
        |--------------------------------------------------------------------------
        */

        $adminUser = User::firstOrCreate(
            ['nip' => '0000.00000'],
            [
                'name' => 'Admin',
                'no_hp' => '081234567890',
                'password' => Hash::make('password'),
            ]
        );

        $adminUser->syncRoles(['super_admin']);
    }
}
