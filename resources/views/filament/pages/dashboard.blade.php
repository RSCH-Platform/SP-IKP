<div class="mt-10">
    @php
        $incidentUninvestigatedCount = $this->getIncidentUninvestigatedCount();
        $investigationInProgressCount = $this->getInvestigationInProgressCount();

        $tabs = [
            'umum' => [
                'label' => 'Ringkasan Umum',
                'description' => 'Statistik utama, info akun, dan status operasional.',
                'badge' => null,
                'icon' => 'heroicon-o-squares-2x2',
                'color' => 'blue',
            ],
            'laporan_insiden' => [
                'label' => 'Laporan Insiden',
                'description' => 'Laporan terverifikasi yang belum masuk investigasi.',
                'badge' => $incidentUninvestigatedCount,
                'icon' => 'heroicon-o-clipboard-document-list',
                'color' => 'amber',
            ],
            'investigasi' => [
                'label' => 'Investigasi',
                'description' => 'Kasus yang sedang aktif dikerjakan tim investigasi.',
                'badge' => $investigationInProgressCount,
                'icon' => 'heroicon-o-magnifying-glass',
                'color' => 'purple',
            ],
        ];

        $colorMap = [
            'blue' => [
                'icon_bg' => 'bg-blue-100 dark:bg-blue-950',
                'icon_text' => 'text-blue-600 dark:text-blue-400',
                'active_icon_bg' => 'bg-blue-200 dark:bg-blue-900',
                'active_border' => 'border-blue-500 dark:border-blue-400',
                'active_bg' => 'bg-blue-50 dark:bg-blue-950/60',
                'active_bar' => 'bg-blue-500 dark:bg-blue-400',
                'badge_bg' => 'bg-blue-100 dark:bg-blue-900',
                'badge_text' => 'text-blue-700 dark:text-blue-300',
            ],
            'amber' => [
                'icon_bg' => 'bg-amber-100 dark:bg-amber-950',
                'icon_text' => 'text-amber-600 dark:text-amber-400',
                'active_icon_bg' => 'bg-amber-200 dark:bg-amber-900',
                'active_border' => 'border-amber-500 dark:border-amber-400',
                'active_bg' => 'bg-amber-50 dark:bg-amber-950/60',
                'active_bar' => 'bg-amber-500 dark:bg-amber-400',
                'badge_bg' => 'bg-amber-100 dark:bg-amber-900',
                'badge_text' => 'text-amber-700 dark:text-amber-300',
            ],
            'purple' => [
                'icon_bg' => 'bg-purple-100 dark:bg-purple-950',
                'icon_text' => 'text-purple-600 dark:text-purple-400',
                'active_icon_bg' => 'bg-purple-200 dark:bg-purple-900',
                'active_border' => 'border-purple-500 dark:border-purple-400',
                'active_bg' => 'bg-purple-50 dark:bg-purple-950/60',
                'active_bar' => 'bg-purple-500 dark:bg-purple-400',
                'badge_bg' => 'bg-purple-100 dark:bg-purple-900',
                'badge_text' => 'text-purple-700 dark:text-purple-300',
            ],
        ];
    @endphp

    <div class="space-y-8">

        {{-- HEADER --}}
        <section class="border-b border-slate-200 pb-6 dark:border-white/10">
            <div class="space-y-2">
                <h1 class="text-xl font-semibold text-slate-900 dark:text-white">
                    Dashboard IKP
                </h1>
                <p class="max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Gunakan kategori di bawah untuk melihat statistik umum, laporan insiden yang belum diproses, dan
                    investigasi yang sedang berjalan.
                </p>
            </div>
        </section>

        {{-- TABS --}}
        <section>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">

                @foreach ($tabs as $tabKey => $tab)
                            @php
                                $c = $colorMap[$tab['color']];
                                $isActive = $dashboardTab === $tabKey;
                            @endphp

                            <button type="button" wire:click="$set('dashboardTab', '{{ $tabKey }}')" class="group relative overflow-hidden rounded-xl border p-5 text-left transition-all duration-200
                                        {{ $isActive
                    ? $c['active_bg'] . ' ' . $c['active_border'] . ' border-[1.5px]'
                    : 'border-slate-200 bg-white hover:bg-slate-50 dark:border-white/10 dark:bg-slate-950 dark:hover:bg-white/5'
                                        }}">
                                {{-- TOP ACCENT BAR --}}
                                <span class="absolute inset-x-0 top-0 h-[3px] rounded-b-sm transition-all duration-200
                                        {{ $isActive ? $c['active_bar'] : 'bg-transparent' }}">
                                </span>

                                {{-- ICON --}}
                                <div class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-lg transition-colors duration-200
                                        {{ $isActive ? $c['active_icon_bg'] : $c['icon_bg'] }}">
                                    <x-dynamic-component :component="$tab['icon']" class="h-5 w-5 {{ $c['icon_text'] }}" />
                                </div>

                                {{-- LABEL + BADGE --}}
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $tab['label'] }}
                                    </span>

                                    @if (!is_null($tab['badge']) && $tab['badge'] > 0)
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                                    {{ $c['badge_bg'] }} {{ $c['badge_text'] }}">
                                            {{ number_format($tab['badge']) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- DESCRIPTION --}}
                                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    {{ $tab['description'] }}
                                </p>

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