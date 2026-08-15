<x-filament-widgets::widget class="fi-filament-info-widget printable-widget">
    <style>
        @media print {
            body, html { background: white !important; margin: 0 !important; padding: 0 !important; }
            .printable-widget { width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
            .printable-widget button, .printable-widget .no-print, details { display: none !important; }
            table { page-break-inside: auto !important; width: 100% !important; border-collapse: collapse !important; }
            tr { page-break-inside: avoid !important; page-break-after: auto !important; }
            thead { display: table-header-group !important; }
            .printable-widget .overflow-x-auto, .printable-widget .overflow-y-auto, .printable-widget .overflow-hidden { overflow: visible !important; }
        }
    </style>
    <script>
        if (typeof window.printThisWidget !== 'function') {
            window.printThisWidget = function(btn) {
                const widget = btn.closest('.printable-widget');
                const originalParent = widget.parentNode;
                const originalNextSibling = widget.nextSibling;
                const appLayout = document.querySelector('.fi-layout');
                const oldDisplay = appLayout ? appLayout.style.display : '';
                
                if (appLayout) appLayout.style.display = 'none';
                document.body.appendChild(widget);
                window.print();
                
                if (originalNextSibling) {
                    originalParent.insertBefore(widget, originalNextSibling);
                } else {
                    originalParent.appendChild(widget);
                }
                if (appLayout) appLayout.style.display = oldDisplay;
            };
        }
    </script>
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