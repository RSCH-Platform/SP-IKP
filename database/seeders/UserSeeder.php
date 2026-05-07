<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users    = json_decode(File::get(database_path('data/users.json')), true);
        $pivots   = json_decode(File::get(database_path('data/user_unit_kerja.json')), true);

        // --- 1. Upsert users ---
        foreach ($users as $data) {
            User::updateOrCreate(
                ['nip' => $data['nip']],
                [
                    'name'     => $data['nama'],
                    'no_hp'    => $data['nomor_telepon'] ?? null,
                    'password' => Hash::make('Rschjaya123'), // default password = NIP
                ]
            );
        }

        $this->command->info('Users seeded: ' . count($users) . ' records.');

        // --- 2. Seed pivot user_unit_kerja ---
        $userMap = User::pluck('id', 'nip');

        $rows = [];
        $seen = [];

        foreach ($pivots as $pivot) {
            $userId = $userMap[$pivot['user_nip']] ?? null;
            $unitId = $pivot['unit_kerja_id'];

            if (! $userId || ! $unitId) {
                continue;
            }

            $key = "{$userId}-{$unitId}";
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $rows[] = [
                'user_id'       => $userId,
                'unit_kerja_id' => $unitId,
                'created_at'    => $pivot['assigned_at'] ?? now(),
                'updated_at'    => $pivot['assigned_at'] ?? now(),
            ];
        }

        if (! empty($rows)) {
            DB::table('user_unit_kerja')->upsert(
                $rows,
                ['user_id', 'unit_kerja_id'],
                ['updated_at']
            );
        }

        $this->command->info('user_unit_kerja pivot seeded: ' . count($rows) . ' records.');

        // --- 3. Assign roles from user_roles.json ---
        $roleData     = json_decode(File::get(database_path('data/user_roles.json')), true);
        $existingRoles = Role::pluck('name')->flip(); // fast lookup set
        $userMap       = User::pluck('id', 'nip');    // refresh after upsert

        // Role mapping: jika role di JSON tidak ada di DB, gunakan mapping ini
        $roleMapping = [
            'validator_pic'   => 'kepala_unit',
            'pengumpul_data'  => 'pelapor',
        ];

        $assigned = 0;
        foreach ($roleData as $entry) {
            $user = User::find($userMap[$entry['user_nip']] ?? null);
            if (! $user) {
                continue;
            }

            // Tentukan role: gunakan mapping jika role tidak ada di DB
            if (isset($existingRoles[$entry['role_name']])) {
                $roleName = $entry['role_name'];
            } else {
                $roleName = $roleMapping[$entry['role_name']] ?? 'pelapor';
            }

            // Assign role jika belum dimiliki
            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
                $assigned++;
            }
        }

        $this->command->info("Roles assigned: {$assigned} new assignments.");
    }
}
