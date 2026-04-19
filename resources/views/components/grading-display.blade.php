@props(['grade' => null, 'justification' => null, 'editable' => false, 'disabled' => false])

@php
use App\Models\LaporanInsiden;

$gradingOptions = array_values(LaporanInsiden::GRADING_RISIKO_OPTIONS);
$gradingColors = LaporanInsiden::GRADING_RISIKO_COLORS;

$normalizedGrade = null;
if (! blank($grade)) {
$normalizedGrade = trim($grade);
$normalizedGrade = mb_convert_case(strtolower($normalizedGrade), MB_CASE_TITLE, 'UTF-8');
}
@endphp

<div class="mb-2">
    <p class="report-field-title">Grading Risiko</p>
    <div class="grid grid-cols-4 gap-2">
        @foreach($gradingOptions as $option)
        @php
        $isSelected = $normalizedGrade === $option;
        $colors = $gradingColors[$option];
        $styleClass = $isSelected ? $colors['bg'] : 'border ' . $colors['border'] . ' bg-white text-slate-800';
        @endphp
        <div>
            <div class="flex items-center justify-center p-2 rounded text-xs font-semibold uppercase tracking-wide {{ $styleClass }} title='{{ $colors['desc'] }}'">
                {{ $option }}
            </div>
            <p class="text-xs text-slate-600 text-center mt-1 leading-tight">{{ $colors['desc'] }}</p>
        </div>
        @endforeach
    </div>
</div>
<div class="border border-slate-200 p-2">
    <p class="report-field-title">Justifikasi Grading</p>
    <div class="text-xs text-slate-800 whitespace-pre-wrap bg-slate-50 p-2 rounded">{{ $justification ?? 'Belum ada justifikasi grading' }}</div>
</div>