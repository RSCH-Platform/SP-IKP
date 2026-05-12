<x-filament-widgets::widget class="fi-filament-info-widget">
    <div class="space-y-6 fi-section p-4">

        <!-- Header -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    📊 Analisis Unit Kerja - Manager
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Monitoring dan analisis mendalam performa unit kerja dengan breakdown jenis insiden
                </p>
            </div>

            @include('filament.widgets.table-data.filter-bar')
        </div>

        @include('filament.widgets.table-data.unit-performance')

        @include('filament.widgets.table-data.priority-risk')

    </div>
</x-filament-widgets::widget>