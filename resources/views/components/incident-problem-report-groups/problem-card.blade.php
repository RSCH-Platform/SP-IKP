@props(['problem' => []])

<details wire:key="problem-{{ $problem['id'] }}" class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition open:bg-white dark:border-white/10 dark:bg-slate-950 dark:open:bg-slate-900">
    <summary class="cursor-pointer list-none">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold uppercase text-slate-800 dark:text-slate-100">
                        {{ $problem['problem_type_label'] ?? $problem['problem_type'] ?? '-' }}
                    </span>

                    @if (! empty($problem['problem_type_caption']))
                        <span class="text-xs text-slate-400 dark:text-slate-500">
                            ({{ $problem['problem_type_caption'] }})
                        </span>
                    @endif
                </div>

                <p class="line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    {{ $problem['problem_description'] }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300">
                    {{ $problem['recommendations_count'] }} Rekom
                </span>

                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-300">
                    {{ $problem['completed_actions_count'] }}/{{ $problem['actions_count'] }} Aksi
                </span>
            </div>
        </div>
    </summary>

    <div class="mt-5 space-y-5">
        <div>
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Rekomendasi</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Insight awal dari hasil analisis problem.</p>
                </div>

                <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300">
                    {{ $problem['recommendations_count'] }}
                </span>
            </div>

            <div class="grid gap-3 lg:grid-cols-2">
                @forelse ($problem['recommendations'] as $recommendation)
                    <x-incident-problem-report-groups.recommendation-card :recommendation="$recommendation" />
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900 dark:text-slate-400">
                        Belum ada rekomendasi untuk problem ini.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Timeline Tindakan</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Progress tindak lanjut berdasarkan rekomendasi.</p>
                </div>

                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-300">
                    {{ $problem['actions_count'] }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse ($problem['actions'] as $action)
                    <x-incident-problem-report-groups.action-item :action="$action" :is-last="$loop->last" />
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-slate-950 dark:text-slate-400">
                        Belum ada tindakan untuk problem ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</details>