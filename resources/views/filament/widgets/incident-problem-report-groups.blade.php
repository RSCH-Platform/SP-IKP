<x-filament-widgets::widget>
    <div class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-950">
        <x-incident-problem-report-groups.summary-stats :summary="$summary" />

        @forelse ($units as $unit)
            <x-incident-problem-report-groups.unit-section :unit="$unit" />
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center dark:border-white/10 dark:bg-slate-900">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Belum ada laporan untuk ditampilkan</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Setelah laporan insiden masuk dan punya problem, data akan muncul di grup per unit kerja di sini.
                </p>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>