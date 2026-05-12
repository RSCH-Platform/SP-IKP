<!-- TABLE 1: UNIT KERJA PERFORMANCE -->
<div class="mt-10 space-y-3">
    <div class="border-b border-gray-200 pb-3 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            📊 TABLE 1: Unit Kerja Performance (Monitoring)
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Perbandingan efisiensi penyelesaian insiden per unit kerja
        </p>
    </div>

    <x-report-table>
        <x-slot:colgroup>
            <colgroup>
                <col class="w-2/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-2/12">
            </colgroup>
        </x-slot:colgroup>

        <x-slot:header>
            <tr>
                <x-report-table.th rowspan="2">Unit Kerja</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Total</x-report-table.th>
                <x-report-table.th :colspan="4" align="center">STATUS LAPORAN</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Risk</x-report-table.th>
            </tr>
            <tr>
                <x-report-table.th align="center">Draft</x-report-table.th>
                <x-report-table.th align="center">Proses</x-report-table.th>
                <x-report-table.th align="center">Selesai</x-report-table.th>
                <x-report-table.th align="center">Close%</x-report-table.th>
            </tr>
        </x-slot:header>

        @forelse($this->getTable1UnitPerformance() as $row)
        <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40">
            <x-report-table.td>{{ $row['unit_name'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['total'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['draft'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['proses'] }}</x-report-table.td>
            <x-report-table.td align="center" class="text-green-600 dark:text-green-400 font-semibold">
                {{ $row['selesai'] }}
            </x-report-table.td>
            <x-report-table.td align="center"
                style="background-color: {{ $row['close_rate'] >= 85 ? '#d1fae5' : ($row['close_rate'] >= 70 ? '#fef3c7' : '#fee2e2') }}"
                class="font-semibold">
                {{ $row['close_rate'] }}%
            </x-report-table.td>
            <x-report-table.td align="center" class="font-semibold">{{ $row['risk_level'] }}</x-report-table.td>
        </tr>
        @empty
        <x-report-table.empty colspan="7" title="Data tidak tersedia" description="Belum terdapat data unit kerja pada periode yang dipilih." />
        @endforelse
    </x-report-table>
</div>