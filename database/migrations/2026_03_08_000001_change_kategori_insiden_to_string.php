<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->string('kategori_insiden')->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
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
                'Lainnya',
            ])->change();
        });
    }
};
