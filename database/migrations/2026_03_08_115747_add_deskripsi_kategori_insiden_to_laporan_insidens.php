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
            $table->text('deskripsi_kategori_insiden')->nullable()->after('kategori_insiden');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropColumn('deskripsi_kategori_insiden');
        });
    }
};
