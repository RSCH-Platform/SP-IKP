@php
use Illuminate\Support\Carbon;

// Debug: Log what we received
\Log::debug('TimelineGridView data:', [
'timelineEventsCount' => count($timelineEvents ?? []),
'categoriesCount' => count($timelineCategories ?? []),
'events' => $timelineEvents ?? [],
]);

// Group events by date
$eventsByDate = collect($timelineEvents ?? [])->groupBy(function ($event) {
return Carbon::parse($event['event_datetime'])->format('Y-m-d');
})->sortKeys();

$allCategories = $timelineCategories ?? [];
@endphp

<!-- DEBUG: Show event count -->
@if(config('app.debug'))
<div class="mb-4 p-2 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded text-xs text-yellow-800 dark:text-yellow-200">
    <strong>DEBUG:</strong>
    Events: {{ count($timelineEvents ?? []) }} |
    Categories: {{ count($allCategories ?? []) }} |
    Grouped Dates: {{ count($eventsByDate) }}
</div>
@endif

<div class="space-y-6 dark:text-gray-100 dark:bg-slate-900 bg-white rounded-lg dark:rounded-lg" x-data="timelineGrid()">
    @forelse($eventsByDate as $date => $dateEvents)
    @php
    $dateObj = Carbon::createFromFormat('Y-m-d', $date);
    $formattedDate = $dateObj->translatedFormat('d F Y');
    // Sort events by time within the date
    $sortedEvents = collect($dateEvents)->sortBy('event_datetime');
    @endphp

    <div class="border rounded-lg overflow-hidden dark:border-gray-600 bg-white dark:bg-slate-800 shadow-sm dark:shadow-lg" x-data="{ expanded: true }">
        <!-- Date Header -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-25 dark:from-blue-950 dark:to-blue-900 px-4 py-3 border-b dark:border-blue-800 flex items-center justify-between cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900 transition-colors duration-200" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <span class="text-lg">📅</span>
                <h3 class="font-semibold text-gray-900 dark:text-blue-100">{{ $formattedDate }}</h3>
                <span class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-900 dark:text-blue-100 px-2 py-1 rounded-full font-medium">{{ $sortedEvents->count() }} event</span>
            </div>
            <div class="flex items-center gap-2">
                @if($addEventModal)
                <button
                    type="button"
                    @click.stop="$dispatch('add-timeline-event', { dateString: '{{ $date }}' })"
                    class="text-xs px-3 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium shadow-sm dark:shadow-md">
                    + Tambah Event
                </button>
                @endif
                <svg :class="expanded ? 'rotate-180' : ''" class="w-5 h-5 transform transition-transform text-gray-700 dark:text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </div>

        <!-- Data Table -->
        <div x-show="expanded" class="overflow-x-auto bg-white dark:bg-slate-900" x-collapse>
            <table class="w-full text-sm">
                <!-- Header Row -->
                <thead>
                    <tr class="border-b dark:border-gray-700 bg-gray-50 dark:bg-slate-800 sticky top-0">
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-20 bg-gray-50 dark:bg-slate-800">Waktu</th>
                        @foreach($allCategories as $category)
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 min-w-[200px] bg-gray-50 dark:bg-slate-800">
                            <div class="flex flex-col">
                                <span class="text-gray-900 dark:text-white">{{ $category['name'] }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">({{ $category['code'] }})</span>
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Data Rows -->
                <tbody>
                    @foreach($sortedEvents as $eventIndex => $event)
                    @php
                    $eventTime = Carbon::parse($event['event_datetime']);
                    $timeFormatted = $eventTime->format('H:i');
                    $isEven = $eventIndex % 2 === 0;
                    @endphp

                    <tr class="border-b dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-150 {{ $isEven ? 'bg-white dark:bg-slate-900' : 'bg-gray-50 dark:bg-slate-800' }}">
                        <!-- Time Cell -->
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 sticky left-0 z-10 {{ $isEven ? 'bg-white dark:bg-slate-900' : 'bg-gray-50 dark:bg-slate-800' }} hover:bg-blue-50 dark:hover:bg-slate-700">
                            {{ $timeFormatted }}
                        </td>

                        <!-- Category Cells -->
                        @foreach($allCategories as $category)
                        @php
                        $entry = collect($event['entries'] ?? [])->firstWhere('category_id', $category['id']);
                        $description = $entry['description'] ?? null;
                        $hasContent = !empty($description);
                        @endphp

                        <td class="px-4 py-3">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    @if($hasContent)
                                    <p class="text-gray-800 dark:text-gray-200 text-sm line-clamp-2 leading-relaxed">{{ $description }}</p>
                                    @else
                                    <p class="text-gray-400 dark:text-gray-500 text-sm italic">[Kosong]</p>
                                    @endif
                                </div>
                                <button
                                    type="button"
                                    class="flex-shrink-0 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold text-xs whitespace-nowrap edit-timeline-btn transition-colors duration-150 hover:underline"
                                    data-event-id="{{ $event['id'] ?? 'new' }}"
                                    data-category-id="{{ $category['id'] }}"
                                    data-category-name="{{ addslashes($category['name']) }}"
                                    data-event-time="{{ $timeFormatted }}"
                                    data-event-date="{{ $formattedDate }}"
                                    data-description="{{ addslashes(str_replace(["\n", "\r"], ' ', $description ?? '')) }}"
                                    data-date-string="{{ $date }}"
                                    @click.stop="editTimelineEntry($event)">
                                    ✎ Edit
                                </button>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @empty
    <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
        <p class="text-gray-500 dark:text-gray-400 text-base">📭 Belum ada Timeline Insiden</p>
    </div>
    @endforelse
</div>

<script>
    function timelineGrid() {
        return {
            editTimelineEntry(event) {
                const btn = event.currentTarget;
                const data = {
                    eventId: btn.dataset.eventId,
                    categoryId: parseInt(btn.dataset.categoryId),
                    categoryName: btn.dataset.categoryName,
                    eventTime: btn.dataset.eventTime,
                    eventDate: btn.dataset.eventDate,
                    description: btn.dataset.description,
                    dateString: btn.dataset.dateString
                };

                // Dispatch to parent Livewire component
                this.$dispatch('edit-timeline-entry', data);
            }
        };
    }
</script>