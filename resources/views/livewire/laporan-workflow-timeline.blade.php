<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">

    {{-- header --}}
    <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-4 dark:border-gray-700 dark:from-gray-800">
        <h3 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
            <x-heroicon-o-clock class="h-5 w-5" />
            Workflow Progress
        </h3>
    </div>

    <div class="p-8">

        <div class="relative">

            {{-- timeline line --}}
            <div class="absolute left-5 top-0 h-full w-px bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-10">

                @foreach($steps as $step)
                @if($step['show'])

                <div class="relative flex items-start gap-6">

                    {{-- node icon --}}
                    <div
                        class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-4 border-white dark:border-gray-900 @if($this->stepStatus($step['key']) === 'done') bg-green-500 text-white @elseif($this->stepStatus($step['key']) === 'current') bg-blue-500 text-white animate-pulse @else bg-gray-300 text-gray-600 dark:bg-gray-600 @endif">
                        @if($this->stepStatus($step['key']) === 'done')
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        @else
                        <x-dynamic-component :component="$step['icon']" class="h-5 w-5" />
                        @endif
                    </div>

                    {{-- card --}}
                    <div class="relative flex-1 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">

                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $step['title'] }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $step['desc'] }}
                                </div>
                            </div>

                            @if($this->stepStatus($step['key']) === 'done')
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400 whitespace-nowrap">
                                selesai
                            </span>
                            @elseif($this->stepStatus($step['key']) === 'current')
                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 whitespace-nowrap">
                                proses
                            </span>
                            @endif
                        </div>

                        <div class="mt-4 space-y-2 text-sm">
                            @foreach($step['data'] as $label => $value)
                            @if($value)
                            <div class="flex justify-between text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ $label }}:</span>
                                <span class="text-right">{{ $value }}</span>
                            </div>
                            @endif
                            @endforeach
                        </div>

                    </div>

                </div>

                @endif
                @endforeach

            </div>

        </div>

    </div>

</div>