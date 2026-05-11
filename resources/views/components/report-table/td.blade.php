@props([
'align' => 'left',
'mono' => false,
])

@php
$alignClass = match($align) {
'center' => 'text-center',
'right' => 'text-right',
default => 'text-left',
};

$monoClass = $mono
? 'font-mono tabular-nums font-semibold'
: '';
@endphp

<td
    {{ $attributes->merge([
        'class' => "
            px-5 py-3
            text-sm
            text-gray-700
            dark:text-gray-200
            border border-gray-300
            dark:border-gray-700
            {$alignClass}
            {$monoClass}
        "
    ]) }}>
    {{ $slot }}
</td>