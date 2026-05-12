@props([
'colspan' => 1,
'title' => 'Data tidak tersedia',
'description' => null,
])

<tr>
    <td colspan="{{ $colspan }}"
        class="px-6 py-14 text-center">

        <div class="mx-auto flex max-w-sm flex-col items-center">

            <!-- Icon -->
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-2xl shadow-sm dark:bg-gray-800">
                📭
            </div>

            <!-- Content -->
            <div class="mt-4 space-y-1">
                <p class="text-sm font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                    {{ $title }}
                </p>

                @if($description)
                <p class="text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                    {{ $description }}
                </p>
                @endif
            </div>

        </div>

    </td>
</tr>