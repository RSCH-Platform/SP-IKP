@props([
'createdByName' => 'Nama Pembuat Laporan',
'createdByNip' => '-',
'createdByPosition' => 'Posisi/Jabatan',
'receivedByName' => null,
'receivedByNip' => null,
'receivedByPosition' => null,
'unitId' => null,
'reportDate' => null,
'receivedDate' => null,
'notes' => []
])

@php
// Auto-query kepala unit jika tidak disediakan manual
if (!$receivedByName && $unitId) {
$headOfUnit = \App\Models\User::whereHas('unitKerjas', function ($query) use ($unitId) {
$query->where('unit_kerja_id', $unitId);
})
->whereHas('roles', function ($q) {
$q->where('name', 'kepala_unit');
})
->first();

if ($headOfUnit) {
$receivedByName = $headOfUnit->name ?? 'Kepala Unit';
$receivedByNip = $headOfUnit->nip ?? '-';
$receivedByPosition = 'Kepala Unit';
}
}

// Fallback values
$receivedByName = $receivedByName ?? 'Kepala Unit';
$receivedByNip = $receivedByNip ?? '-';
$receivedByPosition = $receivedByPosition ?? 'Kepala Unit';
@endphp

<div class="break-inside-avoid print:block break-inside-avoid print:break-inside-avoid">
    <x-section-header title="BAGIAN F: Tanda Tangan dan Persetujuan" />

    <div class="bg-white border border-slate-300 p-2 print:block break-inside-avoid print:break-inside-avoid">
        <div class="grid grid-cols-2 gap-2 mb-3">
            <!-- Pembuat Laporan -->
            <div class="border border-slate-200 p-2">
                <p class="report-field-label">Pembuat Laporan</p>
                <div class="space-y-0.5 mb-4">
                    <p class="text-xs font-medium text-slate-800">{{ $createdByName }}</p>
                    <p class="text-xs text-slate-600">NIP: {{ $createdByNip }}</p>
                </div>
                <div class="mt-6 pt-2 border-t border-slate-300">
                    <p class="text-xs text-slate-600 mt-1">
                        {{ $reportDate ?? now()->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            <!-- Penerima Laporan -->
            <div class="border border-slate-200 p-2">
                <p class="report-field-label">Penerima Laporan</p>
                <div class="space-y-0.5 mb-4">
                    <p class="text-xs font-medium text-slate-800">{{ $receivedByName }}</p>
                    <p class="text-xs text-slate-600">NIP: {{ $receivedByNip }}</p>
                </div>
                <div class="mt-6 pt-2 border-t border-slate-300">
                    <p class="text-xs text-slate-600 mt-1">
                        {{ $receivedDate ?? '.........................' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if(count($notes) > 0)
        <div class="border border-slate-200 p-2 mt-2">
            <p class="report-field-label">Catatan Penting</p>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($notes as $note)
                <li class="text-xs text-slate-700">{{ $note }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>