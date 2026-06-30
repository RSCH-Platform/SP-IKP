<?php

namespace Database\Factories;

use App\Models\LaporanInsiden;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LaporanInsiden>
 */
class LaporanInsidenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'unit_kerja_id' => \App\Models\UnitKerja::factory(),
            'nama_pelapor' => $this->faker->name(),
            'unit_kerja' => $this->faker->word(),
            'tanggal_lapor' => now()->toDateString(),
            'nomor_laporan' => $this->faker->unique()->numerify('INC-####'),
            'jenis_insiden' => 'KNC', // Kejadian Nyaris Cedera
            'tanggal_insiden' => now()->subDays(2)->toDateString(),
            'waktu_insiden' => now()->subDays(2)->toTimeString(),
            'lokasi_insiden' => $this->faker->word(),
            'nama_pasien' => $this->faker->name(),
            'nomor_rekam_medis' => $this->faker->numerify('RM-######'),
            'insiden_terjadi_pada' => 'Pasien',
            'dampak_insiden' => 'Ringan',
            'kategori_insiden' => 'Klinis',
            'status' => 'draft',
        ];
    }
}
