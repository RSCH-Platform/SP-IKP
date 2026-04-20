@props(['label' => '', 'text' => '-'])

<div>
    <p class="report-field-label">{{ $label }}</p>
    <div class="border border-slate-200 p-3 bg-slate-50/50 rounded text-xs text-slate-800 leading-relaxed whitespace-pre-wrap text-justify">{{ $text }}</div>
</div>