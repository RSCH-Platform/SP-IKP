<x-filament-widgets::widget>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-950">
        {{-- Header --}}
        <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                        Investigasi Selesai
                    </h3>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Rekap laporan investigasi yang telah selesai diproses.
                    </p>
                </div>

            </div>

            {{-- Accordion Filter --}}
            <details class="group mt-4">
                <summary
                    class="flex cursor-pointer list-none items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200 dark:hover:bg-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-funnel" class="h-4 w-4 text-slate-500 dark:text-slate-400" />

                        <span>Filter laporan</span>
                    </div>

                    <x-filament::icon icon="heroicon-o-chevron-down"
                        class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
                </summary>

                <div
                    class="mt-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
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
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
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
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
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
                            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
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
        <div class="p-4">
            <x-report-table tableClass="min-w-[1600px]"
                scrollClass="max-w-full rounded-xl border border-slate-200 dark:border-white/10">
                <x-slot:colgroup>
                    <colgroup>
                        <col class="w-[10%]">
                        <col class="w-[18%]">
                        <col class="w-[12%]">
                        <col class="w-[14%]">
                        <col class="w-[23%]">
                        <col class="w-[23%]">
                    </colgroup>
                </x-slot:colgroup>

                <x-slot:header>
                    <tr class="bg-slate-50 dark:bg-white/[0.03]">
                        <x-report-table.th>Tanggal</x-report-table.th>
                        <x-report-table.th>Insiden</x-report-table.th>
                        <x-report-table.th>Jenis</x-report-table.th>
                        <x-report-table.th>Unit</x-report-table.th>
                        <x-report-table.th>Akar Masalah</x-report-table.th>
                        <x-report-table.th>Rekomendasi</x-report-table.th>
                    </tr>
                </x-slot:header>

                @forelse ($rows ?? [] as $row)
                    <tr class="align-top transition hover:bg-slate-50/80 dark:hover:bg-white/[0.03]">
                        <x-report-table.td class="whitespace-nowrap">
                            {{ $row['tanggal_insiden'] }}
                        </x-report-table.td>

                        <x-report-table.td class="font-medium leading-relaxed">
                            {{ $row['deskripsi_kategori_insiden'] }}
                        </x-report-table.td>

                        <x-report-table.td class="whitespace-pre-line break-words">
                            {{ $row['jenis_insiden'] }}
                        </x-report-table.td>

                        <x-report-table.td class="whitespace-pre-line break-words">
                            {{ $row['unit_kerja'] }}
                        </x-report-table.td>

                        <x-report-table.td class="whitespace-pre-line break-words leading-relaxed">
                            {{ $row['akar_masalah'] }}
                        </x-report-table.td>

                        <x-report-table.td class="whitespace-pre-line break-words leading-relaxed">
                            {{ $row['rekomendasi'] }}
                        </x-report-table.td>
                    </tr>
                @empty
                    <x-report-table.empty :colspan="6" title="Belum ada data investigasi"
                        description="Tidak ada laporan yang sesuai dengan filter yang dipilih." />
                @endforelse
            </x-report-table>
        </div>
    </div>
</x-filament-widgets::widget>