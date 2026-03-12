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
        Schema::create('timeline_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('kode kategori');
            $table->string('name', 100)->comment('nama kategori');
            $table->integer('sort_order')->default(0)->comment('urutan tampil kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_categories');
    }
};
