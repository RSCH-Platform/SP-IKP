@props(['timelineData' => null])

@php
$timelineData = $timelineData ?? ['eventsByDate' => collect(), 'allCategories' => collect()];
$eventsByDate = $timelineData['eventsByDate'] ?? collect();
$allCategories = $timelineData['allCategories'] ?? collect();
@endphp

<div class="break-inside-avoid mb-3">
    <x-section-header title="BAGIAN D: Kronologi Timeline" />
    <div class="bg-white">
        <div class="space-y-6">
            @forelse($eventsByDate as $date => $dateEvents)
            <div class="mb-[-5px]">
                <div class="bg-slate-100 px-1 py-1 border-t-2 border-b-2 border-slate-200">
                    <p class="text-xs font-semibold text-slate-800 uppercase tracking-wider">
                        TANGGAL: {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)?->translatedFormat('l, d F Y') ?? 'Tanggal tidak tersedia' }}
                    </p>
                </div>

                @if($dateEvents->flatMap(fn($event) => $event->entries ?? [])->count() > 0)
                <div class="border border-slate-300 w-full">
                    <table class="w-full text-xs table-fixed border-collapse">
                        <thead>
                            <tr class="bg-slate-200 border-b-2 border-slate-400">
                                <th class="px-1 py-1 text-left font-semibold text-slate-700 uppercase tracking-wide border-r border-slate-300 text-xs" style="width: 15%;">WAKTU</th>
                                @foreach($allCategories as $category)
                                <th class="px-1 py-1 text-left font-semibold text-slate-700 uppercase tracking-wide border-r border-slate-300 text-xs" style="width: {{ 85 / max($allCategories->count(), 1) }}%;">
                                    {{ $category->name ?? 'Kategori' }}
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $timeGroups = $dateEvents->groupBy(fn($event) => \Carbon\Carbon::parse($event->event_datetime)->format('H:i'));
                            @endphp

                            @foreach($timeGroups as $time => $eventsAtSameTime)
                            @php
                            $mergedEntries = collect($eventsAtSameTime)
                            ->flatMap(fn($event) => $event->entries ?? [])
                            ->groupBy('category_id');
                            @endphp
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2 text-slate-700 font-medium border-r border-slate-200 whitespace-nowrap text-xs" style="width: 15%;">
                                    {{ $time }}
                                </td>
                                @foreach($allCategories as $category)
                                @php
                                $entries = $mergedEntries[$category->id] ?? collect();
                                $descriptions = collect($entries)->pluck('description')->filter()->all();
                                @endphp
                                <td class="px-1.5 py-2 text-slate-700 border-r border-slate-200 text-xs" style="width: {{ 85 / max($allCategories->count(), 1) }}%;">
                                    @if(count($descriptions) > 0)
                                    <div class="space-y-1">
                                        @foreach($descriptions as $description)
                                        <p class="text-[8px] leading-relaxed text-left">{{ $description }}</p>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-6 bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-xs text-slate-500 italic">Tidak ada entri untuk tanggal ini</p>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-xs text-slate-500 italic">Belum ada kronologi timeline yang tersedia</p>
            </div>
            @endforelse
        </div>
    </div>
</div>