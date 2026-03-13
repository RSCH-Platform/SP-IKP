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
            $table->foreignId('problem_id')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problem_contributors', function (Blueprint $table) {
            $table->foreignId('problem_id')
                ->nullable(false)
                ->change();
        });
    }
};
