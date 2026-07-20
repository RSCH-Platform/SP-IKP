<div class="mt-8 mb-10">
    <div class="space-y-6">

        {{-- HEADER --}}
        <section class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">
                        Investigasi
                    </h1>

                    <p class="max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Kelola dan pantau proses investigasi insiden keselamatan pasien.
                    </p>
                </div>
            </div>
        </section>

        @php
            $availableTabs = $this->getAvailableTabs();

            $toneMap = [
                'blue' => [
                    'active' => 'border-blue-200 bg-blue-50 ring-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:ring-blue-500/20',
                    'icon'   => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
                    'bar'    => 'bg-blue-600 dark:bg-blue-400',
                    'badge'  => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
                ],
                'violet' => [
                    'active' => 'border-violet-200 bg-violet-50 ring-violet-100 dark:border-violet-500/30 dark:bg-violet-500/10 dark:ring-violet-500/20',
                    'icon'   => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
                    'bar'    => 'bg-violet-600 dark:bg-violet-400',
                    'badge'  => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
                ],
                'amber' => [
                    'active' => 'border-amber-200 bg-amber-50 ring-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:ring-amber-500/20',
                    'icon'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                    'bar'    => 'bg-amber-500 dark:bg-amber-400',
                    'badge'  => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                ],
                'green' => [
                    'active' => 'border-green-200 bg-green-50 ring-green-100 dark:border-green-500/30 dark:bg-green-500/10 dark:ring-green-500/20',
                    'icon'   => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300',
                    'bar'    => 'bg-green-600 dark:bg-green-400',
                    'badge'  => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300',
                ],
            ];

            // Pastikan tab aktif valid; jika tidak ada dalam daftar visible, gunakan tab pertama
            $firstTabKey = array_key_first($availableTabs) ?? 'laporan_aktif';
            $activeTabKey = array_key_exists($investigasiTab, $availableTabs) ? $investigasiTab : $firstTabKey;
            $activeTab = $availableTabs[$activeTabKey] ?? null;
        @endphp

        {{-- SUB-TAB BAR --}}
        @if (count($availableTabs) > 1)
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-2 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($availableTabs as $tabKey => $tab)
                        @php
                            $tone = $toneMap[$tab['tone']] ?? $toneMap['blue'];
                            $isActive = $activeTabKey === $tabKey;
                        @endphp

                        <button
                            type="button"
                            wire:click="$set('investigasiTab', '{{ $tabKey }}')"
                            @class([
                                'group relative overflow-hidden rounded-xl border border-gray-200 p-4 text-left transition-all duration-200',
                                'bg-white hover:border-slate-300 hover:bg-slate-50 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700 dark:hover:bg-slate-800/80' => !$isActive,
                                $tone['active'] => $isActive,
                            ])
                        >
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
                            </div>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- KONTEN WIDGET AKTIF --}}
        @if ($activeTab)
            <x-filament-widgets::widgets
                :columns="1"
                :widgets="[$activeTab['widget']]"
            />
        @endif

    </div>
</div>
