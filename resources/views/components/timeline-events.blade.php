@props(['eventsByDate', 'dateCategories' => null, 'allCategories' => null])

<div class="space-y-6">
    @forelse($eventsByDate as $date => $dateEvents)
    <!-- Date Header Section -->
    <div>
        <div class="bg-slate-100 px-4 py-3 border-t-4 border-b-4 border-slate-400 mb-4">
            <p class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                TANGGAL: {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)?->translatedFormat('l, d F Y') ?? 'Tanggal tidak tersedia' }}
            </p>
        </div>

        <!-- Timeline Table -->
        @php
        $categories = $allCategories ?? $dateCategories[$date] ?? $dateEvents->flatMap(fn($event) => $event->entries ?? [])
        ->pluck('category')
        ->unique('id')
        ->sortBy('sort_order');
        @endphp
        @if(count($categories) > 0)
        <div class="overflow-x-auto border border-slate-300 rounded-lg">
            <table class="min-w-max text-xs">
                <!-- Table Header -->
                <thead>
                    <tr class="bg-slate-200 border-b-2 border-slate-400">
                        <th class="px-4 py-3 text-left font-bold text-slate-800 uppercase tracking-wide border-r border-slate-300 w-20">WAKTU</th>
                        @foreach($categories as $category)
                        <th class="px-4 py-3 text-left font-bold text-slate-800 uppercase tracking-wide border-r border-slate-300 w-40">
                            {{ $category?->name ?? 'Kategori' }}
                        </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Table Body -->
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
                        <!-- Waktu -->
                        <td class="px-4 py-3 text-slate-700 font-medium border-r border-slate-200 whitespace-nowrap">
                            {{ $time }}
                        </td>

                        <!-- Category Data -->
                        @foreach($categories as $category)
                        @php
                        $entries = $mergedEntries[$category->id] ?? collect();
                        $descriptions = collect($entries)->pluck('description')->filter()->all();
                        @endphp
                        <td class="px-4 py-3 text-slate-700 border-r border-slate-200">
                            @if(count($descriptions) > 0)
                            <div class="space-y-2">
                                @foreach($descriptions as $description)
                                <p class="text-xs leading-relaxed">{{ $description }}</p>
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