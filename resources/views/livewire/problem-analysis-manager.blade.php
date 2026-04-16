<div wire:key="problem-analysis-manager" class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            🧠 Analisa Masalah (5 WHY)
        </h3>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            Total: {{ count($problems ?? []) }} masalah
        </span>
    </div>

    {{-- Problems List --}}
    <div class="space-y-6">
        @forelse($problems ?? [] as $problem)

        <div class="border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-800">

            {{-- Header (sudah kamu handle sendiri) --}}
            @include('livewire.problem-analysis-manager.header', ['problem' => $problem])

            {{-- Content --}}
            <div class="px-6 py-4 space-y-8 bg-white dark:bg-slate-800">

                @include('livewire.problem-analysis-manager.why-section', ['problem' => $problem])

                @include('livewire.problem-analysis-manager.contributors-section', ['problem' => $problem])

                @include('livewire.problem-analysis-manager.recommendations-section', ['problem' => $problem])

                @include('livewire.problem-analysis-manager.actions-section', ['problem' => $problem])

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