@props(['label' => '', 'text' => '-'])

<div class="border border-slate-200 p-2">
    <p class="report-field-label">{{ $label }}</p>
    <div class="text-xs text-slate-800 whitespace-pre-wrap bg-slate-50 p-2 rounded">{{ $text }}</div>
</div>