@props(['label' => '', 'text' => '-'])

<div class="border border-slate-200 p-2">
    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">{{ $label }}</p>
    <div class="text-xs text-slate-800 whitespace-pre-wrap bg-slate-50 p-2 rounded">{{ $text }}</div>
</div>