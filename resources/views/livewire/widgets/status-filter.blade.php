<div class="space-y-3 my-[1rem]">
    @php
    $statuses = [
    'draft' => ['label' => 'Draft', 'icon' => '📝'],
    'dilaporkan' => ['label' => 'Dilaporkan', 'icon' => '📢'],
    'diverifikasi' => ['label' => 'Verifikasi', 'icon' => '✓'],
    'investigasi' => ['label' => 'Investigasi', 'icon' => '🔍'],
    'selesai_investigasi' => ['label' => 'Selesai', 'icon' => '✅'],
    ];
    @endphp

    <div class="flex items-center justify-between gap-3">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Filter Status</div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">Gunakan chip status untuk mempersempit data.</div>
        </div>

        <span class="rounded-full bg-primary-100 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
            {{ count($this->statusFilter) }} dipilih
        </span>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($statuses as $value => $config)
        @php
        $isActive = in_array($value, $this->statusFilter);
        @endphp

        <button
            type="button"
            wire:click="toggleStatus('{{ $value }}')"
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-all duration-200 ring-1 ring-inset
                    {{ $isActive
                        ? 'bg-blue-600 text-white ring-primary-600 shadow-sm'
                        : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800' }}">
            <span>{{ $config['icon'] }}</span>
            <span>{{ $config['label'] }}</span>
        </button>
        @endforeach
    </div>
</div>