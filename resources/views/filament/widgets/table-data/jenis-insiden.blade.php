@php
$incidentTypes = $incidentTypes ?? ['KPC', 'KNC', 'KTC', 'KTD', 'Sentinel'];
$rows = $rows ?? [];
$summary = $summary ?? [];
@endphp

<x-report-table>

    <x-slot:colgroup>
        <colgroup>
            <col class="w-2/12">

            @foreach($incidentTypes as $type)
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
                :colspan="count($incidentTypes)"
                align="center">
                Jenis Insiden
            </x-report-table.th>

            <x-report-table.th
                rowspan="2"
                align="center">
                Total Jumlah Insiden
            </x-report-table.th>

        </tr>

        <tr>

            @foreach($incidentTypes as $type)

            <x-report-table.th align="center">
                {{ $type }}
            </x-report-table.th>

            @endforeach

        </tr>

    </x-slot:header>

    @forelse($rows as $row)

    <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40">

        <x-report-table.td>
            {{ $row['month_label'] ?? $row['month'] }}
        </x-report-table.td>

        @foreach($incidentTypes as $type)

        <x-report-table.td align="center">
            {{ $row[$type] ?? 0 }}
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
        :colspan="count($incidentTypes) + 2"
        title="Data laporan tidak tersedia"
        description="Belum terdapat data insiden pada periode yang dipilih." />

    @endforelse

    <!-- Summary Row -->
    <tr class="bg-gray-200 font-semibold dark:bg-gray-900">

        <x-report-table.td>
            TOTAL
        </x-report-table.td>

        @foreach($incidentTypes as $type)

        <x-report-table.td align="center">
            {{ $summary[$type] ?? 0 }}
        </x-report-table.td>

        @endforeach

        <x-report-table.td
            align="center"
            mono>

            {{ number_format($summary['total_count'] ?? 0) }}

        </x-report-table.td>

    </tr>

</x-report-table>