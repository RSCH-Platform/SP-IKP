@props([
'previousIncident' => '',
'description' => '-',
'label' => 'Kejadian Sebelumnya',
])

<div class="border border-slate-200 p-2">
    <p class="report-field-title">{{ $label }}</p>
    <div class="grid grid-cols-2 gap-2">
        <x-checkbox-display :checked="$previousIncident === 'Ya'" label="Ya" disabled />
        <x-checkbox-display :checked="$previousIncident === 'Tidak'" label="Tidak" disabled />
    </div>

    @php
    $descriptionText = trim((string) $description);
    @endphp

    @if($previousIncident === 'Ya' && $descriptionText !== '')
    <div class="mt-3 p-2 bg-slate-50 border border-slate-200 rounded">
        <p class="report-field-title">Deskripsi Kejadian Sebelumnya</p>
        <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $descriptionText }}</p>
    </div>
    @endif
</div>