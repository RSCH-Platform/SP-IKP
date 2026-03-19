<?php

namespace Database\Seeders;

use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanInsidenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reporters = User::query()
            ->whereHas('unitKerja')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })
            ->with('unitKerja:id,unit_name')
            ->get();

        if ($reporters->isEmpty()) {
            $this->command->error('Tidak ada user non-admin yang memiliki unit kerja. Jalankan seeder User dan UnitKerja terlebih dahulu.');
            return;
        }

        $pickReporter = function () use ($reporters) {
            $reporter = $reporters->random();
            $unitKerja = $reporter->unitKerja->random();

            return [$reporter, $unitKerja];
        };

        // Check if sample data already exists to prevent duplicates
        if (LaporanInsiden::where('nama_pasien', 'Ibu Aminah binti Sulaiman')->exists()) {
            $this->command->info('✅ Data contoh laporan insiden sudah ada, melewati seeding');
            return;
        }

        // Laporan 1: KTD - Pasien Jatuh dari Tempat Tidur
        [$reporter, $unitKerja] = $pickReporter();

        $kronologi1 = "Pada tanggal " . now()->subDays(3)->format('d F Y') . " pukul 14.30 WIB, pasien Ny. Aminah (67 tahun) sedang beristirahat di tempat tidur ruang Mawar bed 12 setelah selesai makan siang. Pasien dalam kondisi post-operasi katarak hari ke-2.\n\nPada saat perawat sedang melakukan visite ke pasien lain, pasien mencoba turun dari tempat tidur sendiri tanpa memanggil perawat karena ingin ke kamar mandi. Side rail/pengaman tempat tidur dalam posisi terbuka karena sebelumnya perawat sedang memberikan obat oral dan lupa menutup kembali.\n\nKetika pasien mencoba turun, kakinya terpeleset dan jatuh ke lantai dengan posisi miring ke kanan. Terdengar suara keras yang membuat keluarga pasien di bed sebelah berteriak memanggil perawat. Perawat segera datang dan menemukan pasien terjatuh di samping tempat tidur dengan mengeluh nyeri pada pinggul kanan.\n\nKeluarga pasien yang sedang keluar membeli makan tidak berada di ruangan saat kejadian terjadi.";

        $laporan1 = LaporanInsiden::create([
            'user_id' => $reporter->id,
            'unit_kerja_id' => $unitKerja->id,
            'nama_pelapor' => $reporter->name,
            'unit_kerja' => $unitKerja->unit_name,
            'nomor_telepon' => $reporter->no_hp ?? '080000000000',
            'tanggal_lapor' => now()->subDays(2),
            'jenis_insiden' => 'KTD (Kejadian Tidak Diharapkan)',
            'tanggal_insiden' => now()->subDays(3),
            'waktu_insiden' => '14:30:00',
            'lokasi_insiden' => 'Ruang Mawar, Bed 12',
            'nama_pasien' => 'Ibu Aminah binti Sulaiman',
            'nomor_rekam_medis' => 'RM-2024-001234',
            'ruangan' => 'Ruang Mawar',
            'umur' => 67,
            'kelompok_umur' => '>65 tahun',
            'jenis_kelamin' => 'Perempuan',
            'penanggung_biaya' => 'BPJS',
            'tanggal_masuk_rs' => now()->subDays(5),
            'pelapor_insiden_pasien' => 'perawat',
            'pelapor_insiden_pasien_lainnya' => null,
            'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
            'insiden_menyangkut_pasien_lainnya' => null,
            'spesialisasi_pasien' => 'bedah',
            'spesialisasi_pasien_lainnya' => null,
            'kronologi' => '',
            'insiden_terjadi_pada' => 'Pasien',
            'kategori_insiden' => 'Pasien Jatuh',
            'deskripsi_kategori_insiden' => 'Pasien jatuh dari tempat tidur saat mencoba turun sendiri tanpa memanggil perawat. Side rail tempat tidur dalam kondisi terbuka karena perawat lupa menutup setelah pemberian obat. Pasien post-operasi katarak hari ke-2 dengan faktor risiko usia lanjut (67 tahun) dan penggunaan obat antihipertensi yang dapat menyebabkan pusing. Jatuh mengakibatkan fraktur collum femur dextra yang memerlukan tindakan operasi ORIF.',
            'dampak_insiden' => 'Cedera sedang',
            'tindakan_dilakukan' => "1. Segera membantu pasien dengan hati-hati, memastikan pasien tidak dipindahkan secara tiba-tiba untuk menghindari cedera lebih lanjut\n\n2. Melakukan pemeriksaan kesadaran dan tanda vital:\n   - Kesadaran: Composmentis\n   - TD: 150/90 mmHg\n   - Nadi: 98x/menit\n   - RR: 22x/menit\n   - Suhu: 36.8°C\n\n3. Melakukan pemeriksaan fisik area yang mengeluh nyeri (pinggul kanan), ditemukan bengkak dan nyeri tekan\n\n4. Segera menghubungi dokter jaga (dr. Ahmad Fauzi, Sp.B) untuk melaporkan kejadian dan meminta instruksi\n\n5. Atas instruksi dokter, memindahkan pasien ke tempat tidur dengan bantuan 3 orang perawat menggunakan teknik log-rolling yang benar\n\n6. Memberikan kompres dingin pada area yang bengkak\n\n7. Memberikan analgesik sesuai advice dokter (Ketorolac 30mg IV)\n\n8. Melakukan observasi ketat tanda vital setiap 15 menit selama 1 jam pertama\n\n9. Dokter melakukan pemeriksaan dan memutuskan untuk dilakukan foto rontgen pelvis dan femur kanan\n\n10. Hasil rontgen menunjukkan fraktur collum femur dextra, pasien dikonsulkan ke Sp.OT untuk rencana operasi ORIF\n\n11. Menjelaskan kejadian kepada keluarga pasien dan meminta persetujuan tindakan operasi\n\n12. Mendokumentasikan seluruh kejadian di rekam medis dan membuat laporan insiden\n\n13. Memasang side rail pada posisi terkunci dan menambahkan stiker \"Risiko Jatuh Tinggi\" di tempat tidur pasien\n\n14. Melaporkan kejadian kepada Kepala Ruangan dan Tim IKP",
            'tindakan_dilakukan_oleh' => 'Perawat',
            'tindakan_dilakukan_oleh_lainnya' => null,
            'kejadian_pernah_terjadi_sebelumnya' => 'Tidak',
            'kejadian_pernah_terjadi_sebelumnya_deskripsi' => null,
            'status' => 'dilaporkan',
            'reported_by' => $reporter->id,
            'reported_at' => now()->subDays(2),
            'grading_risiko' => 'Kuning',
            'catatan_tambahan' => 'Side rail tidak terpasang dengan benar. Pasien tidak menggunakan bel panggilan yang sudah tersedia. Perlu edukasi ulang kepada pasien dan keluarga tentang pencegahan jatuh.',
        ]);

        $this->createTimelineForReport($laporan1, [
            [
                'event_datetime' => $laporan1->tanggal_insiden,
                'entries' => [
                    [
                        'category_code' => 'kejadian',
                        'description' => $kronologi1,
                    ],
                    [
                        'category_code' => 'informasi',
                        'description' => 'Pasien post-operasi katarak hari ke-2 dengan faktor risiko usia lanjut, menggunakan antihipertensi, dan tidak menggunakan bel panggilan saat mencoba turun sendiri.',
                    ],
                    [
                        'category_code' => 'good_practice',
                        'description' => 'Petugas segera menempatkan side rail pada posisi terkunci, menempelkan label "Risiko Jatuh Tinggi", dan mengingatkan keluarga untuk selalu memantau pasien.',
                    ],
                ],
            ],
        ]);

        // Laporan 2: KNC - Kesalahan Pemberian Obat yang Terdeteksi
        [$reporter, $unitKerja] = $pickReporter();

        $kronologi2 = "Pada tanggal " . now()->subDays(1)->format('d F Y') . " pukul 08.15 WIB, pasien Tn. Rahmat (45 tahun) datang ke IGD dengan keluhan nyeri dada dan sesak napas. Setelah dilakukan pemeriksaan awal dan EKG, dokter jaga (dr. Lisa Permata, Sp.JP) memberikan instruksi verbal untuk pemberian:\n- Aspilet 1x160mg PO\n- ISDN 5mg SL\n- Clopidogrel 1x75mg PO\n\nPetugas farmasi menyiapkan obat dan menyerahkan kepada perawat. Saat perawat akan memberikan obat kepada pasien, perawat lain (Ns. Dewi) yang kebetulan lewat melihat obat yang akan diberikan dan menanyakan \"Ini untuk pasien mana?\"\n\nSetelah dicek kembali, ternyata obat yang disiapkan adalah:\n- Aspilet 1x160mg ✓ (benar)\n- ISDN 5mg SL ✓ (benar) \n- Clopidogrel 1x300mg PO ✗ (SALAH DOSIS - seharusnya 75mg)\n\nKesalahan dosis ini terdeteksi sebelum obat diberikan kepada pasien. Perawat segera mengkonfirmasi ulang ke dokter dan menukar obat dengan dosis yang benar (75mg) sebelum diberikan kepada pasien.";

        $laporan2 = LaporanInsiden::create([
            'user_id' => $reporter->id,
            'unit_kerja_id' => $unitKerja->id,
            'nama_pelapor' => $reporter->name,
            'unit_kerja' => $unitKerja->unit_name,
            'nomor_telepon' => $reporter->no_hp ?? '080000000000',
            'tanggal_lapor' => now()->subDays(1),
            'jenis_insiden' => 'KNC (Kejadian Nyaris Cedera)',
            'tanggal_insiden' => now()->subDays(1),
            'waktu_insiden' => '08:15:00',
            'lokasi_insiden' => 'IGD Ruang Tindakan',
            'nama_pasien' => 'Tn. Rahmat Hidayat',
            'nomor_rekam_medis' => 'RM-2024-005678',
            'ruangan' => 'IGD',
            'umur' => 45,
            'kelompok_umur' => '>30 tahun - 65 tahun',
            'jenis_kelamin' => 'Laki-laki',
            'penanggung_biaya' => 'Asuransi Swasta',
            'tanggal_masuk_rs' => now()->subDays(1)->setTime(7, 30),
            'pelapor_insiden_pasien' => 'petugas_lain',
            'pelapor_insiden_pasien_lainnya' => 'Petugas laboratorium yang sedang audit',
            'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
            'insiden_menyangkut_pasien_lainnya' => null,
            'spesialisasi_pasien' => 'penyakit_dalam',
            'spesialisasi_pasien_lainnya' => null,
            'kronologi' => '',
            'insiden_terjadi_pada' => 'Pasien',
            'kategori_insiden' => 'Medication / Cairan IV',
            'deskripsi_kategori_insiden' => 'Kesalahan dosis Clopidogrel yang disiapkan oleh farmasi. Farmasi menyiapkan Clopidogrel 300mg sedangkan yang diresepkan dokter adalah 75mg. Kesalahan terjadi karena kurangnya komunikasi antara dokter-farmasi-perawat dan tidak adanya double-check saat penyiapan obat. Beruntung kesalahan terdeteksi oleh perawat lain sebelum obat diberikan kepada pasien sehingga tidak menimbulkan cedera.',
            'dampak_insiden' => 'Tidak ada cedera',
            'tindakan_dilakukan' => "1. Segera menghentikan pemberian obat dan melakukan double-check terhadap instruksi dokter\n\n2. Mengkonfirmasi ulang dosis Clopidogrel kepada dokter penanggung jawab (dr. Lisa Permata, Sp.JP)\n\n3. Dokter mengkonfirmasi bahwa dosis yang benar adalah 75mg (loading dose untuk kasus ini seharusnya 300mg, tetapi pasien sudah pernah konsumsi Clopidogrel sebelumnya)\n\n4. Mengembalikan Clopidogrel 300mg ke farmasi dan meminta Clopidogrel 75mg yang benar\n\n5. Melakukan verifikasi ulang dengan prinsip 6 benar:\n   - Benar pasien ✓\n   - Benar obat ✓\n   - Benar dosis ✓ (75mg)\n   - Benar rute ✓ (PO)\n   - Benar waktu ✓\n   - Benar dokumentasi ✓\n\n6. Memberikan obat yang benar kepada pasien pada pukul 08.25 WIB (terlambat 10 menit dari seharusnya)\n\n7. Pasien tidak mengalami adverse event karena kesalahan terdeteksi sebelum obat diberikan\n\n8. Melakukan klarifikasi dengan petugas farmasi tentang kesalahan penyiapan obat\n\n9. Mendokumentasikan kejadian di rekam medis dan membuat laporan KNC (Kejadian Nyaris Cedera)\n\n10. Melaporkan kepada Kepala IGD dan Tim Farmasi untuk evaluasi sistem\n\n11. Memberikan apresiasi kepada Ns. Dewi yang telah membantu mendeteksi kesalahan sebelum obat diberikan",
            'tindakan_dilakukan_oleh' => 'Perawat',
            'tindakan_dilakukan_oleh_lainnya' => null,
            'kejadian_pernah_terjadi_sebelumnya' => 'Ya',
            'kejadian_pernah_terjadi_sebelumnya_deskripsi' => 'Sebelumnya pernah terjadi kesalahan pemberian obat di IGD karena tidak adanya double-check. Tim farmasi memutuskan untuk menambah checklist 6 benar dalam proses pengambilan obat.',
            'status' => 'investigasi',
            'reported_by' => $reporter->id,
            'reported_at' => now()->subDays(1),
            'verified_by' => $reporter->id,
            'verified_at' => now()->subDays(1)->addHours(2),
            'grading_risiko' => 'Hijau',
            'catatan_tambahan' => 'Kejadian ini menunjukkan pentingnya double-check sebelum pemberian obat. Perlu perbaikan sistem komunikasi antara dokter-farmasi-perawat dan penerapan CPPT (Catatan Perkembangan Pasien Terintegrasi) secara konsisten.',
        ]);

        $this->createTimelineForReport($laporan2, [
            [
                'event_datetime' => $laporan2->tanggal_insiden,
                'entries' => [
                    [
                        'category_code' => 'kejadian',
                        'description' => $kronologi2,
                    ],
                    [
                        'category_code' => 'informasi',
                        'description' => 'Kesalahan dosis terdeteksi sebelum obat diberikan. Proses penyerahan obat dari farmasi ke perawat tidak disertai checklist pemeriksaan 6 benar.',
                    ],
                    [
                        'category_code' => 'cmp',
                        'description' => 'Ditemukan kekurangan pada prosedur double-check pemberian obat di IGD; perlu menstandarisasi proses verifikasi obat sebelum diberikan kepada pasien.',
                    ],
                    [
                        'category_code' => 'sdp',
                        'description' => 'Usulkan penambahan kolom "verifikasi 6 benar" di sistem farmasi/rekam medis untuk mencatat pembacaan kode obat dan dosis sebelum diserahkan.',
                    ],
                ],
            ],
        ]);

        // Laporan 3: KTD - Infeksi Nosokomial Luka Operasi
        [$reporter, $unitKerja] = $pickReporter();

        $kronologi3 = "Pasien Ny. Sari (52 tahun) menjalani operasi appendektomi (pengangkatan usus buntu) pada tanggal " . now()->subDays(7)->format('d F Y') . " pukul 10.00 WIB di Kamar Operasi 2.\n\nOperasi berjalan lancar dengan durasi 1 jam 15 menit. Teknik aseptik dan antiseptik telah dilakukan sesuai SOP. Pasien dipindahkan ke ruang pemulihan dalam kondisi stabil.\n\nPada hari ke-3 post operasi (" . now()->subDays(4)->format('d F Y') . "), pasien mengeluh nyeri pada area luka operasi yang semakin meningkat. Perawat melaporkan kepada dokter bahwa:\n- Luka operasi tampak kemerahan di sekitar jahitan\n- Terdapat pembengkakan (edema) di area insisi\n- Keluar cairan serous dari luka\n- Suhu pasien meningkat menjadi 38.5°C\n- Pasien mengeluh nyeri skala 7/10\n\nDokter melakukan pemeriksaan dan mencurigai adanya infeksi luka operasi (Surgical Site Infection/SSI). Dilakukan kultur pus dan tes sensitivitas antibiotik.\n\nHasil kultur (hari ke-5 post-op) menunjukkan pertumbuhan bakteri Staphylococcus aureus yang resisten terhadap beberapa antibiotik.\n\nPasien didiagnosis dengan Infeksi Nosokomial - Surgical Site Infection (SSI) superfisial.";

        $laporan3 = LaporanInsiden::create([
            'user_id' => $reporter->id,
            'unit_kerja_id' => $unitKerja->id,
            'nama_pelapor' => $reporter->name,
            'unit_kerja' => $unitKerja->unit_name,
            'nomor_telepon' => $reporter->no_hp ?? '080000000000',
            'tanggal_lapor' => now(),
            'jenis_insiden' => 'KTD (Kejadian Tidak Diharapkan)',
            'tanggal_insiden' => now()->subDays(7),
            'waktu_insiden' => '10:00:00',
            'lokasi_insiden' => 'Kamar Operasi 2',
            'nama_pasien' => 'Ny. Sari Wulandari',
            'nomor_rekam_medis' => 'RM-2024-007890',
            'ruangan' => 'Ruang Melati',
            'umur' => 52,
            'kelompok_umur' => '>30 tahun - 65 tahun',
            'jenis_kelamin' => 'Perempuan',
            'penanggung_biaya' => 'BPJS',
            'tanggal_masuk_rs' => now()->subDays(10),
            'pelapor_insiden_pasien' => 'keluarga',
            'pelapor_insiden_pasien_lainnya' => null,
            'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
            'insiden_menyangkut_pasien_lainnya' => null,
            'spesialisasi_pasien' => 'bedah',
            'spesialisasi_pasien_lainnya' => null,
            'kronologi' => '',
            'insiden_terjadi_pada' => 'Pasien',
            'kategori_insiden' => 'Infeksi Terkait Pelayanan Kesehatan',
            'deskripsi_kategori_insiden' => 'Infeksi luka operasi (Surgical Site Infection/SSI) superfisial yang terjadi pada hari ke-3 pasca appendektomi. Hasil kultur menunjukkan pertumbuhan Staphylococcus aureus. Diduga terkait dengan kemungkinan kontaminasi saat prosedur operasi atau saat perawatan luka post-operasi. Mengakibatkan perpanjangan masa rawat inap dari 5 hari menjadi 12 hari dan kebutuhan terapi antibiotik tambahan.',
            'dampak_insiden' => 'Cedera sedang',
            'tindakan_dilakukan' => "1. Segera melakukan pemeriksaan fisik menyeluruh pada area luka operasi\n\n2. Mengambil sampel kultur pus dari luka untuk pemeriksaan mikrobiologi dan tes sensitivitas\n\n3. Melakukan pemeriksaan penunjang:\n   - Darah lengkap: Leukosit 15.000/mm³ (meningkat)\n   - LED: 45 mm/jam (meningkat)\n   - CRP: 12 mg/dL (positif)\n\n4. Mengganti antibiotik profilaksis menjadi antibiotik empiris broad-spectrum (Ceftriaxone 2x1gr IV + Metronidazole 3x500mg IV) sambil menunggu hasil kultur\n\n5. Melakukan perawatan luka dengan teknik steril:\n   - Membersihkan luka dengan NaCl 0.9%\n   - Mengangkat jahitan yang terinfeksi\n   - Drainase pus\n   - Menutup luka dengan kassa steril\n   - Ganti balutan 2x sehari\n\n6. Memberikan analgesik untuk mengurangi nyeri (Ketorolac 3x30mg IV)\n\n7. Memberikan antipiretik untuk demam (Paracetamol 3x1gr PO)\n\n8. Melakukan observasi ketat tanda vital dan kondisi luka setiap 6 jam\n\n9. Setelah hasil kultur keluar (hari ke-5), mengganti antibiotik sesuai tes sensitivitas (Vancomycin 2x1gr IV)\n\n10. Memberikan edukasi kepada pasien dan keluarga tentang kondisi dan rencana perawatan\n\n11. Memperpanjang masa rawat inap dari rencana 5 hari menjadi 12 hari untuk memastikan infeksi teratasi\n\n12. Melakukan investigasi terhadap kemungkinan sumber infeksi:\n    - Review sterility kamar operasi\n    - Review teknik aseptik tim bedah\n    - Kultur lingkungan kamar operasi\n\n13. Melaporkan kejadian ke Tim PPI (Pencegahan dan Pengendalian Infeksi) dan Tim IKP\n\n14. Mendokumentasikan seluruh kejadian di rekam medis",
            'tindakan_dilakukan_oleh' => 'Dokter',
            'tindakan_dilakukan_oleh_lainnya' => null,
            'kejadian_pernah_terjadi_sebelumnya' => 'Ya',
            'kejadian_pernah_terjadi_sebelumnya_deskripsi' => 'Sebelumnya pernah terjadi SSI di kamar operasi ke-2, sehingga saat ini dilakukan audit sterilisasi lebih ketat.',
            'status' => 'diverifikasi',
            'reported_by' => $reporter->id,
            'reported_at' => now()->subHours(12),
            'verified_by' => $reporter->id,
            'verified_at' => now()->subHours(6),
            'grading_risiko' => 'Kuning',
            'catatan_tambahan' => 'Perlu dilakukan audit menyeluruh terhadap prosedur sterilisasi di kamar operasi dan kepatuhan tim bedah terhadap SOP pencegahan infeksi. Surveillance SSI perlu ditingkatkan.',
        ]);

        $this->createTimelineForReport($laporan3, [
            [
                'event_datetime' => $laporan3->tanggal_insiden,
                'entries' => [
                    [
                        'category_code' => 'kejadian',
                        'description' => $kronologi3,
                    ],
                    [
                        'category_code' => 'informasi',
                        'description' => 'Hasil kultur menunjukkan Staphylococcus aureus resisten beberapa antibiotik; pasien menunjukkan tanda-tanda sistemik seperti demam dan leukositosis.',
                    ],
                    [
                        'category_code' => 'good_practice',
                        'description' => 'Tim melakukan perawatan luka steril, mengganti antibiotik sesuai sensitivitas, dan melakukan audit sterilisasi kamar operasi untuk mencegah kejadian serupa.',
                    ],
                    [
                        'category_code' => 'cmp',
                        'description' => 'Perlu penyempurnaan checklist sterilisasi dan dokumentasi prosedur aseptik di ruang operasi untuk mengurangi risiko SSI.',
                    ],
                ],
            ],
        ]);

        // Laporan 4: KTC - Kesalahan Identifikasi Pasien (Terdeteksi)
        [$reporter, $unitKerja] = $pickReporter();

        $kronologi4 = "Pada tanggal " . now()->format('d F Y') . " pukul " . now()->subHours(6)->format('H:i') . " WIB, terdapat 2 pasien dengan nama yang mirip datang ke laboratorium untuk pengambilan sample darah:\n\n1. Tn. Bambang Sutrisno (58 tahun) - RM: 2024-009012\n   Pemeriksaan: Profil Lipid, GDS, HbA1c\n   \n2. Tn. Bambang Sutriono (56 tahun) - RM: 2024-009015  \n   Pemeriksaan: Fungsi Hati, Fungsi Ginjal\n\nKedua pasien dipanggil hampir bersamaan oleh 2 petugas laboratorium yang berbeda. Petugas A memanggil \"Bapak Bambang\" untuk Tn. Sutrisno, namun yang masuk adalah Tn. Sutriono.\n\nPetugas A sudah menyiapkan tabung sample dengan label nama \"Tn. Bambang Sutrisno - RM 2024-009012\" dan hampir melakukan pengambilan darah.\n\nNamun, sebelum jarum ditusukkan, petugas mengecek kembali identitas dengan bertanya:\n- \"Nama lengkap Bapak?\"\n- Pasien menjawab: \"Bambang Sutriono\"\n\nPetugas menyadari ini bukan pasien yang dimaksud, segera menghentikan tindakan dan meminta pasien untuk kembali ke ruang tunggu. Petugas kemudian memanggil ulang dengan menyebutkan nama lengkap dan nomor rekam medis.\n\nTn. Bambang Sutrisno yang benar kemudian masuk dan pengambilan darah dilakukan dengan identifikasi yang benar.";

        $laporan4 = LaporanInsiden::create([
            'user_id' => $reporter->id,
            'unit_kerja_id' => $unitKerja->id,
            'nama_pelapor' => $reporter->name,
            'unit_kerja' => $unitKerja->unit_name,
            'nomor_telepon' => $reporter->no_hp ?? '080000000000',
            'tanggal_lapor' => now()->subHours(5),
            'jenis_insiden' => 'KTC (Kejadian Tidak Cedera)',
            'tanggal_insiden' => now()->subHours(6),
            'waktu_insiden' => now()->subHours(6)->format('H:i:s'),
            'lokasi_insiden' => 'Ruang Pengambilan Sample Darah - Laboratorium',
            'nama_pasien' => 'Tn. Bambang Sutrisno',
            'nomor_rekam_medis' => 'RM-2024-009012',
            'ruangan' => 'Poliklinik Penyakit Dalam',
            'umur' => 58,
            'kelompok_umur' => '>30 tahun - 65 tahun',
            'jenis_kelamin' => 'Laki-laki',
            'penanggung_biaya' => 'Pribadi',
            'pelapor_insiden_pasien' => 'petugas_lain',
            'pelapor_insiden_pasien_lainnya' => 'Petugas laboratorium',
            'insiden_menyangkut_pasien' => 'pasien_rawat_inap',
            'insiden_menyangkut_pasien_lainnya' => null,
            'spesialisasi_pasien' => 'laboratorium',
            'spesialisasi_pasien_lainnya' => null,
            'kronologi' => '',
            'insiden_terjadi_pada' => 'Pasien',
            'kategori_insiden' => 'Dokumentasi Klinis',
            'deskripsi_kategori_insiden' => 'Hampir terjadi kesalahan identifikasi pasien di laboratorium akibat kesamaan nama antara dua pasien yang datang bersamaan (Bambang Sutrisno vs Bambang Sutriono). Petugas laboratorium hanya memanggil nama depan "Bapak Bambang" tanpa menyebutkan nama lengkap atau nomor rekam medis, sehingga pasien yang salah masuk ke area pengambilan darah. Kesalahan berhasil dicegah karena petugas melakukan verifikasi nama lengkap sebelum tindakan.',
            'dampak_insiden' => 'Tidak ada cedera',
            'tindakan_dilakukan' => "1. Segera menghentikan tindakan pengambilan darah sebelum jarum ditusukkan\n\n2. Meminta pasien yang salah (Tn. Sutriono) untuk kembali ke ruang tunggu dengan penjelasan yang sopan\n\n3. Melakukan identifikasi ulang dengan menerapkan 2 identitas (nama lengkap + tanggal lahir atau nomor rekam medis):\n   - \"Nama lengkap Bapak?\"\n   - \"Tanggal lahir Bapak?\"\n   - Mencocokkan dengan gelang identitas pasien\n\n4. Memanggil pasien yang benar (Tn. Bambang Sutrisno - RM 2024-009012) dengan menyebutkan nama lengkap dan nomor rekam medis\n\n5. Melakukan verifikasi identitas sebelum pengambilan darah:\n   - Meminta pasien menyebutkan nama lengkap\n   - Meminta pasien menyebutkan tanggal lahir\n   - Mencocokkan dengan gelang identitas\n   - Mencocokkan dengan formulir permintaan lab\n\n6. Melakukan pengambilan sample darah untuk Tn. Sutrisno dengan benar:\n   - Profil Lipid (tabung tutup merah)\n   - GDS - Gula Darah Sewaktu (tabung tutup abu-abu)\n   - HbA1c (tabung EDTA tutup ungu)\n\n7. Memastikan label pada tabung sample sudah benar sebelum dikirim ke laboratorium\n\n8. Memanggil Tn. Bambang Sutriono dengan metode identifikasi yang lebih jelas (menyebutkan nama lengkap + RM)\n\n9. Melakukan pengambilan sample untuk Tn. Sutriono dengan prosedur identifikasi yang benar\n\n10. Melaporkan kejadian kepada Koordinator Laboratorium\n\n11. Mengingatkan seluruh petugas lab untuk lebih hati-hati dalam identifikasi pasien, terutama untuk nama yang mirip\n\n12. Membuat catatan di papan informasi: \"Hari ini ada 2 pasien dengan nama mirip - Sutrisno vs Sutriono - Perhatikan identifikasi!\"\n\n13. Mendokumentasikan kejadian sebagai pembelajaran untuk mencegah kesalahan serupa",
            'tindakan_dilakukan_oleh' => 'Perawat',
            'tindakan_dilakukan_oleh_lainnya' => null,
            'kejadian_pernah_terjadi_sebelumnya' => 'Tidak',
            'kejadian_pernah_terjadi_sebelumnya_deskripsi' => null,
            'status' => 'draft',
            'grading_risiko' => null,
            'catatan_tambahan' => 'Insiden ini menunjukkan pentingnya prosedur identifikasi pasien yang ketat. Perlu diterapkan sistem pemanggilan pasien yang lebih aman, misalnya menggunakan nomor antrian atau menyebutkan nama lengkap + tanggal lahir. Pertimbangkan implementasi barcode scanning untuk identifikasi pasien.',
        ]);

        $this->createTimelineForReport($laporan4, [
            [
                'event_datetime' => $laporan4->tanggal_insiden,
                'entries' => [
                    [
                        'category_code' => 'kejadian',
                        'description' => $kronologi4,
                    ],
                    [
                        'category_code' => 'good_practice',
                        'description' => 'Petugas laboratorium menghentikan tindakan tepat sebelum terjadi kesalahan, kemudian menerapkan tata cara identifikasi pasien 2 faktor (nama lengkap + nomor rekam medis).',
                    ],
                    [
                        'category_code' => 'informasi',
                        'description' => 'Kedua pasien datang hampir bersamaan dengan nama yang mirip; identifikasi awal menggunakan nama depan saja tidak cukup dan perlu standar pemanggilan yang lebih jelas.',
                    ],
                ],
            ],
        ]);

        $this->command->info('✅ Berhasil membuat 4 contoh data laporan insiden');
        $this->command->info('   - 1 laporan KTD (Pasien jatuh) - Status: dilaporkan - Grading: Kuning');
        $this->command->info('   - 1 laporan KNC (Kesalahan obat) - Status: investigasi - Grading: Hijau');
        $this->command->info('   - 1 laporan KTD (Infeksi nosokomial) - Status: diverifikasi - Belum grading');
        $this->command->info('   - 1 laporan KTC (Kesalahan identifikasi) - Status: draft - Belum grading');
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
