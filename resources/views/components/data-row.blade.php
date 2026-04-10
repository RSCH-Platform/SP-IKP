@props(['label' => '', 'value' => '-', 'cols' => 1])

<div class="border border-slate-200 p-2{{ $cols > 1 ? ' col-span-' . $cols : '' }}">
    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">{{ $label }}</p>
    <p class="text-xs text-slate-800">{{ $value }}</p>
</div>