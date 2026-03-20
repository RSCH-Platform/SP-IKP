<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('folders')) {
            if (! Schema::hasColumn('folders', 'uuid')) {
                Schema::table('folders', function (Blueprint $table) {
                    $table->uuid('uuid')->nullable()->after('id')->unique();
                });
            }

            // Fill existing rows with generated UUID.
            DB::table('folders')->whereNull('uuid')->orderBy('id')->chunk(100, function ($folders) {
                foreach ($folders as $folder) {
                    DB::table('folders')->where('id', $folder->id)->update(['uuid' => (string) Str::uuid()]);
                }
            });

            // Make uuid non-nullable if database supports.
            Schema::table('folders', function (Blueprint $table) {
                $table->uuid('uuid')->nullable(false)->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('folders') && Schema::hasColumn('folders', 'uuid')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};