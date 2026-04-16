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
            $table->string('category')->nullable()->change();
            $table->string('component')->nullable()->change();
            $table->string('sub_component')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problem_contributors', function (Blueprint $table) {
            $table->string('category')->change();
            $table->string('component')->nullable()->change();
            $table->string('sub_component')->nullable()->change();
        });
    }
};
