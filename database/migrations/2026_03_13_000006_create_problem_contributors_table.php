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
        Schema::create('problem_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')
                ->constrained('incident_problems')
                ->onDelete('cascade');
            $table->string('category')->comment('Manusia, Metode, Alat, Lingkungan, Manajemen, Fasyankes');
            $table->string('component')->nullable();
            $table->string('sub_component')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_contributors');
    }
};
