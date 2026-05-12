<!-- TABLE 3: JENIS INSIDEN x GRADING -->
<div class="mt-8 space-y-3">
    <div class="border-b border-gray-200 pb-3 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            🎯 TABLE 3: Jenis Insiden × Grading (Strategic Analytics)
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Distribusi agregat jenis insiden berdasarkan grading risiko (semua unit)
        </p>
    </div>

    @php
    $table3Data = $this->getTable3JenisGradingAgregat();
    @endphp

    <x-report-table>

        <x-slot:colgroup>
            <colgroup>
                <col class="w-[28%]">
                <col class="w-[12%]">
                <col class="w-[10%]">
                <col class="w-[10%]">
                <col class="w-[10%]">
                <col class="w-[10%]">
                <col class="w-[20%]">
            </colgroup>
        </x-slot:colgroup>

        <x-slot:header>

            <!-- Header Level 1 -->
            <tr class="bg-gray-50 dark:bg-gray-800/50">

                <x-report-table.th rowspan="2">
                    Jenis Insiden
                </x-report-table.th>

                <x-report-table.th rowspan="2" align="center">
                    Total
                </x-report-table.th>

                <x-report-table.th
                    align="center"
                    :colspan="4"
                    class="text-xs font-semibold uppercase tracking-wide">
                    Grading Risiko
                </x-report-table.th>

                <x-report-table.th rowspan="2" align="center">
                    Distribusi
                </x-report-table.th>

            </tr>

            <!-- Header Level 2 -->
            <tr>

                <x-report-table.th
                    align="center"
                    class="bg-blue-100 dark:bg-blue-500/20">
                    Biru
                </x-report-table.th>

                <x-report-table.th
                    align="center"
                    class="bg-green-100 dark:bg-green-500/20">
                    Hijau
                </x-report-table.th>

                <x-report-table.th
                    align="center"
                    class="bg-yellow-100 dark:bg-yellow-500/20">
                    Kuning
                </x-report-table.th>

                <x-report-table.th
                    align="center"
                    class="bg-red-200 dark:bg-red-500/20">
                    Merah
                </x-report-table.th>

            </tr>

        </x-slot:header>

        @forelse($table3Data['rows'] as $row)

        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/40">

            <!-- Jenis -->
            <x-report-table.td class="font-medium">
                {{ $row['jenis'] }}
            </x-report-table.td>

            <!-- Total -->
            <x-report-table.td
                align="center"
                mono
                class="font-semibold">
                {{ $row['total'] }}
            </x-report-table.td>

            <!-- Biru -->
            <x-report-table.td
                align="center">
                {{ $row['Biru'] }}
            </x-report-table.td>

            <!-- Hijau -->
            <x-report-table.td
                align="center">
                {{ $row['Hijau'] }}
            </x-report-table.td>

            <!-- Kuning -->
            <x-report-table.td
                align="center">
                {{ $row['Kuning'] }}
            </x-report-table.td>

            <!-- Merah -->
            <x-report-table.td
                align="center">
                {{ $row['Merah'] }}
            </x-report-table.td>

            <!-- Distribusi -->
            <x-report-table.td
                align="center"
                class="font-semibold">
                {{ $row['percentage'] }}%
            </x-report-table.td>

        </tr>

        @empty

        <x-report-table.empty
            colspan="7"
            title="Data distribusi insiden tidak tersedia"
            description="Belum terdapat data jenis insiden pada periode yang dipilih." />

        @endforelse

        @if(!empty($table3Data['rows']))

        <!-- Summary Row -->
        <tr class="border-t-2 border-gray-200 bg-gray-100/80 font-bold dark:border-gray-700 dark:bg-gray-800">

            <x-report-table.td class="uppercase tracking-wide">
                Total Keseluruhan
            </x-report-table.td>

            <x-report-table.td
                align="center"
                mono
                class="font-bold">
                {{ $table3Data['totals']['total'] }}
            </x-report-table.td>

            <x-report-table.td
                align="center">
                {{ $table3Data['totals']['Biru'] }}
            </x-report-table.td>

            <x-report-table.td
                align="center">
                {{ $table3Data['totals']['Hijau'] }}
            </x-report-table.td>

            <x-report-table.td
                align="center">
                {{ $table3Data['totals']['Kuning'] }}
            </x-report-table.td>

            <x-report-table.td
                align="center">
                {{ $table3Data['totals']['Merah'] }}
            </x-report-table.td>

            <x-report-table.td align="center">
                {{ $table3Data['totals']['percentage'] }}%
            </x-report-table.td>

        </tr>

        @endif

    </x-report-table>
</div>