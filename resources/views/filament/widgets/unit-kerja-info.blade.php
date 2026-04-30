<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        @if ($unitKerja)
        <div class="space-y-6">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">

                    {{-- Icon --}}
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                        @svg("heroicon-o-building-office-2", "w-6 h-6")
                    </div>

                    {{-- Title --}}
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            {{ $unitKerja->unit_name }}
                        </h2>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            ID: {{ $unitKerja->id }} •
                            {{ $unitKerja->created_at->translatedFormat('d M Y') }}
                        </p>

                        @if ($unitKerja->description)
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-xl">
                            {{ $unitKerja->description }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-slate-200 dark:border-slate-700"></div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">

                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Penanggung Jawab
                            </p>
                            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">
                                {{ $stats['total_users'] }}
                            </p>
                        </div>

                        <div class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700">
                            @svg('heroicon-o-users', 'w-5 h-5 text-slate-600 dark:text-slate-300')
                        </div>
                    </div>
                </div>

            </div>

            {{-- Users --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold tracking-wide text-slate-500 uppercase dark:text-slate-400">
                        Penanggung Jawab
                    </h3>
                </div>

                <div class="space-y-2">

                    @forelse ($unitKerja->users as $user)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl  border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:shadow-sm hover:border-slate-300 dark:hover:border-slate-600 transition-all">

                        {{-- Avatar --}}
                        <div class="flex items-center justify-center w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white text-xs font-semibold">
                            {{ Str::upper(Str::substr($user->name, 0, 2)) }}
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 truncate dark:text-white">
                                {{ $user->name }}
                            </p>
                            <p class="text-xs text-slate-500 truncate dark:text-slate-400">
                                {{ $user->email }}
                            </p>
                        </div>

                        {{-- Optional badge --}}
                        <span class="text-[10px] px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            User
                        </span>
                    </div>

                    @empty
                    <div class="px-4 py-10 text-center rounded-xl border border-dashed 
                        border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">

                        @svg("heroicon-o-user-group", "w-10 h-10 mx-auto text-slate-400")

                        <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                            Belum ada penanggung jawab
                        </p>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Tambahkan user untuk mulai mengelola unit ini
                        </p>
                    </div>
                    @endforelse

                </div>
            </div>

        </div>
        @else

        {{-- Empty State --}}
        <div class="px-4 py-14 text-center">
            @svg("heroicon-o-building-office-2", "w-14 h-14 mx-auto text-slate-400")

            <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">
                Tidak ada unit kerja
            </p>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Data unit kerja belum tersedia dalam sistem
            </p>
        </div>

        @endif
    </x-filament::section>
</x-filament-widgets::widget>