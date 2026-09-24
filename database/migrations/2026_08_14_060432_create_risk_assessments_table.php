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
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_insiden_id')->constrained('laporan_insidens')->onDelete('cascade');
            $table->integer('severity_score');
            $table->string('severity_level');
            $table->integer('probability_score');
            $table->string('probability_level');
            $table->integer('risk_score');
            $table->string('risk_level');
            $table->string('risk_band');
            $table->text('required_action');
            $table->foreignId('assessed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
