@props(['laporan', 'timelineData'])

<div class="break-inside-avoid mb-6">
    <x-section-header title="BAGIAN B: Kronologi Timeline" />
    <div class="bg-white border border-slate-300 p-2">
        @if($laporan->timelineEvents && $laporan->timelineEvents->count() > 0)
        <x-timeline-events :eventsByDate="$timelineData['eventsByDate']" :dateCategories="$timelineData['dateCategories']" />
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
            <p class="text-xs text-yellow-800">Belum ada timeline untuk laporan ini.</p>
        </div>
        @endif
    </div>
</div>