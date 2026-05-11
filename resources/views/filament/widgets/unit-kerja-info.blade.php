<x-filament-widgets::widget class="fi-filament-info-widget">
    <div class="fi-section overflow-hidden rounded-3xl border border-slate-200/70 bg-white dark:border-slate-800 dark:bg-slate-900">

        @if ($unitKerja)

        <div class="p-6 space-y-6">

            {{-- Header --}}
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                <div class="flex items-start gap-4">

                    {{-- Icon --}}
                    <div class="flex items-center justify-center flex-shrink-0 w-14 h-14 rounded-2xl bg-primary-50 text-primary-600 shadow-sm dark:bg-primary-500/10 dark:text-primary-400">
                        @svg("heroicon-o-building-office-2", "w-7 h-7")
                    </div>

                    {{-- Info --}}
                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">
                                {{ $unitKerja->unit_name }}
                            </h2>

                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                                Aktif
                            </span>

                        </div>

                        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-slate-500 dark:text-slate-400">

                            <span class="inline-flex items-center gap-1">
                                @svg('heroicon-m-hashtag', 'w-3.5 h-3.5')
                                {{ $unitKerja->id }}
                            </span>

                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>

                            <span class="inline-flex items-center gap-1">
                                @svg('heroicon-m-calendar-days', 'w-3.5 h-3.5')
                                Dibuat {{ $unitKerja->created_at->translatedFormat('d M Y') }}
                            </span>

                        </div>

                        @if ($unitKerja->description)
                        <p class="max-w-3xl mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                            {{ $unitKerja->description }}
                        </p>
                        @endif

                    </div>
                </div>

                {{-- Quick Action --}}
                <div class="flex items-center gap-2">

                    <button type="button"
                        x-data="{ open: false }"
                        @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-700">

                        @svg('heroicon-o-users', 'w-4 h-4')

                        <span>Lihat Semua User</span>

                        @svg('heroicon-m-chevron-down', 'w-4 h-4 transition-transform')
                    </button>

                </div>

            </div>

            {{-- Divider --}}
            <div class="border-t border-slate-200 dark:border-slate-800"></div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                {{-- Kepala Unit --}}
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 transition hover:-translate-y-0.5 hover:border-primary-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">

                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-primary-100/40 blur-2xl dark:bg-primary-500/10"></div>

                    <div class="relative flex items-start justify-between">

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Kepala Unit
                            </p>

                            <p class="mt-3 text-lg font-bold tracking-tight text-slate-900 dark:text-white truncate">
                                {{ $stats['kepala_unit_name'] ?? '-' }}
                            </p>

                            <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Penanggung jawab utama unit kerja
                            </p>
                        </div>

                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            @svg('heroicon-o-shield-check', 'w-5 h-5')
                        </div>

                    </div>
                </div>

                {{-- Staff Unit --}}
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 transition hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">

                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-sky-100/40 blur-2xl dark:bg-sky-500/10"></div>

                    <div class="relative flex items-start justify-between">

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Staff Unit
                            </p>

                            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                {{ $stats['total_staff_unit'] ?? 0 }}
                            </p>

                            <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Staff operasional dan pelaksana
                            </p>
                        </div>

                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                            @svg('heroicon-o-user-group', 'w-5 h-5')
                        </div>

                    </div>
                </div>

                {{-- Total User --}}
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">

                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-emerald-100/40 blur-2xl dark:bg-emerald-500/10"></div>

                    <div class="relative flex items-start justify-between">

                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                Total User
                            </p>

                            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                {{ $stats['total_users'] ?? 0 }}
                            </p>

                            <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Total seluruh user di unit
                            </p>
                        </div>

                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            @svg('heroicon-o-users', 'w-5 h-5')
                        </div>

                    </div>
                </div>

            </div>

            {{-- User List --}}
            <div
                x-data="{ openUsers: false }"
                class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">

                {{-- Header --}}
                <button type="button"
                    @click="openUsers = !openUsers"
                    class="flex w-full items-center justify-between bg-slate-50 px-5 py-4 text-left transition hover:bg-slate-100 dark:bg-slate-900/70 dark:hover:bg-slate-800">

                    <div class="flex items-center gap-3">

                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            @svg('heroicon-o-users', 'w-5 h-5')
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                                Daftar User Unit Kerja
                            </h3>

                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Menampilkan seluruh staff unit
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-3">

                        <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                            {{ $unitKerja->users->reject(fn($u) => $u->hasRole('kepala_unit'))->count() }} Staff
                        </span>

                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white shadow-sm dark:bg-slate-800">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-slate-500 transition-transform duration-200"
                                x-bind:class="openUsers ? 'rotate-180' : ''"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>

                    </div>

                </button>

                {{-- Content --}}
                <div x-show="openUsers"
                    x-collapse
                    class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">

                    <div class="divide-y divide-slate-200 dark:divide-slate-800">

                        @forelse ($unitKerja->users->reject(fn($u) => $u->hasRole('kepala_unit')) as $user)

                        <div class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40">

                            {{-- Avatar --}}
                            <div class="relative flex-shrink-0">

                                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-sm font-bold text-white shadow-sm">
                                    {{ Str::upper(Str::substr($user->name, 0, 2)) }}
                                </div>

                                <span class="absolute -bottom-1 -right-1 flex h-4 w-4 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>

                            </div>

                            {{-- User Info --}}
                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center gap-2">

                                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $user->name }}
                                    </p>

                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        Staff Unit
                                    </span>

                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">

                                    <span class="inline-flex items-center gap-1 truncate">
                                        @svg('heroicon-m-envelope', 'w-3.5 h-3.5')
                                        {{ $user->email }}
                                    </span>

                                    @if ($user->phone ?? false)
                                    <span class="inline-flex items-center gap-1">
                                        @svg('heroicon-m-phone', 'w-3.5 h-3.5')
                                        {{ $user->phone }}
                                    </span>
                                    @endif

                                </div>

                            </div>

                            {{-- Action --}}
                            <button type="button"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:border-primary-500/20 dark:hover:bg-primary-500/10 dark:hover:text-primary-400">

                                @svg('heroicon-o-eye', 'w-4 h-4')

                            </button>

                        </div>

                        @empty

                        <div class="px-6 py-14 text-center">

                            <div class="flex items-center justify-center w-16 h-16 mx-auto rounded-3xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                @svg("heroicon-o-user-group", "w-8 h-8")
                            </div>

                            <h3 class="mt-5 text-sm font-semibold text-slate-900 dark:text-white">
                                Belum ada user terdaftar
                            </h3>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Tambahkan kepala unit atau staff unit untuk mulai mengelola unit kerja.
                            </p>

                        </div>

                        @endforelse

                    </div>

                </div>

            </div>

        </div>

        @else

        {{-- Empty State --}}
        <div class="px-6 py-20 text-center">

            <div class="flex items-center justify-center w-20 h-20 mx-auto rounded-3xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                @svg("heroicon-o-building-office-2", "w-10 h-10")
            </div>

            <h3 class="mt-6 text-lg font-semibold text-slate-900 dark:text-white">
                Unit kerja belum tersedia
            </h3>

            <p class="max-w-md mx-auto mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                Data unit kerja belum tersedia dalam sistem. Tambahkan unit kerja terlebih dahulu untuk mulai mengelola struktur organisasi dan user.
            </p>

        </div>

        @endif

    </div>
</x-filament-widgets::widget>