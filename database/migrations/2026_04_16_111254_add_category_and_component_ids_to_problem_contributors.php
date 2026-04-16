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
        Schema::table('problem_contributors', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('problem_id')->index();
            $table->unsignedBigInteger('component_id')->nullable()->after('category_id')->index();
            
            $table->foreign('category_id', 'fk_contrib_category_id')
                ->references('id')
                ->on('problem_contributor_categories')
                ->onDelete('set null');
                
            $table->foreign('component_id', 'fk_contrib_component_id')
                ->references('id')
                ->on('problem_contributor_components')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problem_contributors', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('fk_contrib_category_id');
            $table->dropForeignKeyIfExists('fk_contrib_component_id');
            $table->dropColumn('category_id');
            $table->dropColumn('component_id');
        });
    }
};
