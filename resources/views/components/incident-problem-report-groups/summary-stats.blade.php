@props(['summary' => []])

<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
            Rekomendasi dan Tindak Lanjut Pelaporan Insiden
        </h2>
        <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
            Ringkasan statistik untuk rekomendasi dan tindak lanjut pelaporan insiden yang telah dilakukan. Data ini
            mencakup jumlah laporan yang dibuat, tindakan yg harus dilakukan, serta status tindakan yang telah
            diambil (ditunda atau selesai). Informasi ini dapat membantu dalam memantau efektivitas penanganan insiden
            dan mengidentifikasi area yang memerlukan perhatian lebih lanjut.
        </p>
    </div>

    @if (auth()->user()->can('ViewAllData:LaporanInsiden'))

        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-slate-900">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Unit Kerja</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ number_format($summary['units_count'] ?? 0) }}
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-slate-900">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Laporan</div>
                <div class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">
                    {{ number_format($summary['reports_count'] ?? 0) }}
                </div>
            </div>
            <div
                class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-950/30">
                <div class="text-xs font-medium uppercase tracking-wide text-amber-600 dark:text-amber-300">Tindakan ditunda
                </div>
                <div class="mt-1 text-2xl font-semibold text-amber-700 dark:text-amber-300">
                    {{ number_format($summary['pending_actions_count'] ?? 0) }}
                </div>
            </div>
            <div
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900/40 dark:bg-green-950/30">
                <div class="text-xs font-medium uppercase tracking-wide text-green-600 dark:text-green-300">Tindakan Selesai
                </div>
                <div class="mt-1 text-2xl font-semibold text-green-700 dark:text-green-300">
                    {{ number_format($summary['completed_actions_count'] ?? 0) }}
                </div>
            </div>
        </div>
    @endif
</div>