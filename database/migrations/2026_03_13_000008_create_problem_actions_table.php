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
        Schema::create('problem_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')
                ->constrained('incident_problems')
                ->onDelete('cascade');
            $table->text('action_text');
            $table->string('responsible_person')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_actions');
    }
};
