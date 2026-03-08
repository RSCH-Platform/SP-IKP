<?php

namespace Database\Seeders;

use App\Models\UnitKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(database_path('data/unit_kerja.json'));
        $units = json_decode($json, true);

        foreach ($units as $unit) {
            UnitKerja::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($unit['nama_unit'])],
                [
                    'unit_name'   => $unit['nama_unit'],
                    'description' => $unit['deskripsi'] ?? null,
                ]
            );
        }

        $this->command->info('UnitKerja seeded: ' . count($units) . ' records.');
    }
}
