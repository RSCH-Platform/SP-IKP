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

<div class="space-y-4">
    @if($eventsByDate->isNotEmpty())
    <div class="flex justify-end">
        <form action="{{ route('export.timeline') }}" method="POST" style="display: inline;">
            @csrf
            <input type="hidden" name="record_id" value="{{ $record->id }}">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-blue-600 px-3 py-2 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export ke Excel
            </button>
        </form>
    </div>
    @endif

    <div class="space-y-6">
        @if($eventsByDate->isNotEmpty())
        <x-timeline-events :eventsByDate="$eventsByDate" :dateCategories="$dateCategories" :allCategories="$allCategories" />
        @else
        <div class="rounded-lg border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-500">
            Tidak ada kronologi timeline yang tersedia.
        </div>
        @endif
    </div>
</div>