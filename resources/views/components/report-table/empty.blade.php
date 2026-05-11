@props([
'colspan' => 1,
'title' => 'Data tidak tersedia',
'description' => null,
])

<tr>
    <td colspan="{{ $colspan }}"
        class="px-6 py-10 text-center">

        <div class="space-y-1">

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $title }}
            </p>

            @if($description)
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
            @endif

        </div>

    </td>
</tr>