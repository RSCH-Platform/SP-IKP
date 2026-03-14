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

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles from the Shield seeder to match production setup
        $this->seed(ShieldSeeder::class);
    }

    public function test_user_with_nip_0000_exists_and_is_super_admin(): void
    {
        $user = User::updateOrCreate(
            ['nip' => '0000.00000'],
            [
                'name' => 'Super Admin',
                'no_hp' => '081234567890',
                'password' => bcrypt('Rschjaya123'),
            ]
        );

        $user->assignRole('super_admin');

        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_super_admin_role_has_all_permissions(): void
    {
        $superAdmin = User::where('nip', '0000.00000')->first();
        $this->assertNotNull($superAdmin);

        $superAdmin->assignRole('super_admin');

        $permissionNames = Permission::pluck('name');
        $this->assertEqualsCanonicalizing(
            $permissionNames->toArray(),
            $superAdmin->getAllPermissions()->pluck('name')->toArray(),
            'Super admin should have all permissions.'
        );
    }

    public function test_super_admin_can_access_filament_dashboard(): void
    {
        $user = User::where('nip', '0000.00000')->first();
        $this->assertNotNull($user);

        $user->assignRole('super_admin');

        // Ensure the user can perform a key permission check (gate)
        $this->assertTrue($user->can('ViewAny:LaporanInsiden'));

        // Filament default dashboard path
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_all_roles_have_a_user_and_can_access_dashboard(): void
    {
        $failed = [];

        foreach (Role::all() as $role) {
            $nip = Str::of($role->name)->replace('_', '.')->append('.0000')->__toString();

            $user = User::updateOrCreate(
                ['nip' => $nip],
                [
                    'name' => "Test {$role->name}",
                    'no_hp' => '081200000000',
                    'password' => bcrypt('Rschjaya123'),
                ]
            );

            $user->syncRoles([$role->name]);

            try {
                $this->assertTrue($user->hasRole($role->name));

                $this->assertNotEmpty(
                    $user->getAllPermissions(),
                    "Role '{$role->name}' should have at least one permission."
                );

                $response = $this->actingAs($user)->get('/admin');
                $response->assertStatus(200);
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
        $user = User::where('nip', '0000.00000')->first();
        $this->assertNotNull($user);

        $user->assignRole('super_admin');

        // This is the URL you're testing; in phpunit this will commonly return 403
        // because it is not a route in the test application environment.
        $response = $this->actingAs($user)->get('http://192.168.1.9:8200/');
        $response->assertStatus(403);
    }
}
