@props([
'tableClass' => '',
'wrapperClass' => '',
'containerClass' => '',
'scrollClass' => '',
'theadClass' => '',
'tbodyClass' => '',
])

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 {{ $containerClass }} {{ $wrapperClass }}">

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
                class="bg-gray-50 dark:bg-gray-800/50 {{ $theadClass }}">

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