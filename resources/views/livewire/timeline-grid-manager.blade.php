<div wire:ignore.self class="space-y-6">
    <!-- Debug Info (development only) -->
    @if(config('app.debug'))
    <div class="mb-4 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
        <strong>DEBUG:</strong>
        Events: {{ count($timelineEvents ?? []) }} |
        Categories: {{ count($categories ?? []) }}
    </div>
    @endif

    <!-- Action Header - Always Visible -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            @if(count($timelineEvents ?? []) > 0)
            <h2 class="text-lg font-semibold text-gray-900">📅 Timeline Insiden</h2>
            @else
            <h2 class="text-lg font-semibold text-gray-900">Timeline Insiden (Belum ada data)</h2>
            @endif
        </div>
        <button
            type="button"
            wire:click="openAddEventModal"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center gap-2 shadow-md">
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

    <div class="border rounded-lg overflow-hidden bg-white shadow-sm">
        <!-- Date Header -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-25 px-4 py-3 border-b flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-lg">📅</span>
                <h3 class="font-semibold text-gray-900">{{ $formattedDate }}</h3>
                <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded">{{ $sortedEvents->count() }} event</span>
            </div>
            <button
                type="button"
                wire:click="openAddEventModal('{{ $date }}')"
                class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                + Tambah Event Jam
            </button>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <!-- Header Row -->
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 w-20">Waktu</th>
                        @foreach($categories as $category)
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 min-w-[250px]">
                            <div class="flex flex-col">
                                <span>{{ $category['name'] }}</span>
                                <!-- <span class="text-xs text-gray-500 font-normal">({{ $category['code'] }})</span> -->
                            </div>
                        </th>
                        @endforeach
                        <th class="px-4 py-2 text-center font-semibold text-gray-700 w-16">Aksi</th>
                    </tr>
                </thead>

                <!-- Data Rows -->
                <tbody>
                    @foreach($sortedEvents as $eventIndex => $event)
                    @php
                    $eventTime = \Illuminate\Support\Carbon::parse($event['event_datetime']);
                    $timeFormatted = $eventTime->format('H:i');
                    @endphp

                    <tr class="border-b hover:bg-gray-50 {{ $eventIndex % 2 === 0 ? '' : 'bg-gray-25' }}">
                        <!-- Time Cell -->
                        <td class="px-4 py-3 font-medium text-gray-900 sticky left-0 bg-inherit">
                            {{ $timeFormatted }}
                        </td>

                        <!-- Category Cells -->
                        @foreach($categories as $category)
                        @php
                        $entry = collect($event['entries'] ?? [])->firstWhere('category_id', $category['id']);
                        $description = $entry['description'] ?? null;
                        $hasContent = !empty($description);
                        @endphp

                        <td class="px-4 py-3">
                            <div class="group">
                                @if($hasContent)
                                <p class="text-gray-700 text-sm group-hover:hidden">{{ Str::limit($description, 120) }}</p>
                                @else
                                <p class="text-gray-400 text-sm italic group-hover:hidden">[Kosong]</p>
                                @endif

                                <!-- Hover Action Buttons -->
                                <div class="hidden group-hover:flex gap-1">
                                    <button
                                        type="button"
                                        wire:click="openMoveModal({{ $event['id'] }}, {{ $category['id'] }})"
                                        class="px-2 py-1 text-xs bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                        ➡️ Pindah
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $event['id'] }}, {{ $category['id'] }})"
                                        class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">
                                        ✎ Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteEntry({{ $event['id'] }}, {{ $category['id'] }})"
                                        wire:confirm="Yakin hapus entry ini?"
                                        class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600">
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
                                class="text-red-600 hover:text-red-800 font-medium text-xs">
                                🗑
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @empty
    <div class="text-center py-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border-2 border-dashed border-blue-200">
        <div class="text-4xl mb-3">📭</div>
        <p class="text-gray-600 mb-2">Belum ada Timeline Insiden</p>
        <p class="text-sm text-gray-500 mb-6">Klik tombol <strong>"Tambah Event"</strong> di atas untuk membuat event pertama Anda</p>
        <button
            type="button"
            wire:click="openAddEventModal"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium inline-flex items-center gap-2 shadow-md">
            <span>➕</span>
            <span>Buat Event Pertama</span>
        </button>
    </div>
    @endforelse

    <!-- Edit Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:key="modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <!-- Modal Header -->
            <div class="border-b px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">
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
                    class="text-gray-400 hover:text-gray-600 text-2xl">
                    ×
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 space-y-4">
                @if($modalMode === 'edit')
                @php
                $category = collect($categories)->firstWhere('id', $editingCategoryId);
                $eventDate = \Illuminate\Support\Carbon::parse($editingEventDateTime)->translatedFormat('d F Y, H:i');
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Event & Kategori
                    </label>
                    <div class="p-3 bg-gray-50 rounded text-sm">
                        <p><strong>Tanggal:</strong> {{ $eventDate }}</p>
                        <p><strong>Kategori:</strong> {{ $category['name'] ?? 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi
                    </label>
                    <textarea
                        wire:model="editingDescription"
                        rows="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Tuliskan deskripsi di sini..."></textarea>
                </div>

                @elseif($modalMode === 'move')
                @php
                $sourceCategory = collect($categories)->firstWhere('id', $moveSourceCategoryId);
                @endphp

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pindahkan entry dari kategori
                    </label>
                    <div class="p-3 bg-gray-50 rounded text-sm">
                        <p><strong>Sumber:</strong> {{ $sourceCategory['name'] ?? 'N/A' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih kategori tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model="moveTargetCategoryId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                        @if($category['id'] !== $moveSourceCategoryId)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                @else
                <!-- Add Event Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal & Waktu <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="datetime-local"
                        wire:model="editingEventDateTime"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('editingEventDateTime')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
                    <p>Event baru akan dibuat dengan 5 kategori kosong. Anda bisa mengisinya setelah event dibuat.</p>
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="border-t px-6 py-4 flex justify-end gap-2">
                <button
                    type="button"
                    wire:click="closeModal"
                    class="px-4 py-2 text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="@if($modalMode === 'edit') saveEntry @elseif($modalMode === 'move') moveEntry @else addTimelineEvent @endif"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    @if($modalMode === 'edit')
                    💾 Simpan
                    @elseif($modalMode === 'move')
                    ➡️ Pindahkan
                    @else
                    ➕ Tambah Event
                    @endif
                </button>
            </div>
        </div>
    </div>
    @endif
</div>