@props(['label' => '', 'value' => '-', 'cols' => 1])

<div class="border border-slate-200 p-2{{ $cols > 1 ? ' col-span-' . $cols : '' }}">
    <p class="report-field-label">{{ $label }}</p>
    <p class="report-field-title">{{ $value }}</p>
</div>