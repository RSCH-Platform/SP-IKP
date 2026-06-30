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
        Schema::create('investigations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_insiden_id')->constrained('laporan_insidens')->cascadeOnDelete();
            $table->string('grading_risiko')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->foreignId('investigation_started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('investigation_started_at')->nullable();
            $table->foreignId('investigation_completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('investigation_completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigations');
    }
};
