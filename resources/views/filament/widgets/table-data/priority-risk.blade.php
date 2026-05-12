<!-- TABLE 4: PRIORITY RISK & ESCALATION -->
<div class="mt-8 space-y-3">
    <div class="border-b border-gray-200 pb-3 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            🚨 TABLE 4: Priority Risk & Escalation (Decision & Escalation)
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Ranking unit berdasarkan risk score dan rekomendasi tindakan manager
        </p>
    </div>

    <x-report-table>
        <x-slot:colgroup>
            <colgroup>
                <col class="w-1/12">
                <col class="w-3/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
                <col class="w-1/12">
            </colgroup>
        </x-slot:colgroup>

        <x-slot:header>
            <tr>
                <x-report-table.th rowspan="2" align="center">Rank</x-report-table.th>
                <x-report-table.th rowspan="2">Unit Kerja</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Risk Score</x-report-table.th>
                <x-report-table.th align="center" :colspan="2">Jenis insiden</x-report-table.th>
                <x-report-table.th align="center" :colspan="2">grading</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Overdue</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Avg Resolve</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Close%</x-report-table.th>
                <x-report-table.th rowspan="2" align="center">Action</x-report-table.th>
            </tr>

            <tr>
                <x-report-table.th align="center">Sentinel</x-report-table.th>
                <x-report-table.th align="center">KTD</x-report-table.th>
                <x-report-table.th align="center">Merah</x-report-table.th>
                <x-report-table.th align="center">Dampak Berat</x-report-table.th>
            </tr>
        </x-slot:header>

        @forelse($this->getTable4PriorityRisk() as $row)
        <tr class="hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-800/40"
            style="background-color: {{ str_contains($row['risk_level'], 'Critical') ? '#fee2e2' : (str_contains($row['risk_level'], 'High') ? '#fef3c7' : 'transparent') }}">
            <x-report-table.td align="center" class="font-bold text-lg">{{ $row['rank'] }}</x-report-table.td>
            <x-report-table.td class="font-medium">{{ $row['unit_name'] }}</x-report-table.td>
            <x-report-table.td align="center" class="font-bold">{{ $row['risk_score'] }}</x-report-table.td>
            <x-report-table.td align="center" class="font-semibold">🔴 {{ $row['sentinel'] }}</x-report-table.td>
            <x-report-table.td align="center" class="font-semibold">⚠️ {{ $row['ktd'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['merah'] }}</x-report-table.td>
            <x-report-table.td align="center" class="font-semibold text-red-700 dark:text-red-400">⚫ {{ $row['severe_impact'] }}</x-report-table.td>
            <x-report-table.td align="center" class="font-semibold">⏰ {{ $row['overdue'] }}</x-report-table.td>
            <x-report-table.td align="center">{{ $row['avg_resolve_days'] }} hari</x-report-table.td>
            <x-report-table.td align="center"
                style="background-color: {{ $row['close_rate'] >= 85 ? '#d1fae5' : ($row['close_rate'] >= 70 ? '#fef3c7' : '#fee2e2') }}"
                class="font-semibold">
                {{ $row['close_rate'] }}%
            </x-report-table.td>
            <x-report-table.td align="center" class="font-semibold">{{ $row['action'] }}</x-report-table.td>
        </tr>
        @empty
        <x-report-table.empty colspan="11" title="Data tidak tersedia" description="Belum terdapat data unit kerja untuk analisis risiko." />
        @endforelse
    </x-report-table>
</div>