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
        Schema::create('problem_contributor_sub_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')
                ->constrained('problem_contributor_components')
                ->onDelete('cascade')
                ->index();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_contributor_sub_components');
    }
};
