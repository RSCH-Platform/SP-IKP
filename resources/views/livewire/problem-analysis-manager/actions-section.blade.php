<div x-data="{ openActionModal: false }" @close-action-modal.window="openActionModal = false" class="space-y-3 border-b border-gray-200 dark:border-slate-700 pb-6 bg-white dark:bg-slate-800">
    <div class="flex items-center justify-between">
        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <span class="text-xl">✅</span> Tindakan Korektif & Preventif
        </h4>
        @if(! ($isReadOnly ?? false))
        <button @click="openActionModal = true; $wire.addAction({{ $problem['id'] }})" class="px-3 py-1.5 text-sm bg-green-500 text-white rounded-md hover:bg-green-600 transition font-medium">
            ➕ Tambah Tindakan
        </button>
        @else
        <button disabled class="px-3 py-1.5 text-sm rounded-md bg-slate-200 text-slate-500 cursor-not-allowed">🔒 Tambah Tindakan</button>
        @endif
    </div>

    @if(count($problem['actions'] ?? []) > 0)
    <div class="space-y-2">
        @foreach($problem['actions'] as $action)
        <div class="p-3 border border-green-200 rounded-lg bg-green-50 hover:bg-green-100 transition dark:border-green-700 dark:bg-green-900/20 hover:dark:bg-green-900/30">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ Str::limit($action['action_text'] ?? '', 100) }}</p>
                </div>
                    <div class="flex gap-1 flex-shrink-0">
                    @if(! ($isReadOnly ?? false))
                    <button @click="openActionModal = true; $wire.editAction({{ $action['id'] }})" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition font-bold">
                        ✎ EDIT
                    </button>
                    <button wire:click="deleteAction({{ $action['id'] }})" wire:confirm="Hapus tindakan ini?" class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 transition">
                        🗑 DEL
                    </button>
                    @else
                    <span class="text-xs text-slate-500">🔒</span>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                <div>
                    <span class="text-gray-600 dark:text-gray-400 font-medium uppercase">Penanggung Jawab:</span>
                    <p class="text-gray-700 dark:text-gray-200">{{ $action['responsible_person'] ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400 font-medium uppercase">Deadline:</span>
                    <p class="text-gray-700 dark:text-gray-200">{{ $action['deadline'] ?? '-' }}</p>
                </div>
                <div class="col-span-2">
                    <span class="inline-block px-2 py-1 text-xs font-bold rounded text-white {{ ($action['status'] ?? '') === 'pending' ? 'bg-gray-500' : (($action['status'] ?? '') === 'ongoing' ? 'bg-blue-500' : 'bg-green-600') }}">
                        {{ ucfirst($action['status'] ?? 'pending') }}
                    </span>
                </div>
            </div>

                    @if(!empty($action['media']) && count($action['media']) > 0)
            <div class="mt-2 pt-2 border-t border-green-300">
                <p class="text-xs font-medium text-gray-600 mb-1">📎 Bukti ({{ count($action['media']) }})</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($action['media'] as $file)
                    <a href="{{ $file['url'] }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-white border border-green-300 rounded hover:bg-green-50 transition dark:bg-slate-900 dark:border-green-700 hover:dark:bg-slate-800">
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
    <div class="p-4 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-800 rounded-lg text-sm">
        Belum ada tindakan.
    </div>
    @endif

    @component('livewire.problem-analysis-manager.modal', ['openState' => 'openActionModal'])
    <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
        {{ isset($actionFormData['id']) ? '✎ Edit Tindakan' : '➕ Tambah Tindakan Baru' }}
    </h5>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Tindakan <span class="text-red-500">*</span></label>
            <textarea wire:model="actionFormData.action_text" placeholder="Masukkan deskripsi tindakan yang akan dilakukan..." rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Penanggung Jawab</label>
                <input type="text" wire:model="actionFormData.responsible_person" placeholder="Nama penanggung jawab..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Selesai</label>
                <input type="date" wire:model="actionFormData.deadline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select wire:model="actionFormData.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="pending">🟡 Pending (Belum dimulai)</option>
                <option value="ongoing">🔵 Ongoing (Sedang berjalan)</option>
                <option value="completed">✅ Completed (Selesai)</option>
            </select>
        </div>
        <div class="border-t pt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">📎 Upload Bukti (Opsional)</label>
            <div class="relative border-2 border-dashed border-green-300 rounded-lg p-6 text-center cursor-pointer hover:border-green-500 hover:bg-green-50 transition" @dragover="$el.classList.add('border-green-500', 'bg-green-100')" @dragleave="$el.classList.remove('border-green-500', 'bg-green-100')" @drop.prevent="$el.classList.remove('border-green-500', 'bg-green-100'); $wire.upload('temporaryUploadedFiles', $event.dataTransfer.files)">
                <input type="file" wire:model="temporaryUploadedFiles" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div class="pointer-events-none">
                    <p class="text-sm text-gray-600">Klik atau drag & drop file di sini</p>
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, PDF - Max 5MB per file</p>
                </div>
            </div>

            @if(!empty($existingActionMedia))
            <div class="mt-3 space-y-2 bg-slate-50 dark:bg-slate-900 p-3 rounded-lg">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">📦 File yang sudah tersimpan:</p>
                @foreach($existingActionMedia as $media)
                <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-2 rounded border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        @if(in_array($media['mime'], ['image/jpeg', 'image/png', 'image/gif']))
                        <span class="text-lg">🖼️</span>
                        @elseif($media['mime'] === 'application/pdf')
                        <span class="text-lg">📄</span>
                        @else
                        <span class="text-lg">📎</span>
                        @endif
                        <a href="{{ $media['url'] }}" target="_blank" class="text-sm text-gray-700 dark:text-gray-200 truncate">{{ $media['name'] }}</a>
                    </div>
                    @if($editingItemId && ! ($isReadOnly ?? false))
                    <button type="button" wire:click="deleteExistingFile({{ $editingItemId }}, {{ $media['id'] }})" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition flex-shrink-0">❌ Hapus</button>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

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
                    @if(! ($isReadOnly ?? false))
                    <button type="button" wire:click="removeUploadedFile({{ $index }})" class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition flex-shrink-0">❌</button>
                    @else
                    <button disabled class="text-xs bg-slate-200 text-slate-500 px-2 py-1 rounded">❌</button>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    <div class="flex gap-2 mt-4 justify-end">
        @if(! ($isReadOnly ?? false))
        <button wire:click="saveAction()" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">💾 Simpan Tindakan</button>
        @else
        <button disabled class="px-4 py-2 text-sm bg-slate-200 text-slate-500 rounded-lg cursor-not-allowed">🔒 Simpan</button>
        @endif
        <button @click="openActionModal = false; $wire.resetForm()" class="px-4 py-2 text-sm bg-gray-300 text-gray-700 dark:text-gray-900 rounded-lg hover:bg-gray-400 transition">Batal</button>
    </div>
    @endcomponent
</div>