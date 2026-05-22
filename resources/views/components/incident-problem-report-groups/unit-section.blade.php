@props(['unit' => []])

<section wire:key="unit-{{ $unit['id'] ?? $unit['unit_name'] }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-white/10 dark:bg-slate-900">
    <div class="mb-6 border-b border-slate-200 pb-4 dark:border-white/10">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $unit['unit_name'] }}
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $unit['reports_count'] }} laporan · {{ $unit['problem_count'] }} problem
                </p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $unit['completion_percent'] }}%
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Progress</div>
            </div>
        </div>

        <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
            <div class="h-full rounded-full bg-gradient-to-r from-yellow-500 via-blue-500 to-green-500" style="width: {{ $unit['completion_percent'] }}%"></div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($unit['reports'] as $report)
            <x-incident-problem-report-groups.report-card :report="$report" />
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900 dark:text-slate-400">
                Belum ada laporan untuk unit kerja ini.
            </div>
        @endforelse
    </div>
</section>