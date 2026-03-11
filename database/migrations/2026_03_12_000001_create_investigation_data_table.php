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
        Schema::create('investigation_data', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Foreign key to laporan_insidens
            $table->foreignId('laporan_insiden_id')
                ->constrained('laporan_insidens')
                ->cascadeOnDelete();

            // Investigation category: interview, review_dokumen, or observasi
            $table->enum('kategori', ['interview', 'review_dokumen', 'observasi']);

            // Source of data (person interviewed or document source)
            $table->string('sumber')->nullable();

            // Investigation results
            $table->text('hasil');

            // Observation location
            $table->string('lokasi')->nullable();

            // File path for uploaded documents or evidence photos
            $table->string('file_path')->nullable();

            // User who created this record
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Indexes for better query performance
            $table->index('laporan_insiden_id');
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigation_data');
    }
};
