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
            $table->enum('kejadian_pernah_terjadi_sebelumnya', ['Ya', 'Tidak'])->nullable()->after('tindakan_dilakukan_oleh_lainnya');
            $table->text('kejadian_pernah_terjadi_sebelumnya_deskripsi')->nullable()->after('kejadian_pernah_terjadi_sebelumnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropColumn([
                'kejadian_pernah_terjadi_sebelumnya',
                'kejadian_pernah_terjadi_sebelumnya_deskripsi',
            ]);
        });
    }
};
