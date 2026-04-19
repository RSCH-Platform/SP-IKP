@php
$events = $record->relationLoaded('timelineEvents')
? $record->timelineEvents
: $record->timelineEvents()->with('entries.category')->orderBy('event_datetime', 'asc')->get();

$eventsByDate = $events->groupBy(function ($event) {
return $event->event_datetime?->format('Y-m-d');
})->sortKeys();

$allCategories = \App\Models\TimelineCategory::orderBy('sort_order')->get();

$dateCategories = [];
foreach ($eventsByDate as $date => $dateEvents) {
$dateCategories[$date] = $allCategories;
}
@endphp

<div class="space-y-6">
    @if($eventsByDate->isNotEmpty())
    <x-timeline-events :eventsByDate="$eventsByDate" :dateCategories="$dateCategories" :allCategories="$allCategories" />
    @else
    <div class="rounded-lg border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-500">
        Tidak ada kronologi timeline yang tersedia.
    </div>
    @endif
</div>