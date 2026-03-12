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
        Schema::create('timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timeline_event_id')
                ->constrained('timeline_events')
                ->onDelete('cascade');
            $table->foreignId('category_id')
                ->constrained('timeline_categories');
            $table->longText('description')->comment('isi kronologi timeline');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users');
            $table->timestamps();

            $table->unique(['timeline_event_id', 'category_id']);
            $table->index('timeline_event_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_entries');
    }
};
