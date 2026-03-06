<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all permissions for your application
        $permissions = [
            // LaporanInsiden Permissions
            'ViewAny:LaporanInsiden',
            'View:LaporanInsiden',
            'Create:LaporanInsiden',
            'Update:LaporanInsiden',
            'Delete:LaporanInsiden',
            'Restore:LaporanInsiden',
            'ForceDelete:LaporanInsiden',
            'ForceDeleteAny:LaporanInsiden',
            'RestoreAny:LaporanInsiden',
            'Replicate:LaporanInsiden',
            'Reorder:LaporanInsiden',

            // Role Permissions
            'ViewAny:Role',
            'View:Role',
            'Create:Role',
            'Update:Role',
            'Delete:Role',
            'Restore:Role',
            'ForceDelete:Role',
            'ForceDeleteAny:Role',
            'RestoreAny:Role',
            'Replicate:Role',
            'Reorder:Role',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $panelUserRole = Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Give all permissions to super_admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);

        // Give panel_user basic viewAny permission
        $panelUserRole->syncPermissions([
            'ViewAny:LaporanInsiden',
        ]);

        // Give admin all LaporanInsiden permissions and Role management
        $adminRole->syncPermissions([
            'ViewAny:LaporanInsiden',
            'View:LaporanInsiden',
            'Create:LaporanInsiden',
            'Update:LaporanInsiden',
            'Delete:LaporanInsiden',
            'Restore:LaporanInsiden',
            'ForceDelete:LaporanInsiden',
            'ForceDeleteAny:LaporanInsiden',
            'RestoreAny:LaporanInsiden',
            'Replicate:LaporanInsiden',
            'Reorder:LaporanInsiden',
            'ViewAny:Role',
            'View:Role',
            'Create:Role',
            'Update:Role',
            'Delete:Role',
        ]);

        // Optional: Create or update default admin user with super_admin role
        $adminUser = User::firstOrCreate(
            ['nip' => '0000.00000'],
            [
                'name' => 'Admin',
                'no_hp' => '081234567890',
                'password' => bcrypt('password'),
            ]
        );

        // Assign super_admin role to admin user
        $adminUser->syncRoles(['super_admin']);
    }
}
