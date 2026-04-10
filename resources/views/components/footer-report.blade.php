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
$headOfUnit = \App\Models\User::whereHas('unitKerja', function ($query) use ($unitId) {
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

<div class="break-inside-avoid">
    <h2 class="text-base font-semibold text-slate-800 border-b border-slate-200 pb-2 mb-8">
        Tanda Tangan dan Persetujuan
    </h2>

    <div class="grid md:grid-cols-2 gap-16 text-sm text-slate-800">

        <!-- Pembuat -->
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-6">
                Pembuat Laporan
            </p>

            <div class="space-y-1">
                <p class="font-medium text-slate-900">{{ $createdByName }}</p>
                <p class="text-slate-500">NIP. {{ $createdByNip }}</p>
                <p class="text-slate-500">{{ $createdByPosition }}</p>
            </div>

            <div class="mt-12">
                <div class="border-t border-slate-400 w-44"></div>
                <p class="text-xs text-slate-500 mt-2">Tanda Tangan</p>
            </div>

            <p class="text-xs text-slate-500 mt-4">
                {{ $reportDate ?? now()->translatedFormat('d F Y') }}
            </p>
        </div>

        <!-- Penerima -->
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500 mb-6">
                Penerima Laporan
            </p>

            <div class="space-y-1">
                <p class="font-medium text-slate-900">{{ $receivedByName }}</p>
                <p class="text-slate-500">NIP. {{ $receivedByNip }}</p>
                <p class="text-slate-500">{{ $receivedByPosition }}</p>
            </div>

            <div class="mt-12">
                <div class="border-t border-slate-400 w-44"></div>
                <p class="text-xs text-slate-500 mt-2">Tanda Tangan</p>
            </div>

            <p class="text-xs text-slate-500 mt-4">
                {{ $receivedDate ?? '.........................' }}
            </p>
        </div>
    </div>

    <!-- Notes -->
    @if(count($notes) > 0)
    <div class="mt-10 text-xs text-slate-500">
        <p class="font-medium text-slate-600 mb-2">Catatan:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($notes as $note)
            <li>{{ $note }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>