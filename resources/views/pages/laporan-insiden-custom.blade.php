<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Custom Dashboard Laporan Insiden</title>

    @vite(['resources/css/app.css', 'resources/css/filament/theme.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="fi-filament-info-widget rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="space-y-6 p-4 sm:p-6">

                {{-- ============================================================
     Dashboard Kustom Laporan Insiden
     ============================================================ --}}

                <div class="space-y-6">

                    {{-- ── Header ─────────────────────────────────────────────── --}}
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                            Dashboard Laporan Insiden
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Tampilan disusun sesuai widget Filament dengan data yang dirender dari controller.
                        </p>
                    </div>

                    <div class="h-px bg-gray-200 dark:bg-white/10"></div>

                    {{-- ── Panel Filter ────────────────────────────────────────── --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-gray-900/60">

                        <p class="mb-4 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            <x-filament::icon
                                icon="heroicon-m-adjustments-horizontal"
                                class="h-4 w-4" />
                            Parameter Filter
                        </p>

                        <form
                            method="GET"
                            action="{{ route('laporan-insiden.custom-dashboard') }}"
                            class="grid grid-cols-2 items-end gap-4 sm:grid-cols-3 lg:grid-cols-6">

                            {{-- Tahun --}}
                            <div class="flex flex-col gap-1.5">
                                <label
                                    for="year"
                                    class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    Tahun
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select name="year" id="year">
                                        @foreach ($availableYears as $year)
                                        <option value="{{ $year }}" @selected($selectedYear===(int) $year)>
                                            {{ $year }}
                                        </option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>

                            {{-- Grouping --}}
                            <div class="flex flex-col gap-1.5">
                                <label
                                    for="grouping"
                                    class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    Grouping
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select name="grouping" id="grouping">
                                        <option value="none" @selected($grouping==='none' )>None</option>
                                        <option value="quarter" @selected($grouping==='quarter' )>Quartal</option>
                                        <option value="semester" @selected($grouping==='semester' )>Semester</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>

                            {{-- Periode --}}
                            <div class="flex flex-col gap-1.5">
                                <label
                                    for="period"
                                    class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    Periode
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select name="period" id="period">
                                        @foreach ($periodOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($period===(int) $value)>
                                            {{ $label }}
                                        </option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>

                            {{-- Risk Breakdown --}}
                            <div class="flex flex-col gap-1.5">
                                <label
                                    for="breakdown"
                                    class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    Risk Breakdown
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select name="breakdown" id="breakdown">
                                        <option value="period" @selected($breakdown==='period' )>Akumulasi</option>
                                        <option value="monthly" @selected($breakdown==='monthly' )>Bulanan</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>

                            {{-- Status --}}
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                    Status
                                </span>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-2 rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-gray-800/60">
                                    @foreach ($statusOptions as $statusKey => $statusLabel)
                                    <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                        <input
                                            type="checkbox"
                                            name="statuses[]"
                                            value="{{ $statusKey }}"
                                            class="h-3.5 w-3.5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700"
                                            @checked(in_array($statusKey, $selectedStatuses, true))>
                                        <span>{{ $statusLabel }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    <x-filament::icon icon="heroicon-m-funnel" class="h-4 w-4" />
                                    Terapkan
                                </button>
                            </div>

                        </form>
                    </div>

                    {{-- ── Kartu Statistik ─────────────────────────────────────── --}}
                    <div class="grid grid-cols-3 gap-3">

                        {{-- Total Insiden (aksen) --}}
                        <div class="rounded-xl border border-teal-200 bg-teal-50 p-5 dark:border-teal-800/60 dark:bg-teal-950/40">
                            <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-teal-600 dark:text-teal-400">
                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-4 w-4" />
                                Total Insiden
                            </p>
                            <p class="mt-3 text-4xl font-semibold tabular-nums text-teal-800 dark:text-teal-200">
                                {{ $jenisReport['summary']['total_count'] ?? 0 }}
                            </p>
                            <p class="mt-1 text-xs text-teal-600/70 dark:text-teal-400/70">
                                Seluruh periode aktif
                            </p>
                        </div>

                        {{-- Periode Aktif --}}
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                            <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                                Periode Aktif
                            </p>
                            <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $periodLabel }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                Grouping: {{ ucfirst($grouping) }}
                            </p>
                        </div>

                        {{-- Tahun Pelaporan --}}
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
                            <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <x-filament::icon icon="heroicon-m-calendar" class="h-4 w-4" />
                                Tahun Pelaporan
                            </p>
                            <p class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $selectedYear }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                Tahun fiskal berjalan
                            </p>
                        </div>

                    </div>

                </div>

                <div class="space-y-3 mt-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                Tren Laporan Insiden
                            </h3>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Ringkasan tren bulanan sesuai filter aktif.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $trendStats['total'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Rata-rata</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $trendStats['average'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Puncak</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $trendStats['peakValue'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $trendStats['peakMonthName'] ?? '-' }}</p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Growth</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $trendStats['growth'] ?? 0 }}%</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/50">
                                    @foreach($trendCategories as $category)
                                    <th class="border border-gray-200 px-3 py-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ $category }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                <tr>
                                    @foreach($trendSeries as $value)
                                    <td class="border border-gray-200 px-3 py-2 text-center font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $value }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 mt-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Distribusi Jenis Insiden</h4>
                            <ul class="space-y-2 text-sm">
                                @foreach(($jenisPie['labels'] ?? []) as $index => $label)
                                <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/50">
                                    <span class="text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $jenisPie['series'][$index] ?? 0 }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Distribusi Grading Risiko</h4>
                            <ul class="space-y-2 text-sm">
                                @foreach(($gradingPie['labels'] ?? []) as $index => $label)
                                <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/50">
                                    <span class="text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $gradingPie['series'][$index] ?? 0 }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

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

                    @include('filament.widgets.table-data.jenis-insiden', [
                    'rows' => $jenisReport['rows'] ?? [],
                    'summary' => $jenisReport['summary'] ?? [],
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

                    @include('filament.widgets.table-data.grading', [
                    'rows' => $gradingReport['rows'] ?? [],
                    'summary' => $gradingReport['summary'] ?? [],
                    'gradings' => ['Biru', 'Hijau', 'Kuning', 'Merah', 'Hitam'],
                    ])
                </div>

                <div class="space-y-3 mt-10">
                    <div class="border-b border-gray-200 pb-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Kinerja Penanganan Insiden per Unit Kerja
                        </h3>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Perbandingan jumlah laporan, status penanganan, dan efektivitas tindak lanjut insiden pada setiap unit kerja.
                        </p>
                    </div>

                    @php
                    $statusColumns = $statusOptions;
                    $unitColspan = 2 + count($statusColumns) + 1;
                    @endphp

                    <x-report-table>
                        <x-slot:colgroup>
                            <colgroup>
                                <col class="w-2/12">
                                <col class="w-1/12">
                                @foreach($statusColumns as $statusLabel)
                                <col class="w-1/12">
                                @endforeach
                                <col class="w-1/12">
                            </colgroup>
                        </x-slot:colgroup>

                        <x-slot:header>
                            <tr>
                                <x-report-table.th rowspan="2">Unit Kerja</x-report-table.th>
                                <x-report-table.th rowspan="2" align="center">Total</x-report-table.th>
                                <x-report-table.th :colspan="count($statusColumns) + 1" align="center">STATUS LAPORAN</x-report-table.th>
                            </tr>
                            <tr>
                                @foreach($statusColumns as $statusKey => $statusLabel)
                                <x-report-table.th align="center">{{ $statusLabel }}</x-report-table.th>
                                @endforeach
                                <x-report-table.th align="center">Close%</x-report-table.th>
                            </tr>
                        </x-slot:header>

                        @forelse($unitPerformanceRows as $row)
                        @php
                        $closeRateCellClass = ($row['close_rate'] ?? 0) >= 85
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                        : (($row['close_rate'] ?? 0) >= 70
                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                        : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300');
                        @endphp

                        <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40">
                            <x-report-table.td>{{ $row['unit_name'] }}</x-report-table.td>
                            <x-report-table.td align="center">{{ $row['total'] }}</x-report-table.td>
                            @foreach($statusColumns as $statusKey => $statusLabel)
                            <x-report-table.td align="center">{{ $row[$statusKey] ?? 0 }}</x-report-table.td>
                            @endforeach
                            <x-report-table.td
                                align="center"
                                class="font-semibold {{ $closeRateCellClass }}">
                                {{ $row['close_rate'] ?? 0 }}%
                            </x-report-table.td>
                        </tr>
                        @empty
                        <x-report-table.empty :colspan="$unitColspan" title="Data tidak tersedia" description="Belum terdapat data unit kerja pada periode yang dipilih." />
                        @endforelse
                    </x-report-table>
                </div>

                @if(!empty($priorityRiskTables))
                <div class="space-y-8 mt-10">
                    <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-white">
                                    Analisis Prioritas Risiko Unit Kerja
                                </h3>

                                <p class="max-w-3xl text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                                    Visualisasi tingkat risiko unit kerja berdasarkan jenis insiden, grading risiko, keterlambatan investigasi, dan efektivitas penyelesaian tindak lanjut.
                                </p>
                            </div>
                        </div>
                    </div>

                    @foreach($priorityRiskTables as $tableIndex => $table)
                    <div class="space-y-4 {{ $tableIndex > 0 ? 'pt-4' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <h4 class="text-sm font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                                    {{ $breakdown === 'monthly'
                                        ? 'Analisis Risiko Bulanan — ' . $table['title']
                                        : 'Ringkasan Akumulasi Risiko dan Tindak Lanjut Unit Kerja'
                                    }}
                                </h4>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Fokus analisa pada unit dengan risiko tinggi, overdue investigasi, dan performa penyelesaian.
                                </p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            @php
                            $riskColspan = 2 + count($priorityJenisColumns) + count($priorityGradingColumns) + 4;
                            @endphp

                            <x-report-table class="min-w-full border-separate border-spacing-0">
                                <x-slot:colgroup>
                                    <colgroup>
                                        <col class="w-[40px]">
                                        <col class="w-[340px]">

                                        @foreach($priorityJenisColumns as $key => $label)
                                        <col class="w-[90px]">
                                        @endforeach

                                        @foreach($priorityGradingColumns as $key => $label)
                                        <col class="w-[90px]">
                                        @endforeach

                                        <col class="w-[100px]">
                                        <col class="w-[120px]">
                                        <col class="w-[120px]">
                                    </colgroup>
                                </x-slot:colgroup>

                                <x-slot:header>
                                    <tr class="bg-gray-50 dark:bg-gray-800/80">
                                        <x-report-table.th
                                            rowspan="2"
                                            class="sticky left-0 z-20 border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-800">
                                            No
                                        </x-report-table.th>

                                        <x-report-table.th
                                            rowspan="2"
                                            class="sticky left-[70px] z-20 border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-800">
                                            Unit Kerja
                                        </x-report-table.th>

                                        <x-report-table.th
                                            align="center"
                                            :colspan="count($priorityJenisColumns)"
                                            class="border-b border-gray-200 bg-blue-50/50 text-blue-700 dark:border-gray-700 dark:bg-blue-500/5 dark:text-blue-300">
                                            Jenis Insiden
                                        </x-report-table.th>

                                        <x-report-table.th
                                            align="center"
                                            :colspan="count($priorityGradingColumns)"
                                            class="border-b border-gray-200 bg-rose-50/50 text-rose-700 dark:border-gray-700 dark:bg-rose-500/5 dark:text-rose-300">
                                            Grading Risiko
                                        </x-report-table.th>

                                        <x-report-table.th
                                            rowspan="2"
                                            align="center"
                                            class="border-b border-gray-200 dark:border-gray-700">
                                            Overdue
                                        </x-report-table.th>

                                        <x-report-table.th
                                            rowspan="2"
                                            align="center"
                                            class="border-b border-gray-200 dark:border-gray-700">
                                            Avg Resolve
                                        </x-report-table.th>

                                        <x-report-table.th
                                            rowspan="2"
                                            align="center"
                                            class="border-b border-gray-200 dark:border-gray-700">
                                            Close Rate
                                        </x-report-table.th>
                                    </tr>

                                    <tr class="bg-gray-50/70 dark:bg-gray-800/40">
                                        @foreach($priorityJenisColumns as $key => $label)
                                        <x-report-table.th
                                            align="center"
                                            class="border-b border-gray-200 text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            {{ $label }}
                                        </x-report-table.th>
                                        @endforeach

                                        @foreach($priorityGradingColumns as $key => $label)
                                        <x-report-table.th
                                            align="center"
                                            class="border-b border-gray-200 text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            {{ $label }}
                                        </x-report-table.th>
                                        @endforeach
                                    </tr>
                                </x-slot:header>

                                @foreach($table['rows'] as $row)
                                @php
                                $isCritical = str_contains($row['risk_level'], 'Critical');
                                $isHigh = str_contains($row['risk_level'], 'High');

                                $riskBadge = $isCritical
                                ? 'bg-red-100 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300'
                                : ($isHigh
                                ? 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300'
                                : 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300');

                                $closeRateColor = $row['close_rate'] >= 85
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                : ($row['close_rate'] >= 70
                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                                : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300');
                                @endphp

                                <tr class="group transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <x-report-table.td
                                        class="sticky left-0 z-10 border-b border-gray-100 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-900">
                                        <div class="flex justify-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-xl text-xs font-bold {{ $loop->first ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300' : ($loop->iteration <= 3 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300') }}">
                                                {{ $row['rank'] }}
                                            </div>
                                        </div>
                                    </x-report-table.td>

                                    <x-report-table.td
                                        class="sticky left-[70px] z-10 border-b border-gray-100 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-900">
                                        <div class="space-y-1">
                                            <div class="font-semibold text-gray-800 dark:text-gray-100">
                                                {{ $row['unit_name'] }}
                                            </div>
                                        </div>
                                    </x-report-table.td>

                                    @foreach($priorityJenisColumns as $key => $label)
                                    <x-report-table.td
                                        align="center"
                                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">
                                        <span class="font-mono text-sm font-semibold text-gray-700 dark:text-gray-200">
                                            {{ $row['jenis_counts'][$key] ?? 0 }}
                                        </span>
                                    </x-report-table.td>
                                    @endforeach

                                    @foreach($priorityGradingColumns as $key => $label)
                                    <x-report-table.td
                                        align="center"
                                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">
                                        <span class="inline-flex min-w-[34px] items-center justify-center rounded-lg px-2 py-1 text-xs font-bold {{ ($row['grading_counts'][$key] ?? 0) > 0 ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'text-gray-400' }}">
                                            {{ $row['grading_counts'][$key] ?? 0 }}
                                        </span>
                                    </x-report-table.td>
                                    @endforeach

                                    <x-report-table.td
                                        align="center"
                                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">
                                        <span class="text-sm font-bold {{ $row['overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $row['overdue'] }}
                                        </span>
                                    </x-report-table.td>

                                    <x-report-table.td
                                        align="center"
                                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">
                                        <span class="rounded-lg bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ $row['avg_resolve_days'] }} hari
                                        </span>
                                    </x-report-table.td>

                                    <x-report-table.td
                                        align="center"
                                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $closeRateColor }}">
                                                {{ $row['close_rate'] }}%
                                            </span>

                                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                                <div
                                                    class="h-full rounded-full bg-current {{ str_replace('text', 'bg', explode(' ', $closeRateColor)[1]) }}"
                                                    x-data
                                                    x-init="$el.style.width = $el.dataset.width + '%'"
                                                    data-width="{{ $row['close_rate'] }}">
                                                </div>
                                            </div>
                                        </div>
                                    </x-report-table.td>
                                </tr>
                                @endforeach
                            </x-report-table>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
</body>

</html>