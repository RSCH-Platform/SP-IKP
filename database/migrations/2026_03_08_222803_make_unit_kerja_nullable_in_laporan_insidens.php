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
            $table->string('unit_kerja')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->string('unit_kerja')->nullable(false)->change();
        });
    }
};
