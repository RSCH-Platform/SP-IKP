<?php

// Test Timeline Grid Data Loading
// Run: php artisan tinker < storage/test-timeline-grid.php

use App\Models\LaporanInsiden;
use App\Models\TimelineCategory;

$recordId = 2;

echo "=== Testing Timeline Grid Data Loading ===\n";
echo "Record ID: {$recordId}\n\n";

// Test 1: Load record
echo "1. Loading LaporanInsiden record...\n";
$record = LaporanInsiden::with('timelineEvents.entries.category')->find($recordId);

if (!$record) {
    echo "❌ Record not found!\n";
    exit;
}

echo "✅ Record found: {$record->id}\n";

// Test 2: Check timelineEvents relationship
echo "\n2. Checking timelineEvents relationship...\n";
$events = $record->timelineEvents()->with('entries.category')->orderBy('event_datetime')->get();
echo "✅ Found {$events->count()} timeline events\n";

// Test 3: Check structure
echo "\n3. Timeline Events Structure:\n";
foreach ($events as $event) {
    echo "  - Event {$event->id}: {$event->event_datetime}\n";
    echo "    Entries: {$event->entries->count()}\n";
    
    foreach ($event->entries as $entry) {
        echo "      - Category: {$entry->category?->name} ({$entry->category_id})\n";
        echo "        Description: " . substr($entry->description ?? '[empty]', 0, 50) . "\n";
    }
}

// Test 4: Check categories
echo "\n4. Categories:\n";
$categories = TimelineCategory::orderBy('sort_order')->get();
echo "✅ Found {$categories->count()} categories\n";
foreach ($categories as $cat) {
    echo "  - {$cat->id}: {$cat->name} ({$cat->code})\n";
}

echo "\n=== Data Ready for Grid ===\n";
