{{-- 
  Problem Analysis Manager Component - FLAT MODE (NO DROPDOWN)
  Semua problem details langsung terlihat tanpa accordion
--}}

<div wire:key="problem-analysis-{{ rand() }}" class="space-y-4">
    
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-900">🧠 Analisa Masalah (5 WHY)</h3>
        <span class="text-sm text-gray-600">Total: {{ count($problems ?? []) }} masalah</span>
    </div>

    {{-- Problems List - ALL EXPANDED --}}
    <div class="space-y-6">
        @forelse($problems ?? [] as $problem)
            <div class="border-2 border-gray-300 rounded-lg overflow-hidden bg-gradient-to-br from-white to-gray-50">
                
                {{-- Problem Header (NOT CLICKABLE) --}}
                <div class="px-6 py-4 bg-gradient-to-r from-gray-100 to-white border-b border-gray-300">
                    
                    {{-- Left Side: Problem Type & Description --}}
                    <div class="flex items-start gap-4 flex-1 min-w-0 text-left">
                        {{-- Problem Type Badge --}}
                        <div class="flex-shrink-0">
                            <span class="inline-block px-3 py-1 rounded-full font-bold text-white text-sm flex-shrink-0"
                                class="{{ $problem['problem_type'] === 'CMP' ? 'bg-red-500' : 'bg-orange-500' }}">
                                {{ $problem['problem_type'] }}
                            </span>
                        </div>
                        
                        {{-- Problem Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm text-gray-600">
                                    {{ $problem['problem_type'] === 'CMP' ? '🔴 Conformance Issue' : '🟠 Management Issue' }}
                                </span>
                            </div>
                            <p class="text-gray-700 font-semibold text-lg">{{ $problem['problem_description'] }}</p>
                        </div>
                    </div>


                    {{-- Summary Badges --}}
                    <div class="flex gap-2 flex-shrink-0 mt-3">
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded font-semibold whitespace-nowrap">
                            📊 {{ count($problem['whys'] ?? []) }} WHY
                        </span>
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded font-semibold whitespace-nowrap">
                            🎯 {{ count($problem['contributors'] ?? []) }} Faktor
                        </span>
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded font-semibold whitespace-nowrap">
                            💡 {{ count($problem['recommendations'] ?? []) }} Rekom
                        </span>
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded font-semibold whitespace-nowrap">
                            ✅ {{ count($problem['actions'] ?? []) }} Aksi
                        </span>
                    </div>
                </div>

                {{-- Problem Details - ALWAYS VISIBLE --}}
                <div class="px-6 py-4 space-y-8 bg-white">
                    
                    {{-- Section 1: WHY Analysis --}}
                    <div class="space-y-3 border-b pb-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                <span class="text-xl">📊</span> Analisa WHY (5 WHY - Akar Masalah)
                            </h4>
                            <button wire:click="addWhy({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 transition font-medium">
                                ➕ Tambah WHY
                            </button>
                        </div>

                        {{-- WHYs List --}}
                        @if(count($problem['whys'] ?? []) > 0)
                            <div class="space-y-2">
                                @foreach($problem['whys'] as $why)
                                    <div class="p-3 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 transition">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-xs font-bold px-2 py-1 rounded bg-blue-600 text-white">
                                                        @if($why['why_level'] == collect($problem['whys'])->max('why_level'))
                                                            🎯 ROOT
                                                        @else
                                                            WHY {{ $why['why_level'] }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-700">{{ $why['problem_statement'] }}</p>
                                            </div>
                                            <div class="flex gap-1 flex-shrink-0">
                                                <button wire:click="editWhy({{ $why['id'] }})" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition font-bold">
                                                    ✎ EDIT
                                                </button>
                                                <button wire:click="deleteWhy({{ $why['id'] }})" wire:confirm="Hapus WHY ini?" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                                                    🗑 DEL
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg text-sm">
                                Belum ada WHY. Klik "Tambah" untuk memulai analisa.
                            </div>
                        @endif

                        {{-- WHY Form --}}
                        @if($editingItemType === 'why' && $editingProblemId == $problem['id'])
                            <div class="p-4 border-2 border-blue-400 rounded-lg bg-blue-50">
                                <h5 class="font-semibold text-gray-900 mb-3">
                                    {{ isset($whyFormData['id']) ? '✎ Edit WHY' : '➕ Tambah WHY Baru' }}
                                </h5>
                                <textarea wire:model="whyFormData.problem_statement" 
                                    placeholder="Masukkan penjelasan WHY..."
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </textarea>
                                <div class="flex gap-2 mt-3">
                                    <button wire:click="saveWhy()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                        💾 Simpan
                                    </button>
                                    <button wire:click="resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Section 2: Contributing Factors --}}
                    <div class="space-y-3 border-b pb-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                <span class="text-xl">🎯</span> Faktor Kontributor (5M Analysis)
                            </h4>
                            <button wire:click="addContributor({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-purple-500 text-white rounded-md hover:bg-purple-600 transition font-medium">
                                ➕ Tambah Faktor
                            </button>
                        </div>

                        {{-- Contributors Grid --}}
                        @if(count($problem['contributors'] ?? []) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($problem['contributors'] as $contrib)
                                    <div class="p-3 border border-purple-200 rounded-lg bg-purple-50 hover:bg-purple-100 transition">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-mono font-semibold text-purple-700 mb-2 truncate">
                                                    {{ $contrib['category_name'] ?? 'N/A' }} 
                                                    @if($contrib['component_name'])
                                                        <span class="text-purple-600">> {{ $contrib['component_name'] }}</span>
                                                    @endif
                                                    @if($contrib['sub_component_name'])
                                                        <span class="text-purple-600">> {{ $contrib['sub_component_name'] }}</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-700 line-clamp-3">{{ $contrib['description'] ?? '-' }}</p>
                                            </div>
                                            <div class="flex gap-1 flex-shrink-0">
                                                <button wire:click="editContributor({{ $contrib['id'] }})" class="px-2 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 transition font-bold">
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
                            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg text-sm">
                                Belum ada faktor kontributor.
                            </div>
                        @endif

                        {{-- CONTRIBUTOR FORM --}}
                        @if($editingItemType === 'contributor' && $editingProblemId == $problem['id'])
                            <div class="p-4 border-2 border-purple-400 rounded-lg bg-purple-50 mt-3">
                                <h5 class="font-semibold text-gray-900 mb-4">
                                    {{ isset($contributorFormData['id']) ? '✎ Edit Faktor' : '➕ Tambah Faktor Baru' }}
                                </h5>
                                
                                <div class="space-y-4">
                                    {{-- Category Dropdown --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori (5M) <span class="text-red-500">*</span></label>
                                        <select 
                                            wire:model="contributorFormData.category_id"
                                            wire:change="onCategoryChange($event.target.value)"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach($categories ?? [] as $cat)
                                                <option value="{{ $cat['id'] }}">{{ $cat['name'] ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Component Dropdown --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Komponen <span class="text-red-500">*</span></label>
                                        <select 
                                            wire:model="contributorFormData.component_id"
                                            wire:change="onComponentChange($event.target.value)"
                                            @disabled(empty($contributorFormData['category_id']))
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                            <option value="">-- Pilih Komponen --</option>
                                            @foreach($components ?? [] as $comp)
                                                <option value="{{ $comp['id'] }}">{{ $comp['name'] ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                        @if(empty($contributorFormData['category_id']))
                                            <p class="text-xs text-gray-500 mt-1">💡 Pilih kategori terlebih dahulu</p>
                                        @endif
                                    </div>

                                    {{-- Sub Component Dropdown --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sub Komponen</label>
                                        <select 
                                            wire:model="contributorFormData.sub_component_id"
                                            wire:change="onSubComponentChange($event.target.value)"
                                            @disabled(empty($contributorFormData['component_id']))
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                            <option value="">-- Pilih Sub Komponen --</option>
                                            @foreach($subComponents ?? [] as $subComp)
                                                <option value="{{ $subComp['id'] }}">{{ $subComp['name'] ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                        @if(empty($contributorFormData['component_id']))
                                            <p class="text-xs text-gray-500 mt-1">💡 Pilih komponen terlebih dahulu</p>
                                        @endif
                                    </div>

                                    {{-- Description --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                                        <textarea wire:model="contributorFormData.description" 
                                            placeholder="Masukkan deskripsi faktor kontributor..."
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        </textarea>
                                    </div>
                                </div>

                                {{-- Form Actions --}}
                                <div class="flex gap-2 mt-4">
                                    <button wire:click="saveContributor()" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">
                                        💾 Simpan Faktor
                                    </button>
                                    <button wire:click="resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Section 3: Recommendations --}}
                    <div class="space-y-3 border-b pb-6">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                <span class="text-xl">💡</span> Rekomendasi Perbaikan
                            </h4>
                            <button wire:click="addRecommendation({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition font-medium">
                                ➕ Tambah Rekomendasi
                            </button>
                        </div>

                        {{-- Recommendations Grid --}}
                        @if(count($problem['recommendations'] ?? []) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($problem['recommendations'] as $rec)
                                    <div class="p-3 border rounded-lg transition"
                                        class="{{ $rec['priority'] === 'high' ? 'border-red-300 bg-red-50 hover:bg-red-100' : ($rec['priority'] === 'medium' ? 'border-yellow-300 bg-yellow-50 hover:bg-yellow-100' : ($rec['priority'] === 'low' ? 'border-green-300 bg-green-50 hover:bg-green-100' : 'border-gray-300 bg-gray-50 hover:bg-gray-100')) }}">
                                        <div class="flex items-start justify-between gap-3 mb-2">
                                            <span class="text-xs font-bold px-2 py-1 rounded text-white flex-shrink-0"
                                                class="{{ $rec['priority'] === 'high' ? 'bg-red-500' : ($rec['priority'] === 'medium' ? 'bg-yellow-500' : ($rec['priority'] === 'low' ? 'bg-green-500' : 'bg-gray-500')) }}">
                                                {{ strtoupper($rec['priority'] ?? 'normal') }}
                                            </span>
                                            <div class="flex gap-1 flex-shrink-0">
                                                <button wire:click="editRecommendation({{ $rec['id'] }})" class="px-2 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700 transition font-bold">
                                                    ✎ EDIT
                                                </button>
                                                <button wire:click="deleteRecommendation({{ $rec['id'] }})" wire:confirm="Hapus rekomendasi ini?" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                                                    🗑 DEL
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700">{{ $rec['recommendation_text'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg text-sm">
                                Belum ada rekomendasi.
                            </div>
                        @endif

                        {{-- RECOMMENDATION FORM --}}
                        @if($editingItemType === 'recommendation' && $editingProblemId == $problem['id'])
                            <div class="p-4 border-2 border-yellow-400 rounded-lg bg-yellow-50 mt-3">
                                <h5 class="font-semibold text-gray-900 mb-4">
                                    {{ isset($recommendationFormData['id']) ? '✎ Edit Rekomendasi' : '➕ Tambah Rekomendasi Baru' }}
                                </h5>
                                
                                <div class="space-y-4">
                                    {{-- Recommendation Text --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Rekomendasi <span class="text-red-500">*</span></label>
                                        <textarea wire:model="recommendationFormData.recommendation_text" 
                                            placeholder="Masukkan rekomendasi perbaikan..."
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        </textarea>
                                    </div>

                                    {{-- Priority --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                                        <select wire:model="recommendationFormData.priority" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                            <option value="low">🟢 Low (Rendah)</option>
                                            <option value="medium">🟡 Medium (Sedang)</option>
                                            <option value="high">🔴 High (Tinggi)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Form Actions --}}
                                <div class="flex gap-2 mt-4">
                                    <button wire:click="saveRecommendation()" class="px-4 py-2 text-sm bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-medium">
                                        💾 Simpan Rekomendasi
                                    </button>
                                    <button wire:click="resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Section 4: Actions --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                <span class="text-xl">✅</span> Tindakan Korektif & Preventif
                            </h4>
                            <button wire:click="addAction({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-green-500 text-white rounded-md hover:bg-green-600 transition font-medium">
                                ➕ Tambah Tindakan
                            </button>
                        </div>

                        {{-- Actions List --}}
                        @if(count($problem['actions'] ?? []) > 0)
                            <div class="space-y-2">
                                @foreach($problem['actions'] as $action)
                                    <div class="p-3 border border-green-200 rounded-lg bg-green-50 hover:bg-green-100 transition">
                                        <div class="flex items-start justify-between gap-3 mb-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900">{{ Str::limit($action['action_text'] ?? '', 100) }}</p>
                                            </div>
                                            <div class="flex gap-1 flex-shrink-0">
                                                <button wire:click="editAction({{ $action['id'] }})" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition font-bold">
                                                    ✎ EDIT
                                                </button>
                                                <button wire:click="deleteAction({{ $action['id'] }})" wire:confirm="Hapus tindakan ini?" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                                                    🗑 DEL
                                                </button>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                            <div>
                                                <span class="text-gray-600 font-medium">PJ:</span>
                                                <p class="text-gray-700">{{ $action['responsible_person'] ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-gray-600 font-medium">Deadline:</span>
                                                <p class="text-gray-700">{{ $action['deadline'] ?? '-' }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <span class="inline-block px-2 py-1 text-xs font-bold rounded text-white"
                                                    class="{{ ($action['status'] ?? '') === 'pending' ? 'bg-gray-500' : (($action['status'] ?? '') === 'ongoing' ? 'bg-blue-500' : 'bg-green-600') }}">
                                                    {{ ucfirst($action['status'] ?? 'pending') }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Display uploaded files --}}
                                        @if(!empty($action['media']) && count($action['media']) > 0)
                                            <div class="mt-2 pt-2 border-t border-green-300">
                                                <p class="text-xs font-medium text-gray-600 mb-1">📎 Bukti ({{ count($action['media']) }})</p>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($action['media'] as $file)
                                                        <a href="{{ $file['url'] }}" target="_blank" 
                                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-white border border-green-300 rounded hover:bg-green-50 transition">
                                                            @if(in_array($file['mime'], ['image/jpeg', 'image/png', 'image/gif']))
                                                                🖼️
                                                            @elseif($file['mime'] === 'application/pdf')
                                                                📄
                                                            @else
                                                                📎
                                                            @endif
                                                            {{ Str::limit($file['name'], 15) }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg text-sm">
                                Belum ada tindakan.
                            </div>
                        @endif

                        {{-- ACTION FORM --}}
                        @if($editingItemType === 'action' && $editingProblemId == $problem['id'])
                            <div class="p-4 border-2 border-green-400 rounded-lg bg-green-50">
                                <h5 class="font-semibold text-gray-900 mb-4">
                                    {{ isset($actionFormData['id']) ? '✎ Edit Tindakan' : '➕ Tambah Tindakan Baru' }}
                                </h5>
                                
                                <div class="space-y-4">
                                    {{-- Action Text --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Tindakan <span class="text-red-500">*</span></label>
                                        <textarea wire:model="actionFormData.action_text" 
                                            placeholder="Masukkan deskripsi tindakan yang akan dilakukan..."
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        </textarea>
                                    </div>

                                    {{-- Responsible Person & Deadline (2 columns) --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Responsible Person --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
                                            <input type="text" wire:model="actionFormData.responsible_person" 
                                                placeholder="Nama penanggung jawab..."
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        </div>

                                        {{-- Deadline --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Selesai</label>
                                            <input type="date" wire:model="actionFormData.deadline" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select wire:model="actionFormData.status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="pending">🟡 Pending (Belum dimulai)</option>
                                            <option value="ongoing">🔵 Ongoing (Sedang berjalan)</option>
                                            <option value="completed">✅ Completed (Selesai)</option>
                                        </select>
                                    </div>

                                    {{-- File Upload --}}
                                    <div class="border-t pt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">📎 Upload Bukti (Opsional)</label>
                                        <div class="relative border-2 border-dashed border-green-300 rounded-lg p-6 text-center cursor-pointer hover:border-green-500 hover:bg-green-50 transition"
                                            @dragover="$el.classList.add('border-green-500', 'bg-green-100')"
                                            @dragleave="$el.classList.remove('border-green-500', 'bg-green-100')"
                                            @drop.prevent="$el.classList.remove('border-green-500', 'bg-green-100'); $wire.handleFileUpload($event.dataTransfer.files)">
                                            <input type="file" wire:model="uploadedFiles" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                @change="$wire.handleFileUpload($event.target.files)">
                                            <div class="pointer-events-none">
                                                <p class="text-sm text-gray-600">Klik atau drag & drop file di sini</p>
                                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, PDF - Max 5MB per file</p>
                                            </div>
                                        </div>

                                        {{-- Uploaded Files Queue --}}
                                        @if(count($uploadedFiles) > 0)
                                            <div class="mt-3 space-y-2 bg-green-100 p-3 rounded-lg">
                                                <p class="text-sm font-medium text-gray-700">📦 File yang akan diupload:</p>
                                                @foreach($uploadedFiles as $index => $file)
                                                    <div class="flex items-center justify-between bg-white p-2 rounded border border-green-200">
                                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                                            @if(in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif']))
                                                                <span class="text-lg">🖼️</span>
                                            <span class="text-sm text-gray-700">{{ $file['name'] }} ({{ $file['size'] }}KB)</span>
                                                            @else
                                                                <span class="text-lg">📄</span>
                                                                <span class="text-sm text-gray-700">{{ $file['name'] }} ({{ $file['size'] }}KB)</span>
                                                            @endif
                                                        </div>
                                                        <button type="button" wire:click="removeUploadedFile({{ $index }})" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition flex-shrink-0">
                                                            ❌
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Existing Files (when editing) --}}
                                        @if(!empty($actionFormData['id']) && !empty($problem['actions']))
                                            @php
                                                $currentAction = collect($problem['actions'])->firstWhere('id', $actionFormData['id']);
                                                $existingMedia = $currentAction['media'] ?? [];
                                            @endphp
                                            @if(count($existingMedia) > 0)
                                                <div class="mt-3 space-y-2 bg-blue-100 p-3 rounded-lg">
                                                    <p class="text-sm font-medium text-gray-700">✅ File yang sudah tersimpan:</p>
                                                    @foreach($existingMedia as $media)
                                                        <div class="flex items-center justify-between bg-white p-2 rounded border border-blue-200">
                                                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                                                @if(in_array($media['mime'], ['image/jpeg', 'image/png', 'image/gif']))
                                                                    <span class="text-lg">🖼️</span>
                                                                @elseif($media['mime'] === 'application/pdf')
                                                                    <span class="text-lg">📄</span>
                                                                @else
                                                                    <span class="text-lg">📎</span>
                                                                @endif
                                                                <a href="{{ $media['url'] }}" target="_blank" class="text-sm text-blue-600 hover:underline truncate">{{ $media['name'] }}</a>
                                                            </div>
                                                            <button type="button" wire:click="deleteExistingFile({{ $currentAction['id'] }}, {{ $media['id'] }})" wire:confirm="Hapus file ini?" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition flex-shrink-0">
                                                                🗑
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- Form Actions --}}
                                <div class="flex gap-2 mt-4">
                                    <button wire:click="saveAction()" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                        💾 Simpan Tindakan
                                    </button>
                                    <button wire:click="resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border-2 border-dashed border-gray-300">
                <p class="text-gray-600 font-medium">📭 Tidak ada masalah</p>
                <p class="text-gray-500 text-sm mt-1">Data akan muncul setelah mengisi Timeline section</p>
            </div>
        @endforelse
    </div>

</div>

<script>
// Simple debug logging
console.log('✅ Problem Analysis Manager (FLAT MODE) loaded - no dropdown interference');
</script>
