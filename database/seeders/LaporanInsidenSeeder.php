<?php

namespace Database\Seeders;

use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LaporanInsidenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reporters = User::query()
            ->whereHas('unitKerjas')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['admin_ikp', 'super_admin_ikp']);
            })
            ->with('unitKerjas:id,unit_name')
            ->get();

        if ($reporters->isEmpty()) {
            $this->command->error('Tidak ada user non-admin yang memiliki unit kerja. Jalankan seeder User dan unitKerjas terlebih dahulu.');
            return;
        }

        $pickReporter = function () use ($reporters) {
            $reporter = $reporters->random();
            $unitKerjas = $reporter->unitKerjas->random();

            return [$reporter, $unitKerjas];
        };

        // Check if sample data already exists to prevent duplicates
        if (LaporanInsiden::where('nama_pasien', 'Ibu Aminah binti Sulaiman')->exists()) {
            $this->command->info('✅ Data contoh laporan insiden sudah ada, melewati seeding');
            return;
        }

        // Load data from JSON
        $jsonPath = database_path('seeders/data/laporan-insiden-seed.json');
        if (!File::exists($jsonPath)) {
            $this->command->error('File laporan-insiden-seed.json tidak ditemukan di ' . $jsonPath);
            return;
        }

        $seedData = json_decode(File::get($jsonPath), true);
        if (!is_array($seedData)) {
            $this->command->error('Format JSON tidak valid');
            return;
        }

        // Process each laporan dari JSON
        foreach ($seedData as $data) {
            [$reporter, $unitKerjas] = $pickReporter();

            // Hitung tanggal insiden
            $tanggalInsiden = now()->subDays($data['days_ago'] ?? 0);
            $tanggalLapor = $tanggalInsiden->copy()->addDays(1);
            $tanggalMasukRS = $tanggalInsiden->copy()->subDays(5 + rand(0, 3));

            // Create laporan insiden
            $laporan = LaporanInsiden::create([
                'user_id' => $reporter->id,
                'unit_kerja_id' => $unitKerjas->id,
                'nama_pelapor' => $reporter->name,
                'unit_kerja' => $unitKerjas->unit_name,
                'nomor_telepon' => $reporter->no_hp ?? '080000000000',
                'tanggal_lapor' => $tanggalLapor,
                'jenis_insiden' => $data['jenis_insiden'],
                'tanggal_insiden' => $tanggalInsiden,
                'waktu_insiden' => $data['waktu_insiden'],
                'lokasi_insiden' => $data['lokasi_insiden'],
                'nama_pasien' => $data['nama_pasien'],
                'nomor_rekam_medis' => $data['nomor_rekam_medis'],
                'ruangan' => $data['ruangan'],
                'umur' => $data['umur'],
                'kelompok_umur' => $data['kelompok_umur'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'penanggung_biaya' => $data['penanggung_biaya'],
                'tanggal_masuk_rs' => $tanggalMasukRS,
                'pelapor_insiden_pasien' => $data['pelapor_insiden_pasien'],
                'pelapor_insiden_pasien_lainnya' => $data['pelapor_insiden_pasien_lainnya'] ?? null,
                'insiden_menyangkut_pasien' => $data['insiden_menyangkut_pasien'],
                'insiden_menyangkut_pasien_lainnya' => $data['insiden_menyangkut_pasien_lainnya'] ?? null,
                'spesialisasi_pasien' => $data['spesialisasi_pasien'],
                'spesialisasi_pasien_lainnya' => $data['spesialisasi_pasien_lainnya'] ?? null,
                'insiden_terjadi_pada' => $data['insiden_terjadi_pada'],
                'kategori_insiden' => $data['kategori_insiden'],
                'deskripsi_kategori_insiden' => $data['deskripsi_kategori_insiden'],
                'dampak_insiden' => $data['dampak_insiden'],
                'tindakan_dilakukan' => $data['tindakan_dilakukan'],
                'tindakan_dilakukan_oleh' => $data['tindakan_dilakukan_oleh'],
                'tindakan_dilakukan_oleh_lainnya' => $data['tindakan_dilakukan_oleh_lainnya'] ?? null,
                'kejadian_pernah_terjadi_sebelumnya' => $data['kejadian_pernah_terjadi_sebelumnya'],
                'kejadian_pernah_terjadi_sebelumnya_deskripsi' => $data['kejadian_pernah_terjadi_sebelumnya_deskripsi'] ?? null,
                'status' => $data['status'],
                'reported_by' => $reporter->id,
                'reported_at' => $tanggalLapor,
                'grading_risiko' => $data['grading_risiko'] ?? null,
                'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            ]);

            // Jika status sudah diverifikasi atau investigasi, set verified_by dan verified_at
            if (in_array($data['status'], ['diverifikasi', 'investigasi'])) {
                $laporan->update([
                    'verified_by' => $reporter->id,
                    'verified_at' => $tanggalLapor->copy()->addHours(2),
                ]);
            }

            // Create timeline entries from JSON
            if (!empty($data['timeline_entries'])) {
                $this->createTimelineForReport($laporan, [
                    [
                        'event_datetime' => $tanggalInsiden,
                        'entries' => $data['timeline_entries'],
                    ]
                ]);
            }

            $this->command->info("✅ Berhasil membuat laporan: {$data['nama_pasien']} - {$data['jenis_insiden']}");
        }

        $this->command->info('✅ Seeding laporan insiden berhasil');
    }

    private function createTimelineForReport(LaporanInsiden $laporan, array $events): void
    {
        $categoryMap = TimelineCategory::all()->keyBy('code');

        foreach ($events as $event) {
            $timelineEvent = $laporan->timelineEvents()->create([
                'event_datetime' => $event['event_datetime'] ?? now(),
                'created_by' => $laporan->user_id,
            ]);

            foreach ($event['entries'] as $entry) {
                $categoryId = $entry['category_id'] ?? null;

                if (! $categoryId && isset($entry['category_code'])) {
                    $category = $categoryMap[$entry['category_code']] ?? null;

                    if (! $category) {
                        $category = TimelineCategory::firstOrCreate(
                            ['code' => $entry['category_code']],
                            ['name' => ucfirst(str_replace('_', ' ', $entry['category_code'])), 'sort_order' => 999]
                        );
                        $categoryMap[$entry['category_code']] = $category;
                    }

                    $categoryId = $category->id;
                }

                if (! $categoryId) {
                    continue;
                }

                $timelineEvent->entries()->updateOrCreate(
                    ['category_id' => $categoryId],
                    [
                        'description' => $entry['description'] ?? '',
                        'created_by' => $laporan->user_id,
                    ]
                );
            }
        }
    }
}