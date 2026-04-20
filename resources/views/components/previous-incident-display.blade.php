@props([
'previousIncident' => '',
'description' => '-',
'label' => 'Kejadian Sebelumnya',
])

<div class="border border-slate-200 p-2">
    <p class="report-field-label">{{ $label }}</p>
    <div class="mt-2 mb-4 grid grid-cols-2 gap-2">
        <x-checkbox-display :checked="$previousIncident === 'Ya'" label="Ya" disabled />
        <x-checkbox-display :checked="$previousIncident === 'Tidak'" label="Tidak" disabled />
    </div>

    @php
    $descriptionText = trim((string) $description);
    @endphp

    @if($previousIncident === 'Ya' && $descriptionText !== '')
    <x-long-text-display label="Deskripsi Kejadian Sebelumnya" :text="$descriptionText" />
    @endif
</div>