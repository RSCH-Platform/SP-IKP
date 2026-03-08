<?php

namespace Database\Seeders;

use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

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
                    'password' => Hash::make($data['nip']), // default password = NIP
                ]
            );
        }

        $this->command->info('Users seeded: ' . count($users) . ' records.');

        // --- 2. Seed pivot user_unit_kerja ---
        // Build lookup maps to avoid N+1 queries
        $userMap     = User::pluck('id', 'nip');
        $unitKerjaMap = UnitKerja::pluck('id', 'slug');

        $rows = [];
        $seen = [];

        foreach ($pivots as $pivot) {
            $userId     = $userMap[$pivot['user_nip']] ?? null;
            $unitSlug   = \Illuminate\Support\Str::slug($pivot['unit_kerja_name']);
            $unitId     = $unitKerjaMap[$unitSlug] ?? null;

            if (! $userId || ! $unitId) {
                continue;
            }

            $key = "{$userId}-{$unitId}";
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $rows[] = [
                'user_id'      => $userId,
                'unit_kerja_id' => $unitId,
                'created_at'   => $pivot['assigned_at'] ?? now(),
                'updated_at'   => $pivot['assigned_at'] ?? now(),
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
    }
}
