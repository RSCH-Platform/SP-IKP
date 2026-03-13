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
            $table->foreignId('sub_component_id')
                ->nullable()
                ->after('problem_id')
                ->constrained('problem_contributor_sub_components')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problem_contributors', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['sub_component_id']);
            $table->dropColumn('sub_component_id');
        });
    }
};
