<div wire:key="problem-analysis-manager" class="space-y-4">

    @if(config('app.debug'))
    <div
        class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900
           dark:border-amber-700 dark:bg-amber-900/40 dark:text-amber-100"
        style="--debug-max-h: 260px;">
        <div class="font-semibold">DEBUG Problems</div>

        <div class="mt-1">Count: {{ count($problems ?? []) }}</div>
        <div class="mt-1 break-all">Expanded ID: {{ $expandedProblemId ?? 'null' }}</div>

        <details class="mt-2">
            <summary class="cursor-pointer font-medium">Lihat raw data</summary>

            {{-- Problems --}}
            <pre
                class="mt-2 overflow-auto rounded border border-amber-200 bg-white p-2 text-[11px] leading-relaxed text-amber-900
                   dark:border-amber-700 dark:bg-slate-900 dark:text-amber-100"
                style="max-height: var(--debug-max-h);">{{ json_encode($problems ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

            {{-- Timeline --}}
            <pre
                class="mt-2 overflow-auto rounded border border-amber-200 bg-white p-2 text-[11px] leading-relaxed text-amber-900
                   dark:border-amber-700 dark:bg-slate-900 dark:text-amber-100"
                style="max-height: var(--debug-max-h);">{{ json_encode($record?->timelineEntries->toArray() ?? $record?->timelineEvents->toArray() ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            Analisa Masalah (5 WHY)
        </h3>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            Total: {{ count($problems ?? []) }} masalah
        </span>
    </div>

    {{-- Problems List (Accordion) --}}
    <div class="space-y-2">
        @forelse($problems ?? [] as $problem)

        <div class="border border-gray-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 overflow-hidden transition-all duration-300 hover:shadow-lg">

            {{-- Accordion Header --}}
            <div
                wire:click="toggleProblem({{ $problem['id'] }})"
                class="group cursor-pointer flex items-start gap-2
           hover:bg-gray-50 dark:hover:bg-slate-700
           transition-all duration-200">

                {{-- Chevron --}}
                <div class="flex-shrink-0 flex items-center justify-center pt-5 pl-2">
                    <div class="w-7 h-7 flex items-center justify-center rounded-md
                    bg-gray-100 dark:bg-slate-700
                    group-hover:bg-gray-200 dark:group-hover:bg-slate-600
                    transition">

                        <svg
                            class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-300 ease-in-out"
                            style="transform: rotate({{ $expandedProblemId === $problem['id'] ? '90deg' : '0deg' }});"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            {{-- Chevron Right --}}
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>

                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1">
                    @include('livewire.problem-analysis-manager.header', ['problem' => $problem])
                </div>

            </div>

            {{-- Accordion Content with Smooth Animation --}}
            <div class="overflow-hidden transition-all duration-500 ease-out"
                style="max-height: {{ $expandedProblemId === $problem['id'] ? '2000px' : '0px' }}; opacity: {{ $expandedProblemId === $problem['id'] ? '1' : '0' }};">
                <div class="px-6 py-4 space-y-8 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 animate-in fade-in slide-in-from-top-2 duration-300">

                    @include('livewire.problem-analysis-manager.why-section', ['problem' => $problem])

                    @include('livewire.problem-analysis-manager.contributors-section', ['problem' => $problem])

                    @include('livewire.problem-analysis-manager.recommendations-section', ['problem' => $problem])

                    @include('livewire.problem-analysis-manager.actions-section', ['problem' => $problem])

                </div>
            </div>
        </div>

        @empty

        {{-- Empty State --}}
        <div class="p-8 text-center rounded-xl border border-dashed 
                    border-gray-300 dark:border-slate-600 
                    bg-gray-50 dark:bg-slate-800">

            <p class="text-gray-600 dark:text-gray-300 font-medium">
                📭 Tidak ada masalah
            </p>

            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                Data akan muncul setelah mengisi Timeline section
            </p>

        </div>

        @endforelse
    </div>

</div>