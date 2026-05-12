@php
$jenisColumns = $this->getTable4JenisColumns();
$gradingColumns = $this->getTable4GradingColumns();
$gradingHeaderClasses = [
    'Biru' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
    'Hijau' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
    'Kuning' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
    'Merah' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
];

$tables = collect($this->getTable4PriorityRiskBreakdowns())
->filter(fn ($table) => !empty($table['rows']))
->values();

$colspan = 2 + count($jenisColumns) + count($gradingColumns) + 4;
@endphp

@if($tables->isNotEmpty())

<div class="mt-8 space-y-8">

    <div class="border-b border-gray-200 pb-4 dark:border-gray-800">

        <div class="flex items-start justify-between gap-4">

            <div class="space-y-1">

                <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-white">
                    Analisis Prioritas Risiko Unit Kerja
                </h3>

                <p class="max-w-3xl text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                    Visualisasi tingkat risiko unit kerja berdasarkan jenis insiden,
                    grading risiko, keterlambatan investigasi, dan efektivitas penyelesaian tindak lanjut.
                </p>

            </div>

        </div>

    </div>

    @foreach($tables as $tableIndex => $table)

    <div class="space-y-4 {{ $tableIndex > 0 ? 'pt-4' : '' }}">

        <div class="flex items-center justify-between">

            <div class="space-y-1">

                <h4 class="text-sm font-semibold tracking-tight text-gray-800 dark:text-gray-100">

                    {{ $this->breakdownMode === 'monthly'
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

            <x-report-table
                class="min-w-full border-separate border-spacing-0">

                <x-slot:colgroup>

                    <colgroup>
                        <col class="w-[40px]">
                        <col class="w-[340px]">

                        @foreach($jenisColumns as $key => $label)
                        <col class="w-[90px]">
                        @endforeach

                        @foreach($gradingColumns as $key => $label)
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
                            :colspan="count($jenisColumns)"
                            class="border-b border-gray-200 bg-blue-50/50 text-blue-700 dark:border-gray-700 dark:bg-blue-500/5 dark:text-blue-300">

                            Jenis Insiden

                        </x-report-table.th>

                        <x-report-table.th
                            align="center"
                            :colspan="count($gradingColumns)"
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

                        @foreach($jenisColumns as $key => $label)

                        <x-report-table.th
                            align="center"
                            class="border-b border-gray-200 text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">

                            {{ $label }}

                        </x-report-table.th>

                        @endforeach

                        @foreach($gradingColumns as $key => $label)

                        <x-report-table.th
                            align="center"
                            class="border-b border-gray-200 text-xs font-semibold {{ $gradingHeaderClasses[$label] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }} dark:border-gray-700">

                            {{ $label }}

                        </x-report-table.th>

                        @endforeach

                    </tr>

                </x-slot:header>

                @foreach($table['rows'] as $row)

                @php
                $isCritical = str_contains($row['risk_level'], 'Critical');
                $isHigh = str_contains($row['risk_level'], 'High');

                $riskBadge =
                $isCritical
                ? 'bg-red-100 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300'
                : ($isHigh
                ? 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300'
                : 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300');

                $closeRateColor =
                $row['close_rate'] >= 85
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                : ($row['close_rate'] >= 70
                ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300');
                @endphp

                <tr class="group transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                    <x-report-table.td
                        class="sticky left-0 z-10 border-b border-gray-100 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-900">

                        <div class="flex justify-center">

                            <div class="
                                flex h-8 w-8 items-center justify-center rounded-xl text-xs font-bold
                                {{
                                    $loop->first
                                        ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300'
                                        : ($loop->iteration <= 3
                                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')
                                }}
                            ">
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

                    @foreach($jenisColumns as $key => $label)

                    <x-report-table.td
                        align="center"
                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">

                        <span class="font-mono text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ $row['jenis_counts'][$key] ?? 0 }}
                        </span>

                    </x-report-table.td>

                    @endforeach

                    @foreach($gradingColumns as $key => $label)

                    <x-report-table.td
                        align="center"
                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">

                        <span class="
                            inline-flex min-w-[34px] items-center justify-center rounded-lg px-2 py-1 text-xs font-bold
                            {{
                                ($row['grading_counts'][$key] ?? 0) > 0
                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300'
                                    : 'text-gray-400'
                            }}
                        ">
                            {{ $row['grading_counts'][$key] ?? 0 }}
                        </span>

                    </x-report-table.td>

                    @endforeach

                    <x-report-table.td
                        align="center"
                        class="border-b border-gray-100 px-3 py-4 dark:border-gray-800">

                        <span class="
                            text-sm font-bold
                            {{
                                $row['overdue'] > 0
                                    ? 'text-red-600 dark:text-red-400'
                                    : 'text-emerald-600 dark:text-emerald-400'
                            }}
                        ">
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
                                    style="width: {{ $row['close_rate'] }}%">
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