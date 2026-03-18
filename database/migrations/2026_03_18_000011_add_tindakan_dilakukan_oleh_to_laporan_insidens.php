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
            $table->string('tindakan_dilakukan_oleh')->nullable()->after('tindakan_dilakukan');
            $table->string('tindakan_dilakukan_oleh_lainnya')->nullable()->after('tindakan_dilakukan_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropColumn(['tindakan_dilakukan_oleh', 'tindakan_dilakukan_oleh_lainnya']);
        });
    }
};
