@php
$jenisColumns = $this->getTable4JenisColumns();
$gradingColumns = $this->getTable4GradingColumns();
$tables = collect($this->getTable4PriorityRiskBreakdowns())
->filter(fn ($table) => !empty($table['rows']))
->values();

$colspan = 1 + count($jenisColumns) + count($gradingColumns) + 3;
@endphp

@if($tables->isNotEmpty())

<!-- TABLE 4 -->
<div class="mt-8 space-y-3">

    <div class="border-b border-gray-200 pb-3 dark:border-gray-700">

        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            🚨 Analisis Prioritas Risiko Unit Kerja
        </h3>

        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Menampilkan tingkat risiko setiap unit kerja berdasarkan jenis insiden,
            grading risiko, keterlambatan investigasi, dan penyelesaian tindak lanjut.
        </p>

    </div>

    @foreach($tables as $tableIndex => $table)

    <div class="space-y-3 {{ $tableIndex > 0 ? 'mt-10' : '' }}">

        <div class="flex items-center justify-between">

            <div>

                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">

                    {{ $this->breakdownMode === 'monthly'
                        ? 'Analisis Risiko Bulanan — ' . $table['title']
                        : 'Ringkasan Akumulasi per Quartal/Semester Risiko dan Tindak Lanjut Unit Kerja'
                    }}

                </h4>

            </div>

        </div>

        <x-report-table>

            <x-slot:colgroup>
                <colgroup>
                    <col>

                    @foreach($jenisColumns as $key => $label)
                    <col>
                    @endforeach

                    @foreach($gradingColumns as $key => $label)
                    <col>
                    @endforeach

                    <col>
                    <col>
                    <col>
                </colgroup>
            </x-slot:colgroup>

            <x-slot:header>

                <tr>
                    <x-report-table.th rowspan="2">
                        Unit Kerja
                    </x-report-table.th>

                    <x-report-table.th
                        align="center"
                        :colspan="count($jenisColumns)">

                        Jenis Insiden

                    </x-report-table.th>

                    <x-report-table.th
                        align="center"
                        :colspan="count($gradingColumns)">

                        Grading

                    </x-report-table.th>

                    <x-report-table.th rowspan="2" align="center">
                        Overdue
                    </x-report-table.th>

                    <x-report-table.th rowspan="2" align="center">
                        Avg Resolve
                    </x-report-table.th>

                    <x-report-table.th rowspan="2" align="center">
                        Close%
                    </x-report-table.th>
                </tr>

                <tr>

                    @foreach($jenisColumns as $key => $label)
                    <x-report-table.th align="center">
                        {{ $label }}
                    </x-report-table.th>
                    @endforeach

                    @foreach($gradingColumns as $key => $label)
                    <x-report-table.th align="center">
                        {{ $label }}
                    </x-report-table.th>
                    @endforeach

                </tr>

            </x-slot:header>

            @foreach($table['rows'] as $row)

            <tr
                class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40"
                style="background-color: {{
                    str_contains($row['risk_level'], 'Critical')
                        ? '#fee2e2'
                        : (str_contains($row['risk_level'], 'High')
                            ? '#fef3c7'
                            : 'transparent')
                }}">

                <x-report-table.td class="font-medium">
                    {{ $row['unit_name'] }}
                </x-report-table.td>

                @foreach($jenisColumns as $key => $label)
                <x-report-table.td align="center" class="font-semibold">
                    {{ $row['jenis_counts'][$key] ?? 0 }}
                </x-report-table.td>
                @endforeach

                @foreach($gradingColumns as $key => $label)
                <x-report-table.td align="center" class="font-semibold">
                    {{ $row['grading_counts'][$key] ?? 0 }}
                </x-report-table.td>
                @endforeach

                <x-report-table.td align="center" class="font-semibold">
                    {{ $row['overdue'] }}
                </x-report-table.td>

                <x-report-table.td align="center">
                    {{ $row['avg_resolve_days'] }} hari
                </x-report-table.td>

                <x-report-table.td
                    align="center"
                    class="font-semibold"
                    style="background-color: {{
                        $row['close_rate'] >= 85
                            ? '#d1fae5'
                            : ($row['close_rate'] >= 70
                                ? '#fef3c7'
                                : '#fee2e2')
                    }}">

                    {{ $row['close_rate'] }}%

                </x-report-table.td>

            </tr>

            @endforeach

        </x-report-table>

    </div>

    @endforeach

</div>

@endif