<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="fi-filament-info-widget-main">
            <div>
                {{-- IKP Header --}}
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50 dark:bg-primary-900/30">
                        @svg('heroicon-o-building-office-2', 'w-6 h-6 text-primary-600 dark:text-primary-400')
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                            IKP
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Modul Pelaporan Insiden Keselamatan Pasien
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="fi-filament-info-widget-links border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-2">
                <x-filament::link
                    color="gray"
                    href="https://juniyasyos.github.io"
                    :icon="\Filament\Support\Icons\Heroicon::InformationCircle"
                >
                    Version 1.0.0 - Beta
                </x-filament::link>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
