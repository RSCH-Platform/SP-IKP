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
        Schema::create('incident_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')
                ->constrained('laporan_insidens')
                ->onDelete('cascade')
                ->comment('relasi ke laporan insiden');
            $table->string('problem_type')->comment('CMP atau SDP');
            $table->text('problem_description');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_problems');
    }
};
