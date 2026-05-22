@props(['report' => []])

<article wire:key="report-{{ $report['id'] }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-slate-900">
    <div class="border-b border-slate-100 bg-white px-5 py-4 dark:border-white/10 dark:bg-slate-950">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600 dark:bg-white dark:text-slate-900">
                        {{ $report['nomor_laporan'] }}
                    </span>
                </div>

                <div>
                    <h4 class="text-sm uppercase font-semibold text-slate-900 dark:text-white">
                        {{ $report['jenis_insiden'] }}
                    </h4>

                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $report['deskripsi_kategori_insiden'] }} · {{ $report['tanggal_lapor'] }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                <div class="h-full rounded-full bg-gradient-to-r from-yellow-500 via-blue-500 to-green-500 transition-all duration-300" style="width: {{ $report['completion_percent'] }}%"></div>
            </div>

            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ $report['completion_percent'] }}% completed
            </div>
        </div>
    </div>

    <div class="space-y-4 px-5 py-5">
        @forelse ($report['problems'] as $problem)
            <x-incident-problem-report-groups.problem-card :problem="$problem" />
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900 dark:text-slate-400">
                Laporan ini belum punya analisis problem.
            </div>
        @endforelse
    </div>
</article>