@php
$statusOptions = [
'dilaporkan' => [
'label' => 'Dilaporkan',
'color' => 'sky',
],

'diverifikasi' => [
'label' => 'Diverifikasi',
'color' => 'emerald',
],

'investigasi' => [
'label' => 'Investigasi',
'color' => 'violet',
],

'selesai_investigasi' => [
'label' => 'Selesai Investigasi',
'color' => 'green',
],
];

$defaultStatuses = array_keys($statusOptions);

$allStatuses = array_keys($statusOptions);

if (blank($statuses ?? [])) {
$this->statuses = $defaultStatuses;
$statuses = $defaultStatuses;
}
@endphp

<div
    x-data="{ open: false }"
    class="relative">

    <!-- Trigger -->
    <button
        type="button"
        @click="open = true"
        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:border-primary-300 hover:bg-gray-50 hover:text-primary-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-primary-500/50 dark:hover:bg-gray-800">

        <!-- Icon -->
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.8">

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 5h18M6 12h12M10 19h4" />

        </svg>

        <span>Status Laporan</span>

        <!-- Counter -->
        @if(count($statuses ?? []))

        <span
            class="inline-flex bg-blue-200 items-center justify-center rounded-full bg-primary-500/10 px-2 py-0.5 text-xs font-semibold text-primary-600 dark:text-primary-400">

            {{ count($statuses) }}

        </span>

        @endif

    </button>

    <!-- Modal -->
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <!-- Backdrop -->
        <div
            @click="open = false"
            class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Content -->
        <div
            x-show="open"
            x-transition
            class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">

            <!-- Header -->
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">

                <div class="flex items-start justify-between gap-4">

                    <div>

                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Filter Status Laporan
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Pilih satu atau beberapa status laporan
                        </p>

                    </div>

                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">

                        ✕

                    </button>

                </div>

            </div>

            <!-- Body -->
            <div class="p-5">

                <div class="grid grid-cols-2 gap-3">

                    @foreach($statusOptions as $value => $status)

                    @php
                    $active = in_array($value, $statuses ?? []);
                    @endphp

                    <button
                        type="button"
                        @click="
                                let values = [...@js($statuses ?? [])]

                                if (values.includes('{{ $value }}')) {
                                    values = values.filter(v => v !== '{{ $value }}')
                                } else {
                                    values.push('{{ $value }}')
                                }

                                $wire.set('statuses', values)
                            "
                        class="
                                flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition-all duration-200

                                {{ $active
                                    ? 'border-primary-500 bg-primary-50 dark:border-primary-500 dark:bg-primary-500/10'
                                    : 'border-gray-200 hover:border-primary-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'
                                }}
                            ">

                        <!-- Dot -->
                        <div class="h-3 w-3 rounded-full bg-{{ $status['color'] }}-500"></div>

                        <!-- Label -->
                        <span
                            class="
                                    text-sm font-medium

                                    {{ $active
                                        ? 'text-primary-700 dark:text-primary-300'
                                        : 'text-gray-700 dark:text-gray-200'
                                    }}
                                ">

                            {{ $status['label'] }}

                        </span>

                    </button>

                    @endforeach

                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-gray-800">

                <button
                    type="button"
                    wire:click="$set('statuses', @js($allStatuses))"
                    class="text-sm font-medium text-gray-500 transition hover:text-primary-600 dark:hover:text-primary-400">

                    Pilih Semua

                </button>

                <div class="flex items-center gap-2">

                    <button
                        type="button"
                        wire:click="$set('statuses', [])"
                        class="text-sm font-medium text-gray-500 transition hover:text-danger-600">

                        Reset

                    </button>

                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700">

                        Terapkan

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>