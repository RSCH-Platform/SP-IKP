<div wire:ignore.self class="space-y-6">
    <!-- Debug Info (development only) -->
    @if(config('app.debug'))
    <div class="mb-4 p-2 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded text-xs text-yellow-800 dark:text-yellow-200">
        <strong>DEBUG:</strong>
        Events: {{ count($timelineEvents ?? []) }} |
        Categories: {{ count($categories ?? []) }}
    </div>
    @endif

    <!-- Action Header - Always Visible -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            @if(count($timelineEvents ?? []) > 0)
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">📅 Timeline Insiden</h2>
            @else
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline Insiden (Belum ada data)</h2>
            @endif
        </div>
        <button
            type="button"
            wire:click="openAddEventModal"
            class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium flex items-center gap-2 shadow-md dark:shadow-lg">
            <span>➕</span>
            <span>Tambah Event</span>
        </button>
    </div>

    <!-- Events by Date -->
    @forelse($eventsByDate as $date => $dateEvents)
    @php
    $dateObj = \Illuminate\Support\Carbon::parse($date);
    $formattedDate = $dateObj->translatedFormat('d F Y');
    $sortedEvents = collect($dateEvents)->sortBy('event_datetime');
    @endphp

    <div class="border rounded-lg overflow-hidden bg-white dark:bg-slate-800 shadow-sm dark:shadow-lg dark:border-gray-600 transition-all duration-300" x-data="{ expanded: true, activeRow: null }">
        <!-- Date Header - Accordion -->
        <div @click="expanded = !expanded" class="group cursor-pointer bg-gradient-to-r from-blue-50 to-blue-25 dark:from-blue-950 dark:to-blue-900 px-4 py-3 border-b dark:border-blue-800 flex items-center justify-between hover:from-blue-100 dark:hover:from-blue-900 active:from-blue-100 dark:active:from-blue-800 transition-colors duration-200">
            <div class="flex items-center gap-3">
                <!-- Chevron Icon -->
                <div class="flex-shrink-0">
                    <svg :class="expanded ? 'rotate-180' : ''" class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-transform duration-300 ease-in-out" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>

                <span class="text-lg">📅</span>
                <h3 class="font-semibold text-gray-900 dark:text-blue-100">{{ $formattedDate }}</h3>
                <span class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-900 dark:text-blue-100 px-2 py-1 rounded-full font-medium">{{ $sortedEvents->count() }} event</span>
            </div>
            <button
                type="button"
                @click.stop="$dispatch('add-timeline-event', { dateString: '{{ $date }}' })"
                wire:click="openAddEventModal('{{ $date }}')"
                class="text-xs px-3 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium shadow-sm dark:shadow-md">
                + Tambah Event Jam
            </button>
        </div>

        <!-- Data Table - Accordion Content -->
        <div class="overflow-hidden transition-all duration-500 ease-out" style="max-height: {{ true ? '2000px' : '0px' }}; opacity: {{ true ? '1' : '0' }};" x-show="expanded" x-collapse>
            <div class="overflow-x-auto bg-white dark:bg-slate-900">
                <table class="w-full text-sm">
                    <!-- Header Row -->
                    <thead>
                        <tr class="border-b dark:border-gray-700 bg-gray-50 dark:bg-slate-800 sticky top-0">
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-20 bg-gray-50 dark:bg-slate-800">Waktu</th>
                            @foreach($categories as $category)
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 min-w-[250px] bg-gray-50 dark:bg-slate-800">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 dark:text-white">{{ $category['name'] }}</span>
                                    <!-- <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">({{ $category['code'] }})</span> -->
                                </div>
                            </th>
                            @endforeach
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200 w-16 bg-gray-50 dark:bg-slate-800">Aksi</th>
                        </tr>
                    </thead>

                    <!-- Data Rows -->
                    <tbody>
                        @foreach($sortedEvents as $eventIndex => $event)
                        @php
                        $eventTime = \Illuminate\Support\Carbon::parse($event['event_datetime']);
                        $timeFormatted = $eventTime->format('H:i');
                        $isEven = $eventIndex % 2 === 0;
                        @endphp

                        <tr class="border-b dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-150 {{ $isEven ? 'bg-white dark:bg-slate-900' : 'bg-gray-50 dark:bg-slate-800' }}" x-data="{ rowId: {{ $event['id'] }} }" @click="activeRow === rowId ? activeRow = null : activeRow = rowId">
                            <!-- Time Cell - Clickable to Edit -->
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 sticky left-0 z-10 {{ $isEven ? 'bg-white dark:bg-slate-900' : 'bg-gray-50 dark:bg-slate-800' }} hover:bg-blue-50 dark:hover:bg-slate-700 active:bg-blue-100 dark:active:bg-slate-600 cursor-pointer group relative transition-colors duration-150"
                                wire:click="openEditTimeModal({{ $event['id'] }})"
                                title="Klik untuk edit waktu">
                                <div class="flex items-center justify-between">
                                    <span>{{ $timeFormatted }}</span>
                                    <span class="opacity-0 group-hover:opacity-100 group-active:opacity-100 transition-opacity ml-2 text-xs text-blue-500">✎</span>
                                </div>
                            </td>

                            <!-- Category Cells -->
                            @foreach($categories as $category)
                            @php
                            $entry = collect($event['entries'] ?? [])->firstWhere('category_id', $category['id']);
                            $description = $entry['description'] ?? null;
                            $hasContent = !empty($description);
                            @endphp

                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    @if($hasContent)
                                    <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed">{{ Str::limit($description, 120) }}</p>
                                    @else
                                    <p class="text-gray-400 dark:text-gray-500 text-sm italic">[Kosong]</p>
                                    @endif

                                    <!-- Action Buttons - Show Only When Row is Active -->
                                    <div x-show="activeRow === rowId" x-transition class="flex gap-1 flex-wrap">
                                        <button
                                            type="button"
                                            wire:click="openMoveModal({{ $event['id'] }}, {{ $category['id'] }})"
                                            @click.stop
                                            class="px-2 py-1 text-xs bg-yellow-500 dark:bg-yellow-600 text-white rounded hover:bg-yellow-600 dark:hover:bg-yellow-500 active:bg-yellow-700 dark:active:bg-yellow-700 transition-colors duration-150 font-medium shadow-sm whitespace-nowrap">
                                            ➡️ Pindah
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="openEditModal({{ $event['id'] }}, {{ $category['id'] }})"
                                            @click.stop
                                            class="px-2 py-1 text-xs bg-blue-500 dark:bg-blue-600 text-white rounded hover:bg-blue-600 dark:hover:bg-blue-500 active:bg-blue-700 dark:active:bg-blue-700 transition-colors duration-150 font-medium shadow-sm whitespace-nowrap">
                                            ✎ Edit
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteEntry({{ $event['id'] }}, {{ $category['id'] }})"
                                            wire:confirm="Yakin hapus entry ini?"
                                            @click.stop
                                            class="px-2 py-1 text-xs bg-red-500 dark:bg-red-600 text-white rounded hover:bg-red-600 dark:hover:bg-red-500 active:bg-red-700 dark:active:bg-red-700 transition-colors duration-150 font-medium shadow-sm whitespace-nowrap">
                                            🗑 Hapus
                                        </button>
                                    </div>
                                </div>
                            </td>
                            @endforeach

                            <!-- Event Actions -->
                            <td class="px-4 py-3 text-center">
                                <button
                                    type="button"
                                    wire:click="deleteEvent({{ $event['id'] }})"
                                    wire:confirm="Hapus event ini dan semua entrinya?"
                                    class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 active:text-red-900 dark:active:text-red-200 font-medium text-xs transition-colors duration-150">
                                    🗑
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @empty
    <div class="text-center py-12 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 rounded-lg border-2 border-dashed border-blue-200 dark:border-blue-800 shadow-sm dark:shadow-lg">
        <div class="text-4xl mb-3">📭</div>
        <p class="text-gray-600 dark:text-gray-300 mb-2 font-semibold">Belum ada Timeline Insiden</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Klik tombol <strong>"Tambah Event"</strong> di atas untuk membuat event pertama Anda</p>
        <button
            type="button"
            wire:click="openAddEventModal"
            class="px-6 py-3 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium inline-flex items-center gap-2 shadow-md dark:shadow-lg">
            <span>➕</span>
            <span>Buat Event Pertama</span>
        </button>
    </div>
    @endforelse

    <!-- Edit Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70" wire:key="modal">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl mx-4 dark:shadow-2xl">
            <!-- Modal Header -->
            <div class="border-b dark:border-gray-700 px-6 py-4 flex items-center justify-between bg-gray-50 dark:bg-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if($modalMode === 'edit')
                    Edit Entry
                    @elseif($modalMode === 'move')
                    Pindah Kategori Entry
                    @else
                    Tambah Event Timeline
                    @endif
                </h2>
                <button
                    type="button"
                    wire:click="closeModal"
                    class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 text-2xl transition-colors duration-150">
                    ×
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 space-y-4 dark:bg-slate-800">
                @if($modalMode === 'edit')
                @php
                $category = collect($categories)->firstWhere('id', $editingCategoryId);
                $eventDate = \Illuminate\Support\Carbon::parse($editingEventDateTime)->translatedFormat('d F Y, H:i');
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Event & Kategori
                    </label>
                    <div class="p-3 bg-gray-50 dark:bg-slate-900 rounded text-sm border dark:border-gray-600 text-gray-900 dark:text-gray-100">
                        <p><strong>Tanggal:</strong> {{ $eventDate }}</p>
                        <p><strong>Kategori:</strong> {{ $category['name'] ?? 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Deskripsi
                    </label>
                    <textarea
                        wire:model="editingDescription"
                        rows="6"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"
                        placeholder="Tuliskan deskripsi di sini..."></textarea>
                </div>

                @elseif($modalMode === 'move')
                @php
                $sourceCategory = collect($categories)->firstWhere('id', $moveSourceCategoryId);
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Pindahkan entry dari kategori
                    </label>
                    <div class="p-3 bg-gray-50 dark:bg-slate-900 rounded text-sm border dark:border-gray-600 text-gray-900 dark:text-gray-100">
                        <p><strong>Sumber:</strong> {{ $sourceCategory['name'] ?? 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Pilih kategori tujuan <span class="text-red-500 dark:text-red-400">*</span>
                    </label>
                    <select
                        wire:model="moveTargetCategoryId"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:focus:ring-yellow-600">
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                        @if($category['id'] !== $moveSourceCategoryId)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                @elseif($modalMode === 'edit-time')
                @php
                $event = collect($timelineEvents)->firstWhere('id', $editingTimeEventId);
                $eventDate = $event ? \Illuminate\Support\Carbon::parse($event['event_datetime'])->translatedFormat('d F Y') : 'N/A';
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Tanggal Event
                    </label>
                    <div class="p-3 bg-gray-50 dark:bg-slate-900 rounded text-sm border dark:border-gray-600 text-gray-900 dark:text-gray-100">
                        {{ $eventDate }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Waktu Event <span class="text-red-500 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="time"
                        wire:model="editingTimeValue"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 dark:focus:ring-yellow-600" />
                </div>

                @else
                <!-- Add Event Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Tanggal & Waktu <span class="text-red-500 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="editingEventDateTime"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600" />
                    @error('editingEventDateTime')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded text-sm text-blue-800 dark:text-blue-200">
                    <p>Event baru akan dibuat dengan 5 kategori kosong. Anda bisa mengisinya setelah event dibuat.</p>
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="border-t dark:border-gray-700 px-6 py-4 flex justify-end gap-2 bg-gray-50 dark:bg-slate-700">
                <button
                    type="button"
                    wire:click="closeModal"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors duration-150 font-medium">
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="@if($modalMode === 'edit') saveEntry @elseif($modalMode === 'move') moveEntry @elseif($modalMode === 'edit-time') saveEventTime @else addTimelineEvent @endif"
                    class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium shadow-sm dark:shadow-md">
                    @if($modalMode === 'edit')
                    💾 Simpan
                    @elseif($modalMode === 'move')
                    ➡️ Pindahkan
                    @elseif($modalMode === 'edit-time')
                    💾 Update Waktu
                    @else
                    ➕ Tambah Event
                    @endif
                </button>
            </div>
        </div>
    </div>
    @endif
</div>