<div x-data="{ openWhyModal: false }">
    <div class="space-y-4 border-b pb-6">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <span>📊</span> Analisa WHY
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ count($problem['whys'] ?? []) }} / 5 WHY
                </p>
            </div>

            @if(! ($isReadOnly ?? false))
            <button
                @click="openWhyModal = true; $wire.addWhy({{ $problem['id'] }})"
                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                + Tambah WHY
            </button>
            @else
            <button disabled class="px-3 py-1.5 text-sm rounded-md bg-slate-200 text-slate-500 cursor-not-allowed">🔒 Tambah WHY</button>
            @endif
        </div>

        @if(count($problem['whys'] ?? []) > 0)
        <div class="space-y-3">
            @foreach($problem['whys'] as $index => $why)
            @php
            $isRoot = $why['why_level'] == collect($problem['whys'])->max('why_level');
            @endphp

            <div class="flex items-start gap-3">
                <div class="flex flex-col items-center mt-1">
                    <div class="w-6 h-6 flex items-center justify-center text-xs font-bold rounded-full {{ $isRoot ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-300' }}">
                        {{ $why['why_level'] }}
                    </div>

                    @if(!$loop->last)
                    <div class="w-px h-full bg-gray-300 dark:bg-slate-600 mt-1"></div>
                    @endif
                </div>

                <div class="flex-1">
                    <div class="p-3 rounded-lg border {{ $isRoot ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/20 dark:border-blue-700' : 'bg-white border-gray-200 dark:bg-slate-800 dark:border-slate-700' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-xs font-semibold {{ $isRoot ? 'text-blue-700 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400' }}">
                                    {{ $isRoot ? '🎯 Root Cause/Akar Masalah' : 'WHY '.$why['why_level'] }}
                                </span>
                                <p class="text-sm mt-1 text-gray-800 dark:text-gray-100">
                                    {{ $why['problem_statement'] }}
                                </p>
                            </div>

                            <div class="flex gap-1 flex-shrink-0">
                                @if(! ($isReadOnly ?? false))
                                <button
                                    @click="openWhyModal = true; $wire.editWhy({{ $why['id'] }})"
                                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition font-bold">
                                    ✎ EDIT
                                </button>
                                <button
                                    wire:click="deleteWhy({{ $why['id'] }})"
                                    wire:confirm="Hapus WHY ini?"
                                    class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                                    🗑 DEL
                                </button>
                                @else
                                <span class="text-xs text-slate-500">🔒</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-4 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-800 rounded-lg text-center">
            Mulai dari WHY pertama: <span class="italic">Kenapa masalah ini terjadi?</span>
        </div>
        @endif
    </div>

    @component('livewire.problem-analysis-manager.modal', ['openState' => 'openWhyModal'])
    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
        {{ isset($whyFormData['id']) ? '✎ Edit WHY' : '➕ Tambah WHY ke-' . (count($problem['whys'] ?? []) + 1) }}
    </h5>

    <textarea
        wire:model="whyFormData.problem_statement"
        rows="3"
        placeholder="Jelaskan penyebab..."
        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>

        <div class="flex gap-2 mt-3 justify-end">
        @if(! ($isReadOnly ?? false))
        <button
            wire:click="saveWhy()"
            @click="openWhyModal = false"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
            💾 Simpan WHY
        </button>
        @else
        <button disabled class="px-4 py-2 text-sm bg-slate-200 text-slate-500 rounded-lg cursor-not-allowed">🔒 Simpan</button>
        @endif
        <button
            @click="openWhyModal = false; $wire.resetForm()"
            class="px-4 py-2 text-sm bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
            Batal
        </button>
    </div>
    @endcomponent
</div>