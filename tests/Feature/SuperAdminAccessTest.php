<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_user_with_nip_0000_can_access_app_url(): void
    {
        $user = User::where('nip', '0000.00000')->first();
        $this->assertNotNull($user, 'User with NIP 0000.00000 must exist for this test.');

        // Ensure super_admin role is assigned and has permissions
        $user->assignRole('super_admin');

        // Hit the app URL (replace with the actual panel URL if needed)
        $response = $this->actingAs($user)->get('http://192.168.1.9:8200/');

        $response->assertStatus(200);
    }
}
