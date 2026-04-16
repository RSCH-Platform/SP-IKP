<div x-data="{ openContributorModal: false }" class="space-y-3 border-b border-gray-200 dark:border-slate-700 pb-6 bg-white dark:bg-slate-800">
    <div class="flex items-center justify-between">
        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <span class="text-xl">🎯</span> Faktor Kontributor (5M Analysis)
        </h4>
        <button @click="openContributorModal = true; $wire.addContributor({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-purple-500 text-white rounded-md hover:bg-purple-600 transition font-medium">
            ➕ Tambah Faktor
        </button>
    </div>

    @if(count($problem['contributors'] ?? []) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($problem['contributors'] as $contrib)
        <div class="p-4 border border-purple-200 rounded-lg bg-purple-50 hover:bg-purple-100 transition dark:border-purple-700 dark:bg-purple-900/20 hover:dark:bg-purple-900/30">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1 min-w-0">
                    <div class="space-y-2 mb-3">
                        <div class="text-xs">
                            <span class="inline-block bg-purple-700 text-white px-2 py-1 rounded font-bold">
                                📂 Kategori
                            </span>
                            <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 font-semibold">
                                @if($contrib['object'] && $contrib['object']->category)
                                {{ $contrib['object']->category->name }}
                                @else
                                -
                                @endif
                            </p>
                        </div>
                        <div class="text-xs">
                            <span class="inline-block bg-purple-600 text-white px-2 py-1 rounded font-bold">
                                🔧 Komponen
                            </span>
                            <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 font-semibold">
                                @if($contrib['object'] && $contrib['object']->component)
                                {{ $contrib['object']->component->name }}
                                @else
                                -
                                @endif
                            </p>
                        </div>
                        <div class="text-xs">
                            <span class="inline-block bg-purple-500 text-white px-2 py-1 rounded font-bold">
                                ⚙️ Sub Komponen
                            </span>
                            <p class="text-sm text-gray-800 dark:text-gray-200 mt-1 font-semibold">
                                @if($contrib['object'] && $contrib['object']->subComponent)
                                {{ $contrib['object']->subComponent->name }}
                                @else
                                -
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-xs mt-3 pt-3 border-t border-purple-300">
                        <span class="inline-block bg-purple-400 text-white px-2 py-1 rounded font-bold">
                            📝 Deskripsi
                        </span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 dark:text-gray-200 mt-1 line-clamp-4">
                            {{ $contrib['description'] ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-1 flex-shrink-0 mt-1">
                    <button @click="openContributorModal = true; $wire.editContributor({{ $contrib['id'] }})" class="px-2 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 transition font-bold">
                        ✎ EDIT
                    </button>
                    <button wire:click="deleteContributor({{ $contrib['id'] }})" wire:confirm="Hapus faktor ini?" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                        🗑 DEL
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-4 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-800 rounded-lg text-sm">
        Belum ada faktor kontributor.
    </div>
    @endif

    @component('livewire.problem-analysis-manager.modal', ['openState' => 'openContributorModal'])
    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
        {{ isset($contributorFormData['id']) ? '✎ Edit Faktor' : '➕ Tambah Faktor Baru' }}
    </h5>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori (5M) <span class="text-red-500">*</span></label>
            <select wire:model="contributorFormData.category_id" wire:change="onCategoryChange($event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories ?? [] as $cat)
                <option value="{{ $cat['id'] }}">{{ $cat['name'] ?? 'N/A' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Komponen</label>
            <select wire:model="contributorFormData.component_id" wire:change="onComponentChange($event.target.value)" @disabled(empty($contributorFormData['category_id'])) class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                <option value="">-- Pilih Komponen --</option>
                @foreach($components ?? [] as $comp)
                <option value="{{ $comp['id'] }}">{{ $comp['name'] ?? 'N/A' }}</option>
                @endforeach
            </select>
            @if(empty($contributorFormData['category_id']))
            <p class="text-xs text-gray-500 mt-1">💡 Pilih kategori terlebih dahulu</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sub Komponen</label>
            <select wire:model="contributorFormData.sub_component_id" wire:change="onSubComponentChange($event.target.value)" @disabled(empty($contributorFormData['component_id'])) class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                <option value="">-- Pilih Sub Komponen --</option>
                @foreach($subComponents ?? [] as $subComp)
                <option value="{{ $subComp['id'] }}">{{ $subComp['name'] ?? 'N/A' }}</option>
                @endforeach
            </select>
            @if(empty($contributorFormData['component_id']))
            <p class="text-xs text-gray-500 mt-1">💡 Pilih komponen terlebih dahulu</p>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
            <textarea wire:model="contributorFormData.description" placeholder="Masukkan deskripsi faktor kontributor..." rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
        </div>
    </div>
    <div class="flex gap-2 mt-4 justify-end">
        <button wire:click="saveContributor()" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">💾 Simpan Faktor</button>
        <button @click="openContributorModal = false; $wire.resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 dark:text-gray-900 rounded-lg hover:bg-gray-400 transition">Batal</button>
    </div>
    @endcomponent
</div>