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

$colspan = 2 + count($jenisColumns) + count($gradingColumns);
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

    @php
        $totalUnitKerja = count($table['rows']);
        $totalJenis = [];
        $totalGrading = [];
        
        foreach($jenisColumns as $key => $label) {
            $totalJenis[$key] = 0;
        }
        foreach($gradingColumns as $key => $label) {
            $totalGrading[$key] = 0;
        }

        foreach($table['rows'] as $row) {
            foreach($jenisColumns as $key => $label) {
                $totalJenis[$key] += $row['jenis_counts'][$key] ?? 0;
            }
            foreach($gradingColumns as $key => $label) {
                $totalGrading[$key] += $row['grading_counts'][$key] ?? 0;
            }
        }

        $grandTotalJenis = array_sum($totalJenis);
        $grandTotalGrading = array_sum($totalGrading);
    @endphp

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

        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <x-report-table
                class="min-w-max border-separate border-spacing-0">

                <x-slot:colgroup>

                    <colgroup>
                        <col class="w-[50px]">
                        <col class="min-w-[200px] max-w-[280px]">

                        @foreach($jenisColumns as $key => $label)
                        <col class="w-[70px]">
                        @endforeach

                        @foreach($gradingColumns as $key => $label)
                        <col class="w-[70px]">
                        @endforeach
                    </colgroup>

                </x-slot:colgroup>

                <x-slot:header>

                    <tr class="bg-gray-50 dark:bg-gray-800/80">

                        <x-report-table.th
                            rowspan="2"
                            class="sticky left-0 z-20 border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-800/90 text-center">
                            No
                        </x-report-table.th>

                        <x-report-table.th
                            rowspan="2"
                            class="sticky left-[50px] z-20 border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-gray-700 dark:bg-gray-800/90">
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

                    </tr>

                    <tr class="bg-gray-50/70 dark:bg-gray-800/40">

                        @foreach($jenisColumns as $key => $label)

                        <x-report-table.th
                            align="center"
                            class="border-b border-gray-200 text-[11px] font-bold text-gray-600 dark:border-gray-700 dark:text-gray-400 tracking-wider uppercase">
                            {{ $label }}
                        </x-report-table.th>

                        @endforeach

                        @foreach($gradingColumns as $key => $label)

                        <x-report-table.th
                            align="center"
                            class="border-b border-gray-200 text-[11px] font-bold uppercase tracking-wider {{ $gradingHeaderClasses[$label] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }} dark:border-gray-700">
                            {{ $label }}
                        </x-report-table.th>

                        @endforeach

                    </tr>

                </x-slot:header>

                @foreach($table['rows'] as $row)

                <tr class="group transition hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">

                    <x-report-table.td
                        class="sticky left-0 z-10 border-b border-gray-100 bg-white px-2 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex justify-center">
                            <div class="flex h-7 w-7 items-center justify-center text-xs font-bold tabular-nums text-gray-700 dark:text-gray-300">
                                {{ $row['rank'] }}
                            </div>
                        </div>
                    </x-report-table.td>

                    <x-report-table.td
                        class="sticky left-[50px] z-10 border-b border-gray-100 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $row['unit_name'] }}
                        </div>
                    </x-report-table.td>

                    @foreach($jenisColumns as $key => $label)
                    <x-report-table.td
                        align="center"
                        class="border-b border-gray-100 px-2 py-3 dark:border-gray-800">
                        @php $val = $row['jenis_counts'][$key] ?? 0; @endphp
                        @if($val > 0)
                            <span class="font-semibold text-gray-900 dark:text-gray-100 tabular-nums">
                                {{ $val }}
                            </span>
                        @else
                            <span class="text-gray-300 dark:text-gray-700 tabular-nums">-</span>
                        @endif
                    </x-report-table.td>
                    @endforeach

                    @foreach($gradingColumns as $key => $label)
                    <x-report-table.td
                        align="center"
                        class="border-b border-gray-100 px-2 py-3 dark:border-gray-800">
                        @php $val = $row['grading_counts'][$key] ?? 0; @endphp
                        @if($val > 0)
                            <span class="font-semibold text-gray-900 dark:text-gray-100 tabular-nums">
                                {{ $val }}
                            </span>
                        @else
                            <span class="text-gray-300 dark:text-gray-700 tabular-nums">-</span>
                        @endif
                    </x-report-table.td>
                    @endforeach

                </tr>

                @endforeach

                <!-- Row 1: Total per kolom -->
                <tr class="bg-blue-50/60 dark:bg-blue-900/20">
                    <x-report-table.td class="sticky left-0 z-10 border-t-2 border-blue-200 bg-blue-50/90 px-2 py-3 dark:border-blue-800 dark:bg-blue-900/40 text-center">
                        <span class="text-sm font-bold text-blue-500 dark:text-blue-400">Σ</span>
                    </x-report-table.td>
                    
                    <x-report-table.td class="sticky left-[50px] z-10 border-t-2 border-blue-200 bg-blue-50/90 px-4 py-3 dark:border-blue-800 dark:bg-blue-900/40">
                        <div class="font-bold text-blue-900 dark:text-blue-100 uppercase text-[11px] tracking-wider">
                            Total ({{ $totalUnitKerja }} Unit)
                        </div>
                    </x-report-table.td>

                    @foreach($jenisColumns as $key => $label)
                    <x-report-table.td align="center" class="border-t-2 border-blue-200 px-2 py-3 dark:border-blue-800">
                        <span class="font-bold text-blue-900 dark:text-blue-100 tabular-nums">
                            {{ $totalJenis[$key] > 0 ? $totalJenis[$key] : '-' }}
                        </span>
                    </x-report-table.td>
                    @endforeach

                    @foreach($gradingColumns as $key => $label)
                    <x-report-table.td align="center" class="border-t-2 border-blue-200 px-2 py-3 dark:border-blue-800">
                        <span class="font-bold text-blue-900 dark:text-blue-100 tabular-nums">
                            {{ $totalGrading[$key] > 0 ? $totalGrading[$key] : '-' }}
                        </span>
                    </x-report-table.td>
                    @endforeach
                </tr>

                <!-- Row 2: Jumlah Keseluruhan -->
                <tr class="bg-indigo-100/50 dark:bg-indigo-900/30">
                    <x-report-table.td class="sticky left-0 z-10 border-t border-indigo-200 bg-indigo-50/90 px-2 py-4 dark:border-indigo-800 dark:bg-indigo-900/50 text-center">
                        
                    </x-report-table.td>
                    
                    <x-report-table.td class="sticky left-[50px] z-10 border-t border-indigo-200 bg-indigo-50/90 px-4 py-4 dark:border-indigo-800 dark:bg-indigo-900/50">
                        <div class="font-bold text-indigo-900 dark:text-indigo-100 uppercase text-[11px] tracking-wider">
                            Jumlah Insiden
                        </div>
                    </x-report-table.td>

                    <x-report-table.td align="center" colspan="{{ count($jenisColumns) }}" class="border-t border-indigo-200 px-4 py-4 dark:border-indigo-800">
                        <div class="flex flex-col items-center justify-center">
                            <span class="text-base font-black text-indigo-700 dark:text-indigo-300 tabular-nums">
                                {{ $grandTotalJenis }}
                            </span>
                        </div>
                    </x-report-table.td>

                    <x-report-table.td align="center" colspan="{{ count($gradingColumns) }}" class="border-t border-indigo-200 px-4 py-4 dark:border-indigo-800">
                        <div class="flex flex-col items-center justify-center">
                            <span class="text-base font-black text-indigo-700 dark:text-indigo-300 tabular-nums">
                                {{ $grandTotalGrading }}
                            </span>
                        </div>
                    </x-report-table.td>
                </tr>

            </x-report-table>

        </div>

    </div>

    @endforeach

</div>

@endif
