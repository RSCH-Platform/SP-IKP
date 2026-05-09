@props([
'tableClass' => '',
'wrapperClass' => '',
'containerClass' => 'overflow-hidden rounded-lg border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900',
'scrollClass' => 'overflow-x-auto',
'theadClass' => 'bg-gray-100 dark:bg-gray-800',
'tbodyClass' => '',
])

<div class="{{ $containerClass }} {{ $wrapperClass }}">
    <div class="{{ $scrollClass }}">
        <table {{ $attributes->merge(['class' => 'w-full border-collapse table-fixed ' . $tableClass]) }}>

            @isset($colgroup)
            {{ $colgroup }}
            @endisset

            @isset($header)
            <thead class="{{ $theadClass }}">
                {{ $header }}
            </thead>
            @endisset

            <tbody class="{{ $tbodyClass }}">
                {{ $slot }}
            </tbody>

        </table>
    </div>
</div>