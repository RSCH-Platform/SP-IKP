<!-- TABLE 1: UNIT KERJA PERFORMANCE -->
<div class="mt-10 space-y-3">
    <div class="border-b border-gray-200 pb-3 dark:border-gray-700">

        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            📊 Kinerja Penanganan Insiden per Unit Kerja
        </h3>

        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Menampilkan perbandingan jumlah laporan, status penanganan,
            dan efektivitas tindak lanjut insiden pada setiap unit kerja.
        </p>

    </div>

    <x-report-table>
        @php
        $statuses = $this->statuses ?? [];
        $colspan = 2 + count($statuses) + 1; // Unit, Total, statuses..., Close%
        @endphp

        <x-slot:colgroup>
            <colgroup>
                <col class="w-2/12">
                <col class="w-1/12">
                @foreach($statuses as $k => $label)
                <col class="w-1/12">
                @endforeach
                <col class="w-1/12">
            </colgroup>
        </x-slot:colgroup>

        <x-slot:header>
            <tr>
                <x-report-table.th rowspan="2">Unit Kerja</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Total</x-report-table.th>
                <x-report-table.th :colspan="count($statuses) + 1" align="center">STATUS LAPORAN</x-report-table.th>
            </tr>
            <tr>
                @foreach($statuses as $k => $label)
                <x-report-table.th align="center">{{ $label }}</x-report-table.th>
                @endforeach
                <x-report-table.th align="center">Close%</x-report-table.th>
            </tr>
        </x-slot:header>

        @forelse($this->getTable1UnitPerformance() as $row)
        <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40">
            <x-report-table.td>{{ $row['unit_name'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['total'] }}</x-report-table.td>
            @foreach($statuses as $k => $label)
            <x-report-table.td align="center">{{ $row[$k] ?? 0 }}</x-report-table.td>
            @endforeach
            <x-report-table.td align="center"
                style="background-color: {{ ($row['close_rate'] ?? 0) >= 85 ? '#d1fae5' : (($row['close_rate'] ?? 0) >= 70 ? '#fef3c7' : '#fee2e2') }}"
                class="font-semibold">
                {{ $row['close_rate'] ?? 0 }}%
            </x-report-table.td>
        </tr>
        @empty
        <x-report-table.empty :colspan="$colspan" title="Data tidak tersedia" description="Belum terdapat data unit kerja pada periode yang dipilih." />
        @endforelse
    </x-report-table>
</div>