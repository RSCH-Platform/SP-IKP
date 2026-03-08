<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')
                ->nullable()
                ->after('user_id')
                ->constrained('unit_kerja')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('laporan_insidens_unit_kerja_id_foreign');
            $table->dropColumn('unit_kerja_id');
        });
    }
};
