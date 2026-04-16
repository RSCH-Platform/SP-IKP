<?php

require './vendor/autoload.php';
$app = require_once './bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check timeline categories
$categories = \App\Models\TimelineCategory::get(['id', 'code', 'name']);
echo "Timeline Categories:\n";
$categories->each(fn($c) => echo "  ID: {$c->id}, Code: {$c->code}, Name: {$c->name}\n");

// Check timeline entries for record 2
echo "\n\nTimeline Entries for Laporan ID 2:\n";
$entries = \App\Models\TimelineEntry::where('laporan_insiden_id', 2)->with('category')->get();
$entries->each(fn($e) => echo "  ID: {$e->id}, Category Code: {$e->category?->code}, Desc: " . substr($e->description, 0, 50) . "\n");

// Check existing problems for record 2
echo "\n\nExisting Problems for Laporan ID 2:\n";
$problems = \App\Models\IncidentProblem::where('laporan_insiden_id', 2)->get();
if ($problems->isEmpty()) {
    echo "  (No problems exist yet)\n";
} else {
    $problems->each(fn($p) => echo "  ID: {$p->id}, Type: {$p->problem_type}, Desc: " . substr($p->problem_description, 0, 50) . "\n");
}
