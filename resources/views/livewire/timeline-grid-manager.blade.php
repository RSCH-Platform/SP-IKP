<div wire:ignore.self class="space-y-6">
    @php
        $isReadOnly = (bool) ($isReadOnly ?? false);
    @endphp

    @if($isReadOnly)
        <div
            class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200">
            Laporan berstatus selesai. Timeline dalam mode lihat saja.
        </div>
    @endif

    <details
        class="rounded-xl border border-blue-200 bg-blue-50/80 shadow-sm open:shadow-md dark:border-blue-800 dark:bg-blue-950/40">
        <summary
            class="cursor-pointer list-none px-4 py-3 flex items-center justify-between gap-3 text-sm font-semibold text-blue-900 dark:text-blue-100">
            <span class="flex items-center gap-2">
                <span class="text-base">ℹ️</span>
                <span>Panduan CRUD Timeline</span>
            </span>
            <span class="text-xs font-medium text-blue-700 dark:text-blue-200">Klik untuk lihat langkah</span>
        </summary>

        <div class="border-t border-blue-200 px-4 py-4 text-sm text-slate-700 dark:border-blue-800 dark:text-slate-200">
            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-slate-900">
                    <div class="mb-2 flex items-center gap-2 font-semibold text-slate-900 dark:text-white">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">1</span>
                        Tambah event baru
                    </div>
                    <p class="leading-relaxed text-sm text-slate-600 dark:text-slate-300">
                        Klik tombol <span class="font-semibold">Tambah Event</span> di atas untuk membuat event baru
                        dengan tanggal dan jam lengkap.
                    </p>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-slate-900">
                    <div class="mb-2 flex items-center gap-2 font-semibold text-slate-900 dark:text-white">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">2</span>
                        Tambah jam saja
                    </div>
                    <p class="leading-relaxed text-sm text-slate-600 dark:text-slate-300">
                        Jika sudah memilih tanggal, gunakan tombol <span class="font-semibold">Tambah Event Jam</span>
                        pada blok tanggal tersebut untuk mengisi jam tanpa mengubah tanggal.
                    </p>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-slate-900">
                    <div class="mb-2 flex items-center gap-2 font-semibold text-slate-900 dark:text-white">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200">3</span>
                        Edit, pindah, hapus
                    </div>
                    <p class="leading-relaxed text-sm text-slate-600 dark:text-slate-300">
                        Klik <span class="font-semibold">baris event</span> dulu agar tombol aksi muncul. Setelah itu
                        pilih <span class="font-semibold">Pindah</span>, <span class="font-semibold">Edit</span>, atau
                        <span class="font-semibold">Hapus</span> sesuai kebutuhan.
                    </p>
                </div>
            </div>
        </div>
    </details>

    <!-- Action Header - Always Visible -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1">
            @if(count($timelineEvents ?? []) > 0)
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">📅 Timeline Insiden</h2>
            @else
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline Insiden (Belum ada data)</h2>
            @endif
        </div>
        <div class="flex gap-2">
            <form action="{{ route('export.timeline') }}" method="POST">
                @csrf
                <input type="hidden" name="record_id" value="{{ $recordId }}">

                <button type="submit"
                    class="inline-flex items-center gap-2.5 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-200 dark:bg-emerald-600 dark:hover:bg-emerald-500 dark:focus:ring-emerald-900">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v10m0 0l-4-4m4 4l4-4M4 20h16" />
                    </svg>

                    <span>Export ke Excel</span>
                </button>
            </form>
            @if($isReadOnly)
                <button type="button" disabled
                    class="px-4 py-2 rounded-lg bg-slate-300 text-slate-600 dark:bg-slate-700 dark:text-slate-300 font-medium flex items-center gap-2 shadow-sm cursor-not-allowed"
                    title="Laporan selesai: tidak bisa tambah event">
                    <span>🔒</span>
                    <span>Tambah Event</span>
                </button>
            @else
                <button type="button" wire:click="openAddEventModal"
                    class="px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium flex items-center gap-2 shadow-md dark:shadow-lg">
                    <span>➕</span>
                    <span>Tambah Event</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Events by Date -->
    @forelse($eventsByDate as $date => $dateEvents)
        @php
            $dateObj = \Illuminate\Support\Carbon::parse($date);
            $formattedDate = $dateObj->translatedFormat('d F Y');
            $sortedEvents = collect($dateEvents)->sortBy('event_datetime');
        @endphp

        <div class="border rounded-lg overflow-hidden bg-white dark:bg-slate-800 shadow-sm dark:shadow-lg dark:border-gray-600 transition-all duration-300"
            x-data="{ expanded: true, activeRow: null }">
            <!-- Date Header - Accordion -->
            <div @click="expanded = !expanded"
                class="group cursor-pointer bg-gradient-to-r from-blue-50 to-blue-25 dark:from-blue-950 dark:to-blue-900 px-4 py-3 border-b dark:border-blue-800 flex items-center justify-between hover:from-blue-100 dark:hover:from-blue-900 active:from-blue-100 dark:active:from-blue-800 transition-colors duration-200">
                <div class="flex items-center gap-3">
                    <!-- Chevron Icon -->
                    <div class="flex-shrink-0">
                        <svg :class="expanded ? 'rotate-180' : ''"
                            class="w-5 h-5 text-gray-600 dark:text-gray-300 transition-transform duration-300 ease-in-out"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>

                    <span class="text-lg">📅</span>
                    <h3 class="font-semibold text-gray-900 dark:text-blue-100">{{ $formattedDate }}</h3>
                    <span
                        class="text-xs bg-blue-200 dark:bg-blue-800 text-blue-900 dark:text-blue-100 px-2 py-1 rounded-full font-medium">{{ $sortedEvents->count() }}
                        event</span>
                </div>
                @if(!$isReadOnly)
                    <button type="button" @click.stop="$dispatch('add-timeline-event', { dateString: '{{ $date }}' })"
                        wire:click="openAddEventModal('{{ $date }}')"
                        class="text-xs px-3 py-1 bg-blue-600 dark:bg-blue-700 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium shadow-sm dark:shadow-md">
                        + Tambah Event Jam
                    </button>
                @endif
            </div>

            <!-- Data Table - Accordion Content -->
            <div class="overflow-hidden transition-all duration-500 ease-out"
                style="max-height: {{ true ? '2000px' : '0px' }}; opacity: {{ true ? '1' : '0' }};" x-show="expanded"
                x-collapse>
                <div class="overflow-x-auto bg-white dark:bg-slate-900">
                    <table class="w-full text-sm">
                        <!-- Header Row -->
                        <thead>
                            <tr class="border-b dark:border-gray-700 bg-gray-50 dark:bg-slate-800 sticky top-0">
                                <th
                                    class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 w-20 bg-gray-50 dark:bg-slate-800">
                                    Waktu</th>
                                @foreach($categories as $category)
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200 min-w-[250px] bg-gray-50 dark:bg-slate-800">
                                        <div class="flex flex-col">
                                            <span class="text-gray-900 dark:text-white">{{ $category['name'] }}</span>
                                            <!-- <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">({{ $category['code'] }})</span> -->
                                        </div>
                                    </th>
                                @endforeach
                                @if(!$isReadOnly)
                                    <th
                                        class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200 w-16 bg-gray-50 dark:bg-slate-800">
                                        Aksi</th>
                                @endif
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

                                <tr class="border-b dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-150 {{ $isEven ? 'bg-white dark:bg-slate-900' : 'bg-gray-50 dark:bg-slate-800' }}"
                                    x-data="{ rowId: {{ $event['id'] }} }" @if(!$isReadOnly)
                                    @click="activeRow === rowId ? activeRow = null : activeRow = rowId" @endif>
                                    <!-- Time Cell - Clickable to Edit -->
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100 sticky left-0 z-10 {{ $isEven ? 'bg-white dark:bg-slate-900' : 'bg-gray-50 dark:bg-slate-800' }} hover:bg-blue-50 dark:hover:bg-slate-700 active:bg-blue-100 dark:active:bg-slate-600 {{ $isReadOnly ? 'cursor-default' : 'cursor-pointer group relative' }} transition-colors duration-150"
                                        @if(!$isReadOnly) wire:click="openEditTimeModal({{ $event['id'] }})" @endif
                                        title="{{ $isReadOnly ? 'Mode lihat saja' : 'Klik untuk edit waktu' }}">
                                        <div class="flex items-center justify-between">
                                            <span>{{ $timeFormatted }}</span>
                                            @if(!$isReadOnly)
                                                <span
                                                    class="opacity-0 group-hover:opacity-100 group-active:opacity-100 transition-opacity ml-2 text-xs text-blue-500">✎</span>
                                            @endif
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
                                                    <p class="text-gray-800 dark:text-gray-200 text-sm leading-relaxed">
                                                        {{ Str::limit($description, 120) }}</p>
                                                @else
                                                    <p class="text-gray-400 dark:text-gray-500 text-sm italic">[Kosong]</p>
                                                @endif

                                                <!-- Action Buttons - Show Only When Row is Active -->
                                                @if(!$isReadOnly)
                                                    <div x-show="activeRow === rowId" x-transition class="flex gap-1 flex-wrap">
                                                        <button type="button"
                                                            wire:click="openMoveModal({{ $event['id'] }}, {{ $category['id'] }})"
                                                            @click.stop
                                                            class="px-2 py-1 text-xs bg-yellow-500 dark:bg-yellow-600 text-white rounded hover:bg-yellow-600 dark:hover:bg-yellow-500 active:bg-yellow-700 dark:active:bg-yellow-700 transition-colors duration-150 font-medium shadow-sm whitespace-nowrap">
                                                            ➡️ Pindah
                                                        </button>
                                                        <button type="button"
                                                            wire:click="openEditModal({{ $event['id'] }}, {{ $category['id'] }})"
                                                            @click.stop
                                                            class="px-2 py-1 text-xs bg-blue-500 dark:bg-blue-600 text-white rounded hover:bg-blue-600 dark:hover:bg-blue-500 active:bg-blue-700 dark:active:bg-blue-700 transition-colors duration-150 font-medium shadow-sm whitespace-nowrap">
                                                            ✎ Edit
                                                        </button>
                                                        <button type="button"
                                                            wire:click="deleteEntry({{ $event['id'] }}, {{ $category['id'] }})"
                                                            wire:confirm="Yakin hapus entry ini?" @click.stop
                                                            class="px-2 py-1 text-xs bg-red-500 dark:bg-red-600 text-white rounded hover:bg-red-600 dark:hover:bg-red-500 active:bg-red-700 dark:active:bg-red-700 transition-colors duration-150 font-medium shadow-sm whitespace-nowrap">
                                                            🗑 Hapus
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach

                                    @if(!$isReadOnly)
                                        <!-- Event Actions -->
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" wire:click="deleteEvent({{ $event['id'] }})"
                                                wire:confirm="Hapus event ini dan semua entrinya?"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 active:text-red-900 dark:active:text-red-200 font-medium text-xs transition-colors duration-150">
                                                🗑
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @empty
        <div
            class="text-center py-12 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 rounded-lg border-2 border-dashed border-blue-200 dark:border-blue-800 shadow-sm dark:shadow-lg">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-gray-600 dark:text-gray-300 mb-2 font-semibold">Belum ada Timeline Insiden</p>
            @if($isReadOnly)
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Belum ada event timeline. Data hanya bisa dilihat
                    karena laporan sudah selesai.</p>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Klik tombol <strong>"Tambah Event"</strong> di atas
                    untuk membuat event pertama Anda</p>
            @endif
            @if(!$isReadOnly)
                <button type="button" wire:click="openAddEventModal"
                    class="px-6 py-3 bg-blue-600 dark:bg-blue-700 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150 font-medium inline-flex items-center gap-2 shadow-md dark:shadow-lg">
                    <span>➕</span>
                    <span>Buat Event Pertama</span>
                </button>
            @endif
        </div>
    @endforelse

    <!-- Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm dark:bg-black/60"
            wire:key="modal">
            <div
                class="w-full max-w-6xl mx-4 overflow-hidden rounded-[10px] border border-slate-200/80 bg-white shadow-[0_35px_80px_-40px_rgba(15,23,42,0.45)] transition-all duration-300 dark:border-slate-700/80 dark:bg-slate-950 dark:shadow-[0_35px_80px_-40px_rgba(15,23,42,0.7)]">
                <!-- Modal Header -->
                <div
                    class="flex flex-col gap-2 px-6 py-5 bg-gradient-to-r from-sky-50 to-white dark:from-slate-900 dark:to-slate-950 border-b border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 shadow-sm">
                                @if($modalMode === 'edit')✏️@elseif($modalMode === 'move')➡️@elseif($modalMode === 'edit-time')⏰@else➕@endif
                            </span>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                    @if($modalMode === 'edit')
                                        Edit Entry
                                    @elseif($modalMode === 'move')
                                        Pindah Kategori Entry
                                    @elseif($modalMode === 'edit-time')
                                        Ubah Waktu Event
                                    @else
                                        Tambah Event Timeline
                                    @endif
                                </h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    @if($modalMode === 'edit')
                                        Perbarui isi deskripsi untuk entry ini.
                                    @elseif($modalMode === 'move')
                                        Pilih kategori tujuan untuk memindahkan entry.
                                    @elseif($modalMode === 'edit-time')
                                        Atur ulang jam event tanpa mengubah tanggal.
                                    @else
                                        Tambahkan event baru ke timeline Anda.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal"
                            class="rounded-full p-2 text-slate-500 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">
                            <span class="text-xl">×</span>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="space-y-5 px-6 py-6 bg-white dark:bg-slate-950">
                    @if($modalMode === 'edit')
                        @php
                            $category = collect($categories)->firstWhere('id', $editingCategoryId);
                            $eventDate = \Illuminate\Support\Carbon::parse($editingEventDateTime)->translatedFormat('d F Y, H:i');
                        @endphp

                        <div
                            class="space-y-4 rounded-2xl border border-slate-200/80 bg-slate-50 p-5 dark:border-slate-700/80 dark:bg-slate-900">
                            <div>
                                <p class="text-[12px] font-medium text-slate-700 dark:text-slate-200">Event & Kategori</p>
                                <div
                                    class="mt-3 rounded-xl bg-white px-4 py-3 text-[12px] text-slate-900 shadow-sm dark:bg-slate-950 dark:text-slate-100">
                                    <p class="mb-1"><strong>Tanggal:</strong> {{ $eventDate }}</p>
                                    <p><strong>Kategori:</strong> {{ $category['name'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Deskripsi</label>
                                <textarea wire:model="editingDescription" rows="6"
                                    class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-[12px] text-slate-900 shadow-sm outline-none transition duration-150 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-500 dark:focus:ring-blue-900"
                                    placeholder="Tuliskan deskripsi di sini..."></textarea>
                            </div>
                        </div>

                    @elseif($modalMode === 'move')
                        @php
                            $sourceCategory = collect($categories)->firstWhere('id', $moveSourceCategoryId);
                        @endphp

                        <div
                            class="space-y-4 rounded-3xl border border-slate-200/80 bg-slate-50 p-5 dark:border-slate-700/80 dark:bg-slate-900">
                            <div>
                                <p class="text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Pindahkan entry dari
                                    kategori</p>
                                <div
                                    class="rounded-2xl bg-white px-4 py-3 text-[12px] text-slate-900 shadow-sm dark:bg-slate-950 dark:text-slate-100">
                                    <p><strong>Sumber:</strong> {{ $sourceCategory['name'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Pilih
                                    kategori tujuan <span class="text-red-500 dark:text-red-400">*</span></label>
                                <select wire:model="moveTargetCategoryId"
                                    class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-[12px] text-slate-900 outline-none transition duration-150 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-yellow-500 dark:focus:ring-yellow-900">
                                    <option value="">Pilih kategori</option>
                                    @foreach($categories as $category)
                                        @if($category['id'] !== $moveSourceCategoryId)
                                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    @elseif($modalMode === 'edit-time')
                        @php
                            $event = collect($timelineEvents)->firstWhere('id', $editingTimeEventId);
                            $eventDate = $event ? \Illuminate\Support\Carbon::parse($event['event_datetime'])->translatedFormat('d F Y') : 'N/A';
                        @endphp

                        <div
                            class="space-y-4 rounded-3xl border border-slate-200/80 bg-slate-50 p-5 dark:border-slate-700/80 dark:bg-slate-900">
                            <div>
                                <p class="text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Tanggal Event</p>
                                <div
                                    class="rounded-2xl bg-white px-4 py-3 text-[12px] text-slate-900 shadow-sm dark:bg-slate-950 dark:text-slate-100">
                                    {{ $eventDate }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Waktu Event
                                    <span class="text-red-500 dark:text-red-400">*</span></label>
                                <input type="time" wire:model="editingTimeValue"
                                    class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-[12px] text-slate-900 outline-none transition duration-150 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-yellow-500 dark:focus:ring-yellow-900" />
                            </div>
                        </div>

                    @else
                        <!-- Add Event Mode -->
                        <div
                            class="space-y-5 rounded-3xl border border-slate-200/80 bg-slate-50 p-5 dark:border-slate-700/80 dark:bg-slate-900">
                            @if($addEventDate)
                                <div>
                                    <label class="block text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Jam Event
                                        <span class="text-red-500 dark:text-red-400">*</span></label>
                                    <input type="time" wire:model="addEventTime"
                                        class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-[12px] text-slate-900 outline-none transition duration-150 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-500 dark:focus:ring-blue-900" />
                                    @error('addEventTime')
                                        <p class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div>
                                    <label class="block text-[12px] font-medium text-slate-700 dark:text-slate-200 mb-2">Tanggal &
                                        Waktu <span class="text-red-500 dark:text-red-400">*</span></label>
                                    <input type="datetime-local" wire:model="editingEventDateTime"
                                        class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-[12px] text-slate-900 outline-none transition duration-150 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-500 dark:focus:ring-blue-900" />
                                    @error('editingEventDateTime')
                                        <p class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div
                                class="rounded-3xl bg-blue-50 px-4 py-3 text-[12px] text-blue-900 border border-blue-200 dark:bg-blue-950/80 dark:border-blue-700 dark:text-blue-200">
                                <p>Event baru akan dibuat dengan 5 kategori kosong. Anda bisa mengisinya setelah event dibuat.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200/80 bg-slate-50 px-6 py-4 dark:border-slate-700/80 dark:bg-slate-950">
                    <button type="button" wire:click="closeModal"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-2.5 text-[12px] font-medium text-slate-700 transition duration-150 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        Batal
                    </button>
                    @if($modalMode === 'edit')
                        <button type="button" wire:click="saveEntry"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-2.5 text-[12px] font-medium text-white transition duration-150 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-950">
                            💾 Simpan
                        </button>
                    @elseif($modalMode === 'move')
                        <button type="button" wire:click="moveEntry"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-2.5 text-[12px] font-medium text-white transition duration-150 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-950">
                            ➡️ Pindahkan
                        </button>
                    @elseif($modalMode === 'edit-time')
                        <button type="button" wire:click="saveEventTime"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-2.5 text-[12px] font-medium text-white transition duration-150 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-950">
                            💾 Update Waktu
                        </button>
                    @else
                        <button type="button" wire:click="addTimelineEvent"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-5 py-2.5 text-[12px] font-medium text-white transition duration-150 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-slate-950">
                            ➕ Tambah Event
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>