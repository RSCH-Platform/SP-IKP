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
        Schema::table('problem_whys', function (Blueprint $table) {
            if (Schema::hasColumn('problem_whys', 'immediate_cause')) {
                $table->dropColumn('immediate_cause');
            }

            if (Schema::hasColumn('problem_whys', 'root_cause')) {
                $table->dropColumn('root_cause');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problem_whys', function (Blueprint $table) {
            if (! Schema::hasColumn('problem_whys', 'immediate_cause')) {
                $table->text('immediate_cause')->nullable();
            }

            if (! Schema::hasColumn('problem_whys', 'root_cause')) {
                $table->text('root_cause')->nullable();
            }
        });
    }
};
