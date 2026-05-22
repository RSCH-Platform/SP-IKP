<x-filament-widgets::widget>
    <div class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-950">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Group Analisis per Laporan
                </h2>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Setiap laporan ditampilkan sebagai satu grup agar status tindakan, rekomendasi, dan bukti tidak tercampur.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-slate-900">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Laporan</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($summary['reports_count'] ?? 0) }}</div>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900/40 dark:bg-emerald-950/30">
                    <div class="text-xs font-medium uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Aksi Selesai</div>
                    <div class="mt-1 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($summary['completed_actions_count'] ?? 0) }}</div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-950/30">
                    <div class="text-xs font-medium uppercase tracking-wide text-amber-600 dark:text-amber-300">Aksi Pending</div>
                    <div class="mt-1 text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ number_format($summary['pending_actions_count'] ?? 0) }}</div>
                </div>
                <div class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 dark:border-cyan-900/40 dark:bg-cyan-950/30">
                    <div class="text-xs font-medium uppercase tracking-wide text-cyan-600 dark:text-cyan-300">Rekomendasi</div>
                    <div class="mt-1 text-2xl font-semibold text-cyan-700 dark:text-cyan-300">{{ number_format($summary['recommendations_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        @forelse ($reports as $report)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-slate-900">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-4 dark:border-white/10 dark:from-slate-900 dark:to-slate-900/60">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
                                    {{ $report['nomor_laporan'] }}
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $report['status_badge_classes'] }}">
                                    {{ $report['status_label'] }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ $report['unit_kerja'] }}
                                </h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $report['jenis_insiden'] }} · {{ $report['tanggal_lapor'] }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:min-w-[420px]">
                            <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70">
                                <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Masalah</div>
                                <div class="mt-1 text-base font-semibold text-slate-900 dark:text-white">{{ number_format($report['problem_count']) }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70">
                                <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Aksi</div>
                                <div class="mt-1 text-base font-semibold text-slate-900 dark:text-white">{{ number_format($report['actions_count']) }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70">
                                <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Selesai</div>
                                <div class="mt-1 text-base font-semibold text-emerald-600 dark:text-emerald-300">{{ number_format($report['completed_actions_count']) }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/70">
                                <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Progress</div>
                                <div class="mt-1 text-base font-semibold text-cyan-600 dark:text-cyan-300">{{ $report['completion_percent'] }}%</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 via-blue-500 to-emerald-500" style="width: {{ $report['completion_percent'] }}%"></div>
                    </div>
                </div>

                <div class="space-y-4 px-5 py-5">
                    @forelse ($report['problems'] as $problem)
                        <details class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 open:bg-white dark:border-white/10 dark:bg-slate-950 dark:open:bg-slate-900">
                            <summary class="cursor-pointer list-none">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                                {{ $problem['problem_type'] }}
                                            </span>
                                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $problem['whys_count'] }} WHY
                                            </span>
                                            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">
                                                {{ $problem['recommendations_count'] }} Rekom
                                            </span>
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                {{ $problem['completed_actions_count'] }}/{{ $problem['actions_count'] }} Aksi
                                            </span>
                                        </div>

                                        <p class="max-w-4xl text-sm leading-6 text-slate-700 dark:text-slate-200">
                                            {{ $problem['problem_description'] }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        <span class="rounded-full bg-slate-200 px-3 py-1 dark:bg-slate-800">{{ $problem['pending_actions_count'] }} belum</span>
                                        <span class="rounded-full bg-slate-200 px-3 py-1 dark:bg-slate-800">{{ $problem['ongoing_actions_count'] }} berjalan</span>
                                    </div>
                                </div>
                            </summary>

                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div class="rounded-xl border border-dashed border-cyan-200 bg-cyan-50/70 p-4 dark:border-cyan-900/40 dark:bg-cyan-950/20">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-cyan-900 dark:text-cyan-200">Rekomendasi</h4>
                                        <span class="text-xs font-semibold text-cyan-700 dark:text-cyan-300">{{ $problem['recommendations_count'] }}</span>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse ($problem['recommendations'] as $recommendation)
                                            <div class="rounded-lg border border-cyan-100 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm dark:border-cyan-900/30 dark:bg-slate-900 dark:text-slate-200">
                                                <div class="mb-1 flex items-center gap-2">
                                                    <span class="rounded-full bg-cyan-100 px-2 py-0.5 text-[11px] font-semibold text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300">
                                                        {{ strtoupper($recommendation['priority']) }}
                                                    </span>
                                                </div>
                                                {{ $recommendation['text'] }}
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-dashed border-cyan-200 bg-white px-3 py-2 text-sm text-slate-500 dark:border-cyan-900/40 dark:bg-slate-900 dark:text-slate-400">
                                                Belum ada rekomendasi untuk problem ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                                    <div class="mb-3 flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">Tindakan</h4>
                                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $problem['actions_count'] }}</span>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse ($problem['actions'] as $action)
                                            <div class="rounded-lg border border-emerald-100 bg-white px-3 py-2 shadow-sm dark:border-emerald-900/30 dark:bg-slate-900">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                                                            {{ $action['text'] }}
                                                        </p>
                                                        <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                                                            <span>{{ $action['responsible_person'] ?: '-' }}</span>
                                                            <span>•</span>
                                                            <span>{{ $action['deadline'] ?: '-' }}</span>
                                                            <span>•</span>
                                                            <span>{{ $action['media_count'] }} bukti</span>
                                                        </div>
                                                    </div>

                                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $action['status_badge_classes'] }}">
                                                        {{ $action['status_label'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-dashed border-emerald-200 bg-white px-3 py-2 text-sm text-slate-500 dark:border-emerald-900/40 dark:bg-slate-900 dark:text-slate-400">
                                                Belum ada tindakan untuk problem ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900 dark:text-slate-400">
                            Laporan ini belum punya analisis problem.
                        </div>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center dark:border-white/10 dark:bg-slate-900">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Belum ada laporan untuk ditampilkan</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Setelah laporan insiden masuk dan punya problem, data akan muncul di grup per laporan di sini.
                </p>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>