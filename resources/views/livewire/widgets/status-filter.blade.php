<div class="space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Filter Status Laporan:</span>
        <span class="text-xs px-2 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 font-medium">
            {{ count($this->statusFilter) }} dipilih
        </span>
    </div>

    <div class="flex flex-wrap gap-2">
        @php
        $statuses = [
        'draft' => ['label' => 'Draft', 'icon' => '📝'],
        'dilaporkan' => ['label' => 'Dilaporkan', 'icon' => '📢'],
        'revisi' => ['label' => 'Revisi', 'icon' => '✏️'],
        'revisi_unit' => ['label' => 'Revisi Unit', 'icon' => '⚙️'],
        'diverifikasi' => ['label' => 'Verifikasi', 'icon' => '✓'],
        'investigasi' => ['label' => 'Investigasi', 'icon' => '🔍'],
        'selesai_investigasi' => ['label' => 'Selesai', 'icon' => '✅'],
        ];
        @endphp

        @foreach($statuses as $value => $config)
        @php
        $isActive = in_array($value, $this->statusFilter);
        @endphp

        <button
            type="button"
            wire:click="toggleStatus('{{ $value }}')"
            class="px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 inline-flex items-center gap-1.5
                    {{ $isActive 
                        ? 'bg-primary-500 text-white shadow-md ring-2 ring-offset-1 ring-primary-600' 
                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            <span>{{ $config['icon'] }}</span>{{ $config['label'] }}
        </button>
        @endforeach
    </div>

    <div class="flex gap-2 text-xs pt-2 border-t border-gray-200 dark:border-gray-700">
        <button
            type="button"
            wire:click="$set('statusFilter', @json(array_keys($statuses)))"
            class="px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">
            Semua
        </button>
        <button
            type="button"
            wire:click="$set('statusFilter', [])"
            class="px-3 py-1.5 rounded-md bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition">
            Reset
        </button>
    </div>
</div>