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
        Schema::create('problem_contributor_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_component_id')
                ->constrained('problem_contributor_sub_components', 'id', 'problem_contributor_descriptions_sub_component_id_foreign')
                ->onDelete('cascade')
                ->index();
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_contributor_descriptions');
    }
};
