<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_insidens', function (Blueprint $table) {
            $table->id();

            // Data Pelapor
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_pelapor');
            $table->string('unit_kerja');
            $table->string('nomor_telepon')->nullable();
            $table->date('tanggal_lapor');

            // Data Insiden
            $table->string('nomor_laporan')->unique();
            $table->enum('jenis_insiden', [
                'KPC (Kondisi Potensial Cedera)',
                'KNC (Kejadian Nyaris Cedera)',
                'KTD (Kejadian Tidak Diharapkan)',
                'KTC (Kejadian Tidak Cedera)',
                'Sentinel'
            ]);
            $table->date('tanggal_insiden');
            $table->time('waktu_insiden');
            $table->string('lokasi_insiden');
            $table->string('nama_pasien')->nullable();
            $table->string('nomor_rekam_medis')->nullable();

            // Kronologi Insiden
            $table->enum('insiden_terjadi_pada', [
                'Pasien',
                'Petugas',
                'Pengunjung',
                'Lainnya'
            ])->default('Pasien');

            // Dampak Insiden
            $table->enum('dampak_insiden', [
                'Tidak ada cedera',
                'Cedera ringan',
                'Cedera sedang',
                'Cedera berat',
                'Meninggal'
            ])->default('Tidak ada cedera');

            // Kategori Insiden
            $table->enum('kategori_insiden', [
                'Medikasi',
                'Prosedur Klinik',
                'Dokumentasi',
                'Infeksi Nosokomial',
                'Jatuh',
                'Komunikasi',
                'Peralatan Medis',
                'Transfusi Darah',
                'Diet/Nutrisi',
                'Lainnya'
            ]);

            // Tindakan yang Dilakukan
            $table->text('tindakan_dilakukan')->nullable();

            // Analisis dan Rekomendasi
            $table->text('analisis_penyebab')->nullable();
            $table->text('rekomendasi')->nullable();

            // Status Laporan
            $table->enum('status', [
                'draft',
                'submitted',
                'reviewed',
                'closed'
            ])->default('draft');

            // Grading Risiko
            $table->enum('grading_risiko', [
                'Biru (Tidak signifikan)',
                'Hijau (Minor)',
                'Kuning (Moderat)',
                'Merah (Mayor)',
                'Hitam (Katastropik)'
            ])->nullable();

            $table->text('catatan_tambahan')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_insidens');
    }
};
