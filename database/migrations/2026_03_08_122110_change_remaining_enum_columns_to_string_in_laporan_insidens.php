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
            $table->string('jenis_insiden')->change();
            $table->string('kelompok_umur')->nullable()->change();
            $table->string('jenis_kelamin')->nullable()->change();
            $table->string('penanggung_biaya')->nullable()->change();
            $table->string('insiden_terjadi_pada')->change();
            $table->string('dampak_insiden')->default('Tidak ada cedera')->change();
            $table->string('grading_risiko')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Intentionally left empty — reverting to ENUM is not necessary
    }
};
