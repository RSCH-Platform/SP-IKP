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
        Schema::table('timeline_entries', function (Blueprint $table) {
            // Allow entries without a category (e.g. partial drafts)
            $table->foreignId('category_id')
                ->nullable()
                ->change();

            // Allow entries without a description (draft state)
            $table->longText('description')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timeline_entries', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable(false)
                ->change();

            $table->longText('description')
                ->nullable(false)
                ->change();
        });
    }
};
