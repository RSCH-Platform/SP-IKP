@props(['grade' => null, 'justification' => null])

@php
$gradingOptions = ['Biru', 'Hijau', 'Kuning', 'Merah'];
$gradingColors = [
'Biru' => ['bg' => 'bg-blue-500 text-white', 'border' => 'border-blue-500', 'desc' => 'Tidak ada dampak/Risiko rendah'],
'Hijau' => ['bg' => 'bg-green-500 text-white', 'border' => 'border-green-500', 'desc' => 'Dampak minimal/Risiko rendah'],
'Kuning' => ['bg' => 'bg-amber-500 text-white', 'border' => 'border-amber-500', 'desc' => 'Dampak sedang/Risiko menengah'],
'Merah' => ['bg' => 'bg-red-500 text-white', 'border' => 'border-red-500', 'desc' => 'Dampak berat/Risiko tinggi']
];
@endphp

<div class="mb-2">
    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-2">Grading Risiko</p>
    <div class="grid grid-cols-4 gap-2">
        @foreach($gradingOptions as $option)
        @php
        $isSelected = $grade === $option;
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
    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Justifikasi Grading</p>
    <div class="text-xs text-slate-800 whitespace-pre-wrap bg-slate-50 p-2 rounded">{{ $justification ?? 'Belum ada justifikasi grading' }}</div>
</div>