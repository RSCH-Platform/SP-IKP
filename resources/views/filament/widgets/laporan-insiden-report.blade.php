<x-filament-widgets::widget class="fi-filament-info-widget">
    <div class="space-y-6 fi-section p-4">

        <!-- Header -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Laporan Insiden Keselamatan
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Ringkasan jumlah insiden berdasarkan periode pelaporan.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-end gap-3">

                <x-status-filter-modal :statuses="$statuses" />

                <div class="min-w-[140px]">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Tahun
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="year">
                            @foreach($this->getAvailableYears() as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div class="min-w-[160px]">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Grouping
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="grouping">
                            <option value="quarter">Quartal</option>
                            <option value="semester">Semester</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div class="min-w-[140px]">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Periode
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="period">
                            @if($this->grouping === 'quarter')
                            <option value="1">Quartal 1</option>
                            <option value="2">Quartal 2</option>
                            <option value="3">Quartal 3</option>
                            <option value="4">Quartal 4</option>
                            @else
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            @endif
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="grid gap-4 md:grid-cols-3">

            <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-teal-500 to-cyan-600 p-5 text-white shadow-sm">
                <div class="text-sm font-medium text-white/80">
                    Total Insiden
                </div>

                <div class="mt-2 text-3xl font-bold">
                    {{ $this->getReportDataJenisInsiden()['summary']['total_count'] ?? 0 }}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Periode Aktif
                </div>

                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $this->periodeLabel() }}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Tahun Pelaporan
                </div>

                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $year }}
                </div>
            </div>

        </div>

        <!-- Table Jenis Insiden -->
        <div class="space-y-3 mt-10">

            <div class="flex items-center justify-between">

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Laporan Berdasarkan Jenis Insiden
                    </h3>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Rekap jumlah insiden berdasarkan kategori insiden pada periode yang dipilih.
                    </p>
                </div>

            </div>

            @php
            $reportData = $this->getReportDataJenisInsiden();
            @endphp

            @include('filament.widgets.table-data.jenis-insiden', [
            'rows' => $reportData['rows'] ?? [],
            'summary' => $reportData['summary'] ?? [],
            ])
        </div>


        <div class="space-y-3 mt-10">

            <div class="flex items-center justify-between">

                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Laporan Berdasarkan Grading Risiko
                    </h3>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Rekap jumlah insiden berdasarkan grading risiko pada periode yang dipilih.
                    </p>
                </div>

            </div>

            @php
            $reportDataGrading = $this->getReportDataGrading() ?? [];
            @endphp

            @include('filament.widgets.table-data.grading', [
            'rows' => $reportDataGrading['rows'] ?? [],
            'summary' => $reportDataGrading['summary'] ?? [],
            'gradings' => ['Biru','Hijau','Kuning','Merah','Hitam'],
            ])
        </div>


    </div>
</x-filament-widgets::widget>