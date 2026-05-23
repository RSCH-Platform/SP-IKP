<div class="mt-8">
    @php
        $incidentUninvestigatedCount = $this->getIncidentUninvestigatedCount();
        $investigationInProgressCount = $this->getInvestigationInProgressCount();

        $tabs = [
            'umum' => [
                'label' => 'Ringkasan Umum',
                'description' => 'Statistik utama, akun, dan kondisi operasional.',
                'badge' => null,
                'icon' => 'heroicon-o-squares-2x2',
                'tone' => 'blue',
            ],
            'laporan_insiden' => [
                'label' => 'Laporan Insiden',
                'description' => 'Laporan terverifikasi yang belum masuk investigasi.',
                'badge' => $incidentUninvestigatedCount,
                'icon' => 'heroicon-o-clipboard-document-list',
                'tone' => 'amber',
            ],
            'investigasi' => [
                'label' => 'Investigasi',
                'description' => 'Kasus aktif yang sedang ditangani tim.',
                'badge' => $investigationInProgressCount,
                'icon' => 'heroicon-o-magnifying-glass',
                'tone' => 'violet',
            ],
        ];

        $toneMap = [
            'blue' => [
                'active' => 'border-gray-100 bg-blue-100 dark:border-blue-300 dark:bg-blue-300',
                'icon' => 'bg-blue-200 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
                'bar' => 'bg-blue-600 dark:bg-blue-400',
                'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
            ],
            'amber' => [
                'active' => 'border-amber-200 bg-amber-50 ring-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:ring-amber-500/20',
                'icon' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                'bar' => 'bg-amber-500 dark:bg-amber-400',
                'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
            ],
            'violet' => [
                'active' => 'border-violet-200 bg-violet-50 ring-violet-100 dark:border-violet-500/30 dark:bg-violet-500/10 dark:ring-violet-500/20',
                'icon' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
                'bar' => 'bg-violet-600 dark:bg-violet-400',
                'badge' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
            ],
        ];

        $activeTab = $tabs[$dashboardTab] ?? $tabs['umum'];
    @endphp

    <div class="space-y-6">
        {{-- HEADER --}}
        <section class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">
                        Dashboard Utama
                    </h1>

                    <p class="max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Pantau ringkasan umum, laporan insiden, dan proses investigasi keselamatan pasien.
                    </p>
                </div>
            </div>
        </section>

        {!! $this->getGlobalWidgetsSchema()->toEmbeddedHtml() !!}

        {{-- TABS --}}
        <section
            class="rounded-2xl border border-slate-200 bg-slate-50 p-2 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                @foreach ($tabs as $tabKey => $tab)
                    @php
                        $tone = $toneMap[$tab['tone']];
                        $isActive = $dashboardTab === $tabKey;
                    @endphp

                    <button type="button" wire:click="$set('dashboardTab', '{{ $tabKey }}')" @class([
                        'group relative overflow-hidden rounded-xl border p-4 text-left transition-all duration-200',
                        'bg-white hover:border-slate-300 hover:bg-slate-50 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700 dark:hover:bg-slate-800/80' => !$isActive,
                        $tone['active'] . ' shadow-sm ring-1' => $isActive,
                    ])>
                        <span @class([
                            'absolute inset-x-4 top-0 h-0.5 rounded-b-full transition-all duration-200',
                            $tone['bar'] => $isActive,
                            'bg-transparent' => !$isActive,
                        ])></span>

                        <div class="flex items-start gap-3">
                            <div @class([
                                'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-all duration-200',
                                $tone['icon'] => $isActive,
                                'bg-slate-50 text-slate-500 ring-1 ring-slate-200 group-hover:bg-white group-hover:text-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700 dark:group-hover:text-slate-200' => !$isActive,
                            ])>
                                <x-dynamic-component :component="$tab['icon']" class="h-5 w-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 @class([
                                            'text-sm font-semibold transition-colors',
                                            'text-slate-950 dark:text-slate-50' => $isActive,
                                            'text-slate-700 group-hover:text-slate-950 dark:text-slate-300 dark:group-hover:text-slate-50' => !$isActive,
                                        ])>
                                            {{ $tab['label'] }}
                                        </h3>

                                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                            {{ $tab['description'] }}
                                        </p>
                                    </div>

                                    @if (!is_null($tab['badge']) && $tab['badge'] > 0)
                                        <span @class([
                                            'shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold',
                                            $tone['badge'] => $isActive,
                                            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => !$isActive,
                                        ])>
                                            {{ number_format($tab['badge']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- CONTENT --}}
        <section class="space-y-6">
            @if ($dashboardTab === 'umum')
                {!! $this->getGeneralWidgetsSchema()->toEmbeddedHtml() !!}
            @endif

            @if ($dashboardTab === 'laporan_insiden')
                {!! $this->getIncidentWidgetsSchema()->toEmbeddedHtml() !!}
            @endif

            @if ($dashboardTab === 'investigasi')
                {!! $this->getInvestigationWidgetsSchema()->toEmbeddedHtml() !!}
            @endif
        </section>
    </div>
</div>