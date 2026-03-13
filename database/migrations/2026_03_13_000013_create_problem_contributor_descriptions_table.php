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
            $table->unsignedBigInteger('sub_component_id')->index();
            $table->text('description');
            $table->timestamps();
            $table->foreign('sub_component_id', 'fk_desc_subcomp_id')
                ->references('id')
                ->on('problem_contributor_sub_components')
                ->onDelete('cascade');
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
