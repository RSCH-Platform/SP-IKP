<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'nip' => '0000.00000',
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            ShieldSeeder::class,
            LaporanInsidenSeeder::class,
        ]);
    }
}
