<div x-data="{ openRecommendationModal: false }" class="space-y-3 border-b border-gray-200 dark:border-slate-700 pb-6 bg-white dark:bg-slate-800">
    <div class="flex items-center justify-between">
        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <span class="text-xl">💡</span> Rekomendasi Perbaikan
        </h4>
        <button @click="openRecommendationModal = true; $wire.addRecommendation({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition font-medium">
            ➕ Tambah Rekomendasi
        </button>
    </div>

    @if(count($problem['recommendations'] ?? []) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($problem['recommendations'] as $rec)
        <div @class([ 'p-3 border rounded-lg transition' , 'border-red-300 bg-red-50 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 hover:dark:bg-red-900/30'=> ($rec['priority'] ?? '') === 'high',
            'border-yellow-300 bg-yellow-50 hover:bg-yellow-100 dark:border-yellow-700 dark:bg-yellow-900/20 hover:dark:bg-yellow-900/30' => ($rec['priority'] ?? '') === 'medium',
            'border-green-300 bg-green-50 hover:bg-green-100 dark:border-green-700 dark:bg-green-900/20 hover:dark:bg-green-900/30' => ($rec['priority'] ?? '') === 'low',
            'border-gray-300 bg-gray-50 hover:bg-gray-100 dark:border-slate-700 dark:bg-slate-800 hover:dark:bg-slate-700' => !in_array($rec['priority'] ?? '', ['high', 'medium', 'low']),
            ])>
            <div class="flex items-start justify-between gap-3 mb-2">
                <span @class([ 'text-xs font-bold px-2 py-1 rounded text-white' , 'bg-red-500'=> ($rec['priority'] ?? '') === 'high',
                    'bg-yellow-500' => ($rec['priority'] ?? '') === 'medium',
                    'bg-green-500' => ($rec['priority'] ?? '') === 'low',
                    'bg-gray-500' => !in_array($rec['priority'] ?? '', ['high', 'medium', 'low']),
                    ])>
                    {{ strtoupper($rec['priority'] ?? 'normal') }}
                </span>
                <div class="flex gap-1 flex-shrink-0">
                    <button @click="openRecommendationModal = true; $wire.editRecommendation({{ $rec['id'] }})" class="px-2 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700 transition font-bold">
                        ✎ EDIT
                    </button>
                    <button wire:click="deleteRecommendation({{ $rec['id'] }})" wire:confirm="Hapus rekomendasi ini?" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                        🗑 DEL
                    </button>
                </div>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-200">{{ $rec['recommendation_text'] }}</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="p-4 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-800 rounded-lg text-sm">
        Belum ada rekomendasi.
    </div>
    @endif

    @component('livewire.problem-analysis-manager.modal', ['openState' => 'openRecommendationModal'])
    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
        {{ isset($recommendationFormData['id']) ? '✎ Edit Rekomendasi' : '➕ Tambah Rekomendasi Baru' }}
    </h5>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rekomendasi <span class="text-red-500">*</span></label>
            <textarea wire:model="recommendationFormData.recommendation_text" placeholder="Masukkan rekomendasi perbaikan..." rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioritas</label>
            <select wire:model="recommendationFormData.priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                <option value="low">🟢 Low (Rendah)</option>
                <option value="medium">🟡 Medium (Sedang)</option>
                <option value="high">🔴 High (Tinggi)</option>
            </select>
        </div>
    </div>
    <div class="flex gap-2 mt-4 justify-end">
        <button wire:click="saveRecommendation()" class="px-4 py-2 text-sm bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-medium">💾 Simpan Rekomendasi</button>
        <button @click="openRecommendationModal = false; $wire.resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 dark:text-gray-800 rounded-lg hover:bg-gray-400 transition">Batal</button>
    </div>
    @endcomponent
</div>