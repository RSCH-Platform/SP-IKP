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
        Schema::create('problem_whys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')
                ->constrained('incident_problems')
                ->onDelete('cascade');
            $table->integer('why_level')->comment('level 1 sampai 5');
            $table->text('problem_statement');
            $table->text('immediate_cause')->nullable();
            $table->text('root_cause')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_whys');
    }
};
