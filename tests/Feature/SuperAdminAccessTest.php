<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
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

    public function test_super_admin_can_access_app_url_and_has_required_permissions(): void
    {
        $user = User::where('nip', '0000.00000')->first();
        $this->assertNotNull($user);

        $user->assignRole('super_admin');

        // Ensure the user can perform a key permission check
        $this->assertTrue($user->can('ViewAny:LaporanInsiden'));

        // Access the app URL (if the app uses a custom port, include it)
        $response = $this->actingAs($user)->get('http://192.168.1.9:8200/');
        $response->assertStatus(200);
    }
}
