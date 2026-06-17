@props([
'tableClass' => '',
'wrapperClass' => '',
'containerClass' => '',
'scrollClass' => '',
'theadClass' => '',
'tbodyClass' => '',
])

<div
    class="overflow-hidden bg-transparent {{ $containerClass }} {{ $wrapperClass }}">

    <div class="overflow-x-auto {{ $scrollClass }}">

        <table
            {{ $attributes->merge([
                'class' => 'w-full border-collapse table-fixed text-sm ' . $tableClass
            ]) }}>

            @isset($colgroup)
            {{ $colgroup }}
            @endisset

            @isset($header)
            <thead
                class="{{ $theadClass }}">

                {{ $header }}

            </thead>
            @endisset

            <tbody
                class="divide-y divide-gray-200 dark:divide-gray-800 {{ $tbodyClass }}">

                {{ $slot }}

            </tbody>

        </table>

    </div>

</div>