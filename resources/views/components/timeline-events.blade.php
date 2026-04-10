@props(['events'])

<div class="space-y-4">
    @forelse($events as $event)
    <div class="border-l-4 border-slate-400 pl-4 py-2">
        <!-- Timeline Header dengan datetime -->
        <div class="mb-3 pb-2 border-b border-slate-200">
            <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                {{ $event->event_datetime?->translatedFormat('d F Y') ?? 'Tanggal tidak tersedia' }}
            </p>
            <p class="text-xs text-slate-700 font-medium">
                {{ $event->event_datetime?->translatedFormat('H:i') ?? '' }} WIB
            </p>
        </div>

        <!-- Timeline Entries -->
        @if($event->entries && $event->entries->count() > 0)
        <div class="space-y-2">
            @foreach($event->entries as $entry)
            <div class="border-l-2 border-slate-300 pl-3 py-1">
                <!-- Category -->
                <p class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-1">
                    {{ $entry->category?->name ?? 'Kategori' }}
                </p>
                <!-- Description -->
                <p class="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $entry->description ?? '-' }}</p>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs text-slate-500 italic">Tidak ada entri untuk timeline ini</p>
        @endif
    </div>
    @empty
    <div class="text-center py-4">
        <p class="text-xs text-slate-500 italic">Belum ada kronologi timeline yang tersedia</p>
    </div>
    @endforelse
</div>