@props(['laporan', 'timelineData'])

<section class="mb-4 break-inside-auto print:block">
    <div class="bg-white print:block break-inside-avoid print:break-inside-avoid">
        <x-section-header title="BAGIAN D: Kronologi Timeline" />
        <div class="space-y-6 print:block">
            @if($laporan->timelineEvents && $laporan->timelineEvents->count() > 0)
            <x-timeline-events :eventsByDate="$timelineData['eventsByDate']" :dateCategories="$timelineData['dateCategories']" />
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-xs text-yellow-800">Belum ada timeline untuk laporan ini.</p>
            </div>
            @endif
        </div>
    </div>
</section>