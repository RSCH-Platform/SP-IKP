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
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_insiden_id')
                ->constrained('laporan_insidens')
                ->onDelete('cascade')
                ->comment('relasi ke laporan insiden');
            $table->dateTime('event_datetime')->index()->comment('waktu kejadian timeline');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->comment('user pembuat event');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
