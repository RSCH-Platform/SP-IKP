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
            // Investigation flow tracking
            $table->foreignId('investigation_started_by')
                ->nullable()
                ->after('rejected_at')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->datetime('investigation_started_at')
                ->nullable()
                ->after('investigation_started_by');

            $table->foreignId('investigation_completed_by')
                ->nullable()
                ->after('investigation_started_at')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->datetime('investigation_completed_at')
                ->nullable()
                ->after('investigation_completed_by');

            // Index for query performance
            $table->index('investigation_started_by');
            $table->index('investigation_completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_insidens', function (Blueprint $table) {
            $table->dropIndex(['investigation_started_by']);
            $table->dropIndex(['investigation_completed_by']);
            $table->dropForeignIdFor('investigation_started_by');
            $table->dropForeignIdFor('investigation_completed_by');
            $table->dropColumn([
                'investigation_started_by',
                'investigation_started_at',
                'investigation_completed_by',
                'investigation_completed_at',
            ]);
        });
    }
};
