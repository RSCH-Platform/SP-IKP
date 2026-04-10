@props(['label' => '', 'text' => '-'])

<div>
    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-2">{{ $label }}</p>
    <div class="border border-slate-200 p-3 bg-slate-50/50 rounded text-xs text-slate-800 leading-relaxed whitespace-pre-wrap">{{ $text }}</div>
</div>