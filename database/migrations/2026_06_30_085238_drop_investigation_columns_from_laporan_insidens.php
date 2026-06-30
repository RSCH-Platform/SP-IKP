<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('laporan_insidens', function (Blueprint $table) {
                $table->dropForeign(['investigation_started_by']);
                $table->dropForeign(['investigation_completed_by']);
                
                $table->dropIndex('laporan_insidens_investigation_started_by_index');
                $table->dropIndex('laporan_insidens_investigation_completed_by_index');
            });

            Schema::table('laporan_insidens', function (Blueprint $table) {
                $table->dropColumn([
                    'investigation_started_by',
                    'investigation_started_at',
                    'investigation_completed_by',
                    'investigation_completed_at',
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->foreignId('investigation_started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('investigation_started_at')->nullable();
            $table->foreignId('investigation_completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('investigation_completed_at')->nullable();
        });
    }
};
