@php
$gradings = $gradings ?? ['Biru','Hijau','Kuning','Merah','Hitam'];
$rows = $rows ?? [];
$summary = $summary ?? [];
@endphp

<x-report-table>

    <x-slot:colgroup>
        <colgroup>
            <col class="w-2/12">

            @foreach($gradings as $g)
            <col class="w-1/12">
            @endforeach

            <col class="w-2/12">
        </colgroup>
    </x-slot:colgroup>

    <x-slot:header>

        <tr>

            <x-report-table.th rowspan="2">
                Bulan
            </x-report-table.th>

            <x-report-table.th
                :colspan="count($gradings)"
                align="center">
                Grading
            </x-report-table.th>

            <x-report-table.th
                rowspan="2"
                align="center">
                Total Jumlah
            </x-report-table.th>

        </tr>

        <tr>

            @foreach($gradings as $g)

            <x-report-table.th align="center">
                {{ $g }}
            </x-report-table.th>

            @endforeach

        </tr>

    </x-slot:header>

    @forelse($rows as $row)

    <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40">

        <x-report-table.td>
            {{ $row['month_label'] ?? $row['month'] }}
        </x-report-table.td>

        @foreach($gradings as $g)

        <x-report-table.td align="center">
            {{ $row[$g] ?? 0 }}
        </x-report-table.td>

        @endforeach

        <x-report-table.td
            align="center"
            mono>

            {{ number_format($row['total_count'] ?? 0) }}

        </x-report-table.td>

    </tr>

    @empty

    <x-report-table.empty
        :colspan="count($gradings) + 2"
        title="Data laporan tidak tersedia"
        description="Belum terdapat data pada periode yang dipilih." />

    @endforelse

    <!-- Summary Row -->
    <tr class="bg-gray-200 font-semibold dark:bg-gray-900">

        <x-report-table.td>
            TOTAL
        </x-report-table.td>

        @foreach($gradings as $g)

        <x-report-table.td align="center">
            {{ $summary[$g] ?? 0 }}
        </x-report-table.td>

        @endforeach

        <x-report-table.td
            align="center"
            mono>

            {{ number_format($summary['total_count'] ?? 0) }}

        </x-report-table.td>

    </tr>

</x-report-table>