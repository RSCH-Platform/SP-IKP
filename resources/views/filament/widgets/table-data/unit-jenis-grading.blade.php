<!-- TABLE 2: DISTRIBUSI RISIKO INSIDEN -->
<div class="mt-10 space-y-5">

    <!-- Section Header -->
    <div class="flex flex-col gap-3 border-b border-gray-200 pb-4 dark:border-gray-800 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-1">
            <h3 class="text-base font-semibold tracking-tight text-gray-900 dark:text-white">
                Distribusi Risiko Insiden per Unit Kerja
            </h3>

            <p class="max-w-3xl text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                Sebaran grading risiko insiden berdasarkan jenis kejadian operasional pada masing-masing unit kerja.
            </p>
        </div>

        <!-- Optional Quick Info -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <div class="rounded-full bg-red-50 px-3 py-1 font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                Fokus Risiko Tinggi
            </div>

            <div class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                Monitoring Operasional
            </div>
        </div>
    </div>

    @forelse($this->getTable2UnitJenisGrading() as $index => $unitData)

    @php
    $totalInsiden = $unitData['subtotal']['total'] ?? 0;
    $totalMerah = $unitData['subtotal']['Merah'] ?? 0;
    $totalKuning = $unitData['subtotal']['Kuning'] ?? 0;
    @endphp

    <div
        x-data="{ expanded: {{ $index < 2 ? 'true' : 'false' }} }"
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition dark:border-gray-800 dark:bg-gray-900">

        <!-- Accordion Header -->
        <button
            type="button"
            @click="expanded = !expanded"
            class="flex w-full items-center justify-between px-4 py-4 text-left transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <div class="min-w-0 flex-1">

                <!-- Unit Title -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-base dark:bg-gray-800">
                        🏥
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $unitData['unit_name'] }}
                        </h4>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Analisis distribusi grading risiko insiden
                        </p>
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-3 flex flex-wrap items-center gap-2">

                    <div class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Total:
                        <span class="font-semibold">{{ $totalInsiden }}</span>
                    </div>

                    <div class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-medium text-red-700 dark:bg-red-500/10 dark:text-red-400">
                        Risiko Merah:
                        <span class="font-semibold">{{ $totalMerah }}</span>
                    </div>

                    <div class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                        Risiko Kuning:
                        <span class="font-semibold">{{ $totalKuning }}</span>
                    </div>

                </div>
            </div>

            <!-- Toggle -->
            <div
                class="ml-4 flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                :class="{ 'rotate-180': expanded }">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transition-transform duration-200"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Table -->
        <div
            x-show="expanded"
            x-collapse
            class="border-t border-gray-100 dark:border-gray-800">

            <div class="overflow-x-auto">

                <x-report-table class="min-w-full">

                    <x-slot:colgroup>
                        <colgroup>
                            <col class="w-24">
                            <col class="w-20">
                            <col class="w-20">
                            <col class="w-20">
                            <col class="w-20">
                            <col class="w-24">
                        </colgroup>
                    </x-slot:colgroup>

                    <x-slot:header>

                        <!-- Header Level 1 -->
                        <tr class="bg-gray-50/80 dark:bg-gray-800/50">

                            <x-report-table.th
                                rowspan="2"
                                class="sticky left-0 z-20 bg-gray-50/95 backdrop-blur dark:bg-gray-800">
                                Jenis Insiden
                            </x-report-table.th>

                            <x-report-table.th
                                :colspan="4"
                                align="center"
                                class="text-[11px] uppercase tracking-wider text-gray-500">
                                Grading Risiko
                            </x-report-table.th>

                            <x-report-table.th
                                rowspan="2"
                                align="center"
                                class="font-semibold">
                                Total
                            </x-report-table.th>
                        </tr>

                        <!-- Header Level 2 -->
                        <tr class="bg-gray-50 dark:bg-gray-800/50">

                            <x-report-table.th
                                align="center"
                                class="bg-blue-200 dark:bg-info-500/20">
                                Biru
                            </x-report-table.th>

                            <x-report-table.th
                                align="center"
                                class="bg-green-200 dark:bg-success-500/20">
                                Hijau
                            </x-report-table.th>

                            <x-report-table.th
                                align="center"
                                class="bg-amber-200 dark:bg-warning-500/20">
                                Kuning
                            </x-report-table.th>

                            <x-report-table.th
                                align="center"
                                class="bg-red-200 dark:bg-danger-500/20">
                                Merah
                            </x-report-table.th>

                        </tr>

                    </x-slot:header>

                    @foreach($unitData['items'] as $item)

                    <tr class="group transition hover:bg-gray-50/70 dark:hover:bg-gray-800/40">

                        <!-- Jenis -->
                        <x-report-table.td
                            class="sticky left-0 z-10 bg-white font-medium text-gray-700 group-hover:bg-gray-50/70 dark:bg-gray-900 dark:text-gray-200 dark:group-hover:bg-gray-800/40">
                            {{ $item['jenis'] }}
                        </x-report-table.td>

                        <!-- Biru -->
                        <x-report-table.td align="center">
                            <div class="rounded-lg px-2 py-1 font-mono text-sm font-semibold text-blue-700 dark:text-blue-400">
                                {{ $item['Biru'] }}
                            </div>
                        </x-report-table.td>

                        <!-- Hijau -->
                        <x-report-table.td align="center">
                            <div class="rounded-lg px-2 py-1 font-mono text-sm font-semibold text-green-700 dark:text-green-400">
                                {{ $item['Hijau'] }}
                            </div>
                        </x-report-table.td>

                        <!-- Kuning -->
                        <x-report-table.td align="center">
                            <div class="rounded-lg px-2 py-1 font-mono text-sm font-semibold text-amber-700 dark:text-amber-400">
                                {{ $item['Kuning'] }}
                            </div>
                        </x-report-table.td>

                        <!-- Merah -->
                        <x-report-table.td align="center">
                            <div class="rounded-lg px-2 py-1 font-mono text-sm font-bold text-red-700 dark:text-red-400">
                                {{ $item['Merah'] }}
                            </div>
                        </x-report-table.td>

                        <!-- Total -->
                        <x-report-table.td
                            align="center"
                            mono
                            class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $item['total'] }}
                        </x-report-table.td>

                    </tr>

                    @endforeach

                    <!-- Footer Summary -->
                    <tr class="border-t-2 border-gray-200 bg-gray-50/90 dark:border-gray-700 dark:bg-gray-800/70">

                        <x-report-table.td class="font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                            Total {{ $unitData['unit_name'] }}
                        </x-report-table.td>

                        <x-report-table.td align="center">
                            <span class="font-bold text-blue-700 dark:text-blue-400">
                                {{ $unitData['subtotal']['Biru'] }}
                            </span>
                        </x-report-table.td>

                        <x-report-table.td align="center">
                            <span class="font-bold text-green-700 dark:text-green-400">
                                {{ $unitData['subtotal']['Hijau'] }}
                            </span>
                        </x-report-table.td>

                        <x-report-table.td align="center">
                            <span class="font-bold text-amber-700 dark:text-amber-400">
                                {{ $unitData['subtotal']['Kuning'] }}
                            </span>
                        </x-report-table.td>

                        <x-report-table.td align="center">
                            <span class="font-bold text-red-700 dark:text-red-400">
                                {{ $unitData['subtotal']['Merah'] }}
                            </span>
                        </x-report-table.td>

                        <x-report-table.td
                            align="center"
                            mono
                            class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $unitData['subtotal']['total'] }}
                        </x-report-table.td>

                    </tr>

                </x-report-table>

            </div>

        </div>

    </div>

    @empty

    <!-- Empty State -->
    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center dark:border-gray-700 dark:bg-gray-900">

        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm dark:bg-gray-800">
            📭
        </div>

        <h4 class="mt-4 text-sm font-semibold text-gray-800 dark:text-gray-200">
            Belum terdapat data distribusi insiden
        </h4>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Tidak ada data unit kerja pada periode yang dipilih.
        </p>

    </div>

    @endforelse

</div>