<x-filament-widgets::widget>
    <div
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-950">

        {{-- Header --}}
        <div class="border-b border-slate-200 px-4 py-3 dark:border-white/10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold leading-5 text-slate-900 dark:text-white">
                        Investigasi Selesai
                    </h3>

                    <p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">
                        Rekap laporan investigasi yang telah selesai diproses.
                    </p>
                </div>
            </div>

            {{-- Accordion Filter --}}
            <details class="group mt-3">
                <summary
                    class="flex cursor-pointer list-none items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200 dark:hover:bg-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <x-filament::icon
                            icon="heroicon-o-funnel"
                            class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400"
                        />

                        <span>Filter laporan</span>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-chevron-down"
                        class="h-3.5 w-3.5 text-slate-400 transition group-open:rotate-180"
                    />
                </summary>

                <div
                    class="mt-2 rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Tahun
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedYear">
                                    @foreach ($this->getAvailableYears() as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Bulan
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedMonth">
                                    <option value="">Semua bulan</option>
                                    @foreach ($this->getMonthOptions() as $monthValue => $monthLabel)
                                        <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Jenis Insiden
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedJenisInsiden">
                                    @foreach ($this->getIncidentTypeOptions() as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Status
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedStatus">
                                    @foreach ($this->getStatusOptions() as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </div>
            </details>
        </div>

        {{-- Table --}}
        @php
            // Style only: dibuat lebih compact
            $thPadding = 'px-2.5 py-2';
            $tdPadding = 'px-2.5 py-2';
            $textSize = 'text-[11px]';
        @endphp

        <div class="p-3">
            <x-report-table
                tableClass="min-w-[1320px] border-separate border-spacing-0"
                scrollClass="max-w-full overflow-x-auto rounded-lg border border-slate-200 dark:border-white/10"
            >
                <x-slot:colgroup>
                    <colgroup>
                        <col class="w-[8%]">
                        <col class="w-[27%]">
                        <col class="w-[11%]">
                        <col class="w-[13%]">
                        <col class="w-[20.5%]">
                        <col class="w-[20.5%]">
                    </colgroup>
                </x-slot:colgroup>

                <x-slot:header>
                    <tr class="bg-slate-50 text-slate-700 dark:bg-white/[0.04] dark:text-slate-200">
                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Tanggal
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Insiden
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Jenis
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Unit
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Akar Masalah
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Rekomendasi
                        </x-report-table.th>
                    </tr>
                </x-slot:header>

                @forelse ($rows ?? [] as $group)
                    @php
                        $base = $group['base'] ?? [];
                        $problems = $group['problems'] ?? [];
                        $rowspan = count($problems) ?: 1;
                    @endphp

                    @foreach ($problems as $i => $p)
                        <tr class="align-top transition hover:bg-slate-50/80 dark:hover:bg-white/[0.035]">
                            @if ($i === 0)
                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top whitespace-nowrap border-b border-slate-200 font-medium text-slate-600 dark:border-white/10 dark:text-slate-300"
                                >
                                    {{ $base['tanggal_insiden'] ?? '-' }}
                                </x-report-table.td>

                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 font-medium leading-5 text-slate-900 dark:border-white/10 dark:text-white"
                                >
                                    <div
                                        class="line-clamp-2"
                                        title="{{ $base['deskripsi_kategori_insiden'] ?? '-' }}"
                                    >
                                        {{ $base['deskripsi_kategori_insiden'] ?? '-' }}
                                    </div>
                                </x-report-table.td>

                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                                >
                                    <div
                                        class="line-clamp-2 break-words"
                                        title="{{ $base['jenis_insiden'] ?? '-' }}"
                                    >
                                        {{ $base['jenis_insiden'] ?? '-' }}
                                    </div>
                                </x-report-table.td>

                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                                >
                                    <div
                                        class="line-clamp-2 break-words"
                                        title="{{ $base['unit_kerja'] ?? '-' }}"
                                    >
                                        {{ $base['unit_kerja'] ?? '-' }}
                                    </div>
                                </x-report-table.td>
                            @endif

                            <x-report-table.td
                                class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                            >
                                <div
                                    class="line-clamp-3 break-words"
                                    title="{{ $p['akar_masalah'] ?? '-' }}"
                                >
                                    {{ $p['akar_masalah'] ?? '-' }}
                                </div>
                            </x-report-table.td>

                            <x-report-table.td
                                class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                            >
                                <div
                                    class="line-clamp-3 break-words"
                                    title="{{ $p['rekomendasi'] ?? '-' }}"
                                >
                                    {{ $p['rekomendasi'] ?? '-' }}
                                </div>
                            </x-report-table.td>
                        </tr>
                    @endforeach
                @empty
                    <x-report-table.empty
                        :colspan="6"
                        title="Belum ada data investigasi"
                        description="Tidak ada laporan yang sesuai dengan filter yang dipilih."
                    />
                @endforelse
            </x-report-table>

            @if ($paginator->hasPages())
                <div class="mt-3">
                    {{ $paginator->links() }}
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>