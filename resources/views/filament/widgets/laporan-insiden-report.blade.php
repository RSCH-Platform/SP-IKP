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
                    {{ collect($this->getReportData())->sum('count') }}
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

        <!-- Table -->
        <x-report-table>
            <x-slot:colgroup>
                <colgroup>
                    <col class="w-4/5">
                    <col class="w-1/5">
                </colgroup>
            </x-slot:colgroup>

            <x-slot:header>
                <tr>

                    <th
                        class="border-b border-r border-gray-300 px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-700 dark:border-gray-700 dark:text-gray-300">
                        Bulan
                    </th>

                    <th
                        class="border-b border-gray-300 px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-700 dark:border-gray-700 dark:text-gray-300">
                        Jumlah Insiden
                    </th>

                </tr>
            </x-slot:header>

            @forelse($this->getReportData() as $row)
            <tr class="bg-white dark:bg-gray-900">

                <!-- Month -->
                <td
                    class="border-b border-r border-gray-200 px-5 py-3 text-sm font-medium text-gray-800 dark:border-gray-800 dark:text-gray-200">
                    {{ $row['month_label'] ?? $row['month'] }}
                </td>

                <!-- Count -->
                <td
                    class="border-b border-gray-200 px-5 py-3 text-center dark:border-gray-800">

                    <span
                        class="font-mono text-sm font-semibold tabular-nums text-gray-900 dark:text-gray-100">
                        {{ number_format($row['count']) }}
                    </span>

                </td>

            </tr>
            @empty
            <tr>

                <td colspan="2"
                    class="border-b border-gray-200 px-5 py-10 text-center dark:border-gray-800">

                    <div class="space-y-1">

                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Data laporan tidak tersedia.
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Belum terdapat data insiden pada periode yang dipilih.
                        </p>

                    </div>

                </td>

            </tr>
            @endforelse
        </x-report-table>
    </div>
</x-filament-widgets::widget>