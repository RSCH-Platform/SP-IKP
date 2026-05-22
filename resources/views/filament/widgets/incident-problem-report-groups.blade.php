<x-filament-widgets::widget>
    <div
        class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-950">
        <!-- SUMMARY STATS -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Group Analisis per Laporan
                </h2>
                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Setiap laporan ditampilkan sebagai satu grup agar status tindakan, rekomendasi, dan bukti tidak
                    tercampur.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-slate-900">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Unit
                        Kerja</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ number_format($summary['units_count'] ?? 0) }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-slate-900">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Laporan
                    </div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ number_format($summary['reports_count'] ?? 0) }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900/40 dark:bg-green-950/30">
                    <div class="text-xs font-medium uppercase tracking-wide text-green-600 dark:text-green-300">Aksi
                        Selesai</div>
                    <div class="mt-1 text-2xl font-semibold text-green-700 dark:text-green-300">
                        {{ number_format($summary['completed_actions_count'] ?? 0) }}
                    </div>
                </div>
                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/40 dark:bg-amber-950/30">
                    <div class="text-xs font-medium uppercase tracking-wide text-amber-600 dark:text-amber-300">Aksi
                        Pending</div>
                    <div class="mt-1 text-2xl font-semibold text-amber-700 dark:text-amber-300">
                        {{ number_format($summary['pending_actions_count'] ?? 0) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- UNITS GROUP -->
        @forelse ($units as $unit)
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-white/10 dark:bg-slate-900">
                <!-- UNIT HEADER -->
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

                    <!-- UNIT PROGRESS -->
                    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-yellow-500 via-blue-500 to-green-500"
                            style="width: {{ $unit['completion_percent'] }}%"></div>
                    </div>
                </div>

                <!-- REPORTS IN UNIT -->
                <div class="space-y-4">
                    @forelse ($unit['reports'] as $report)
                        <article
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-slate-900">
                            <div class="border-b border-slate-100 bg-white px-5 py-4 dark:border-white/10 dark:bg-slate-950">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                    <!-- LEFT CONTENT -->
                                    <div class="space-y-2">

                                        <!-- REPORT NUMBER & STATUS -->
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="text-xs font-semibold text-slate-600 dark:bg-white dark:text-slate-900">
                                                {{ $report['nomor_laporan'] }}
                                            </span>
                                        </div>

                                        <!-- TITLE -->
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

                                <!-- REPORT PROGRESS -->
                                <div class="mt-4">
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                        <div class="h-full rounded-full bg-gradient-to-r from-yellow-500 via-blue-500 to-green-500 transition-all duration-300"
                                            style="width: {{ $report['completion_percent'] }}%"></div>
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $report['completion_percent'] }}% completed
                                    </div>
                                </div>
                            </div>

                            <!-- PROBLEMS IN REPORT -->
                    <div class="space-y-4 px-5 py-5">
                        @forelse ($report['problems'] as $problem)
                            <details
                                class="group rounded-2xl border border-slate-200 bg-slate-50 p-4 transition open:bg-white dark:border-white/10 dark:bg-slate-950 dark:open:bg-slate-900"
                            >
                                <summary class="cursor-pointer list-none">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold uppercase text-slate-800 dark:text-slate-100">
                                                    @if($problem['problem_type'] === 'CMP')
                                                        CMP
                                                    @elseif($problem['problem_type'] === 'SDP')
                                                        SDP
                                                    @else
                                                        {{ $problem['problem_type'] }}
                                                    @endif
                                                </span>

                                                <span class="text-xs text-slate-400 dark:text-slate-500">
                                                    @if($problem['problem_type'] === 'CMP')
                                                        (Clinical Management Problem)
                                                    @elseif($problem['problem_type'] === 'SDP')
                                                        (System Development Problem)
                                                    @endif
                                                </span>
                                            </div>

                                            <p class="text-sm leading-6 text-slate-600 line-clamp-2 dark:text-slate-300">
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

                                    {{-- REKOMENDASI --}}
                                    <div>
                                        <div class="mb-3 flex items-center justify-between gap-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">
                                                    Rekomendasi
                                                </h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    Insight awal dari hasil analisis problem.
                                                </p>
                                            </div>

                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300">
                                                {{ $problem['recommendations_count'] }}
                                            </span>
                                        </div>

                                        <div class="grid gap-3 lg:grid-cols-2">
                                            @forelse ($problem['recommendations'] as $recommendation)
                                                <div
                                                    @class([
                                                        'rounded-xl border bg-white p-4 shadow-sm transition hover:shadow-md dark:bg-slate-900',
                                                        'border-l-4 border-l-yellow-400 border-slate-200 dark:border-slate-800' => $recommendation['priority'] === 'high',
                                                        'border-l-4 border-l-blue-400 border-slate-200 dark:border-slate-800' => $recommendation['priority'] === 'medium',
                                                        'border-l-4 border-l-slate-300 border-slate-200 dark:border-slate-800' => ! in_array($recommendation['priority'], ['high', 'medium']),
                                                    ])
                                                >
                                                    <div class="mb-2">
                                                        <span
                                                            @class([
                                                                'rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase',
                                                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300' => $recommendation['priority'] === 'high',
                                                                'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' => $recommendation['priority'] === 'medium',
                                                                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! in_array($recommendation['priority'], ['high', 'medium']),
                                                            ])
                                                        >
                                                            {{ strtoupper($recommendation['priority']) }}
                                                        </span>
                                                    </div>

                                                    <p class="text-sm leading-6 text-slate-700 dark:text-slate-200">
                                                        {{ $recommendation['text'] }}
                                                    </p>
                                                </div>
                                            @empty
                                                <div class="rounded-xl border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900 dark:text-slate-400">
                                                    Belum ada rekomendasi untuk problem ini.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- TINDAKAN --}}
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-slate-900">
                                        <div class="mb-4 flex items-center justify-between gap-3">
                                            <div>
                                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">
                                                    Timeline Tindakan
                                                </h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    Progress tindak lanjut berdasarkan rekomendasi.
                                                </p>
                                            </div>

                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-300">
                                                {{ $problem['actions_count'] }}
                                            </span>
                                        </div>

                                        <div class="space-y-3">
                                        @forelse ($problem['actions'] as $action)
                                            @php
                                                $status = $action['status'];

                                                $statusStyles = [
                                                    'pending' => [
                                                        'label' => 'Pending',
                                                        'dot' => 'bg-yellow-500',
                                                        'active' => 'text-yellow-700 dark:text-yellow-300',
                                                    ],
                                                    'ongoing' => [
                                                        'label' => 'Ongoing',
                                                        'dot' => 'bg-blue-500',
                                                        'active' => 'text-blue-700 dark:text-blue-300',
                                                    ],
                                                    'completed' => [
                                                        'label' => 'Selesai',
                                                        'dot' => 'bg-green-500',
                                                        'active' => 'text-green-700 dark:text-green-300',
                                                    ],
                                                ];

                                                $currentStatus = $statusStyles[$status] ?? $statusStyles['pending'];
                                            @endphp

                                            <div class="relative flex gap-4">
                                                {{-- Timeline indicator --}}
                                                <div class="flex flex-col items-center">
                                                    <span @class([
                                                        'mt-4 h-3 w-3 rounded-full ring-4',
                                                        'bg-yellow-500 ring-yellow-100 dark:ring-yellow-950/40' => $status === 'pending',
                                                        'bg-blue-500 ring-blue-100 dark:ring-blue-950/40' => $status === 'ongoing',
                                                        'bg-green-500 ring-green-100 dark:ring-green-950/40' => $status === 'completed',
                                                    ])></span>

                                                    @if (! $loop->last)
                                                        <span class="mt-2 h-full w-px bg-slate-200 dark:bg-white/10"></span>
                                                    @endif
                                                </div>

                                                {{-- Action card --}}
                                                <div class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:bg-white hover:shadow-sm dark:border-white/10 dark:bg-slate-950 dark:hover:bg-slate-900">
                                                    <div class="flex justify-between">
                                                        {{-- Content --}}
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-semibold leading-6 text-slate-900 dark:text-white">
                                                                {{ $action['text'] }}
                                                            </p>

                                                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                                                <span>{{ $action['responsible_person'] ?: '-' }}</span>
                                                                <span>·</span>
                                                                <span>{{ $action['deadline'] ?: '-' }}</span>
                                                                <span>·</span>
                                                                <span>{{ $action['media_count'] }} bukti</span>
                                                            </div>
                                                        </div>

                                                        {{-- Status dropdown --}}
                                                        <div class="relative shrink-0" x-data="{ open: false }" x-on:click.outside="open = false">
                                                            <button
                                                                type="button"
                                                                x-on:click="open = !open"
                                                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-white/5"
                                                            >
                                                                <span class="h-2 w-2 rounded-full">·</span>
                                                                <span class="{{ $currentStatus['active'] }}">
                                                                    {{ $action['status_label'] ?? $currentStatus['label'] }}
                                                                </span>

                                                                <x-heroicon-m-chevron-down
                                                                    class="h-3 w-3 transition text-slate-400 dark:text-slate-500"
                                                                    x-bind:class="open ? 'rotate-180' : ''"
                                                                />
                                                            </button>

                                                            <div
                                                                x-show="open"
                                                                x-transition
                                                                x-cloak
                                                                class="absolute right-0 z-20 mt-2 w-36 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-slate-900"
                                                            >
                                                                @foreach ($statusStyles as $statusKey => $style)
                                                                    <button
                                                                        type="button"
                                                                        wire:click="updateActionStatus({{ $action['id'] }}, '{{ $statusKey }}')"
                                                                        x-on:click="open = false"
                                                                        @class([
                                                                            'flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-medium transition',
                                                                            'bg-slate-50 text-slate-900 dark:bg-white/5 dark:text-white' => $status === $statusKey,
                                                                            'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' => $status !== $statusKey,
                                                                        ])
                                                                    >
                                                                        <span class="h-2 w-2 rounded-full {{ $style['dot'] }}"></span>
                                                                        <span>{{ $style['label'] }}</span>

                                                                        @if ($status === $statusKey)
                                                                            <span class="ml-auto text-slate-400">✓</span>
                                                                        @endif
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-slate-950 dark:text-slate-400">
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
                        <div
                            class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900 dark:text-slate-400">
                            Belum ada laporan untuk unit kerja ini.
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center dark:border-white/10 dark:bg-slate-900">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Belum ada laporan untuk ditampilkan</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Setelah laporan insiden masuk dan punya problem, data akan muncul di grup per unit kerja di sini.
                </p>
            </div>
        @endforelse
    </div>
</x-filament-widgets::widget>