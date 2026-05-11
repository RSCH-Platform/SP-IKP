@props([
'align' => 'left',
])

@php
$alignClass = match($align) {
'center' => 'text-center',
'right' => 'text-right',
default => 'text-left',
};
@endphp

<th
    {{ $attributes->merge([
        'class' => "
            border-b border-gray-200
            px-5 py-3
            text-[11px]
            font-semibold
            uppercase
            tracking-[0.08em]
            text-gray-600
            dark:border-gray-700
            dark:text-gray-300
            border border-gray-300
            dark:border-gray-700
            dark:bg-gray-900
            {$alignClass}
        "
    ]) }}>
    {{ $slot }}
</th>