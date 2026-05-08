<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_problems', function (Blueprint $table) {
            $table->foreignId('timeline_entry_id')
                ->nullable()
                ->after('incident_id')
                ->constrained('timeline_entries')
                ->cascadeOnDelete();

            $table->unique('timeline_entry_id');
        });

        $matches = DB::table('incident_problems as problems')
            ->join('timeline_events as events', 'events.laporan_insiden_id', '=', 'problems.incident_id')
            ->join('timeline_entries as entries', 'entries.timeline_event_id', '=', 'events.id')
            ->join('timeline_categories as categories', 'categories.id', '=', 'entries.category_id')
            ->whereNull('problems.timeline_entry_id')
            ->whereIn(DB::raw('LOWER(problems.problem_type)'), ['cmp', 'sdp'])
            ->whereRaw('LOWER(categories.code) = LOWER(problems.problem_type)')
            ->whereRaw('TRIM(COALESCE(entries.description, "")) = TRIM(COALESCE(problems.problem_description, ""))')
            ->orderBy('problems.id')
            ->orderBy('entries.id')
            ->get([
                'problems.id as problem_id',
                'entries.id as timeline_entry_id',
            ]);

        foreach ($matches as $match) {
            DB::table('incident_problems')
                ->where('id', $match->problem_id)
                ->update(['timeline_entry_id' => $match->timeline_entry_id]);
        }
    }

    public function down(): void
    {
        Schema::table('incident_problems', function (Blueprint $table) {
            $table->dropUnique('incident_problems_timeline_entry_id_unique');
            $table->dropConstrainedForeignId('timeline_entry_id');
        });
    }
};