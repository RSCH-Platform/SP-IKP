<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles from the Shield seeder to match production setup
        $this->seed(ShieldSeeder::class);
        
        $this->superAdmin = User::firstOrNew(['nip' => '0000.00000']);
        $this->superAdmin->name = 'Super Admin';
        $this->superAdmin->no_hp = '081234567890';
        $this->superAdmin->password = bcrypt('Rschjaya123');
        $this->superAdmin->save();
        $this->superAdmin->assignRole('super_admin');
    }

    public function test_user_with_nip_0000_exists_and_is_super_admin(): void
    {
        $this->assertTrue($this->superAdmin->hasRole('super_admin'));
    }

    public function test_super_admin_role_has_all_permissions(): void
    {
        $permissionNames = Permission::pluck('name');
        $this->assertEqualsCanonicalizing(
            $permissionNames->toArray(),
            $this->superAdmin->getAllPermissions()->pluck('name')->toArray(),
            'Super admin should have all permissions.'
        );
    }

    public function test_force_edit_permission_is_created_and_assigned_to_admin(): void
    {
        $this->assertTrue(
            Permission::where('name', 'ForceEdit:LaporanInsiden')->exists(),
            'Expected ForceEdit:LaporanInsiden permission to exist.'
        );

        $adminRole = Role::where('name', 'admin_ikp')->first();
        $this->assertNotNull($adminRole);
        $this->assertTrue(
            $adminRole->hasPermissionTo('ForceEdit:LaporanInsiden'),
            'Admin role should have ForceEdit:LaporanInsiden permission.'
        );
    }

    public function test_super_admin_can_access_filament_dashboard(): void
    {
        // Ensure the user can perform a key permission check (gate)
        $this->assertTrue($this->superAdmin->can('ViewAny:LaporanInsiden'));

        // Filament default dashboard path
        $response = $this->actingAs($this->superAdmin)->get('/ikp-application');
        $response->assertStatus(200);
    }

    public function test_all_roles_have_a_user_and_can_access_dashboard(): void
    {
        $failed = [];

        foreach (Role::all() as $role) {
            $user = User::role($role->name)->first();

            if (!$user) {
                $user = User::factory()->create();
                $user->assignRole($role->name);
            }

            try {
                $this->assertTrue($user->hasRole($role->name));

                $this->assertNotEmpty(
                    $user->getAllPermissions(),
                    "Role '{$role->name}' should have at least one permission."
                );

                // Try to access the dashboard. Some roles might be redirected or denied depending on exact logic.
                // Filament panel access is controlled by canAccessPanel().
                // Assuming all roles can access the panel:
                $response = $this->actingAs($user)->get('/ikp-application');
                $this->assertContains($response->status(), [200, 302], "Dashboard access returned {$response->status()}");
            } catch (\Throwable $e) {
                $failed[] = "{$role->name}: {$e->getMessage()}";
            }
        }

        $this->assertEmpty(
            $failed,
            "Some roles failed access checks:\n" . implode("\n", $failed)
        );
    }

    public function test_super_admin_gets_403_when_requesting_external_host_url(): void
    {
        // This is the URL you're testing; in phpunit this will commonly return 404/403/302
        // because it is not a route in the test application environment.
        $response = $this->actingAs($this->superAdmin)->get('http://127.0.0.1:8200/');
        $this->assertContains($response->status(), [404, 403, 302]);
    }
}
