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
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->string('pelapor_insiden_pasien')->nullable()->after('tanggal_masuk_rs');
            $table->string('pelapor_insiden_pasien_lainnya')->nullable()->after('pelapor_insiden_pasien');
            $table->string('insiden_menyangkut_pasien')->nullable()->after('pelapor_insiden_pasien_lainnya');
            $table->string('insiden_menyangkut_pasien_lainnya')->nullable()->after('insiden_menyangkut_pasien');
            $table->string('spesialisasi_pasien')->nullable()->after('insiden_menyangkut_pasien_lainnya');
            $table->string('spesialisasi_pasien_lainnya')->nullable()->after('spesialisasi_pasien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropColumn([
                'pelapor_insiden_pasien',
                'pelapor_insiden_pasien_lainnya',
                'insiden_menyangkut_pasien',
                'insiden_menyangkut_pasien_lainnya',
                'spesialisasi_pasien',
                'spesialisasi_pasien_lainnya',
            ]);
        });
    }
};
