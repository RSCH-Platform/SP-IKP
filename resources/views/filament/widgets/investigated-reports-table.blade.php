<x-filament-widgets::widget class="printable-widget w-full min-w-0 max-w-full">
    <style>
        /* Scroll container styling */
        .investigasi-scroll-container {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            cursor: grab;
            scrollbar-width: thin !important;
            scrollbar-color: #94a3b8 #f1f5f9 !important;
        }

        .investigasi-top-scrollbar {
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch;
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            height: 12px !important;
            scrollbar-width: thin !important;
            scrollbar-color: #94a3b8 #f1f5f9 !important;
        }

        /* WebKit Scrollbar Styling (Always Visible & Prominent) */
        .custom-investigasi-scroll::-webkit-scrollbar,
        .investigasi-top-scrollbar::-webkit-scrollbar {
            height: 10px !important;
            display: block !important;
        }
        .custom-investigasi-scroll::-webkit-scrollbar-track,
        .investigasi-top-scrollbar::-webkit-scrollbar-track {
            background: #e2e8f0 !important;
            border-radius: 9999px !important;
        }
        .custom-investigasi-scroll::-webkit-scrollbar-thumb,
        .investigasi-top-scrollbar::-webkit-scrollbar-thumb {
            background: #94a3b8 !important;
            border-radius: 9999px !important;
            border: 2px solid #e2e8f0 !important;
        }
        .custom-investigasi-scroll::-webkit-scrollbar-thumb:hover,
        .investigasi-top-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b !important;
        }

        /* Dark Mode Scrollbars */
        .dark .investigasi-scroll-container,
        .dark .investigasi-top-scrollbar {
            scrollbar-color: #64748b #1e293b !important;
        }
        .dark .custom-investigasi-scroll::-webkit-scrollbar-track,
        .dark .investigasi-top-scrollbar::-webkit-scrollbar-track {
            background: #1e293b !important;
        }
        .dark .custom-investigasi-scroll::-webkit-scrollbar-thumb,
        .dark .investigasi-top-scrollbar::-webkit-scrollbar-thumb {
            background: #64748b !important;
            border: 2px solid #1e293b !important;
        }
        .dark .custom-investigasi-scroll::-webkit-scrollbar-thumb:hover,
        .dark .investigasi-top-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8 !important;
        }

        /* Fixed table sizing (Forces horizontal overflow regardless of Tailwind build) */
        .investigasi-table-fixed {
            min-width: 1780px !important;
            width: 1780px !important;
            table-layout: fixed !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        @media print {
            body, html { background: white !important; margin: 0 !important; padding: 0 !important; }
            .printable-widget { width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
            .printable-widget button, .printable-widget .no-print, details, .investigasi-top-scrollbar { display: none !important; }
            table { page-break-inside: auto !important; width: 100% !important; min-width: 0 !important; border-collapse: collapse !important; }
            table colgroup col { width: auto !important; min-width: 0 !important; }
            tr { page-break-inside: avoid !important; page-break-after: auto !important; }
            thead { display: table-header-group !important; }
            .printable-widget .overflow-x-auto, .printable-widget .overflow-y-auto, .printable-widget .overflow-hidden, .investigasi-scroll-container { overflow: visible !important; }
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
    <div
        class="w-full min-w-0 max-w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-950">

        {{-- Header --}}
        <div class="border-b border-slate-200 px-4 py-3 dark:border-white/10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold leading-5 text-slate-900 dark:text-white">
                            Pemantauan Investigasi
                        </h3>
                        <span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-semibold text-violet-700 ring-1 ring-inset ring-violet-700/10 dark:bg-violet-900/30 dark:text-violet-300">
                            Analisis RCA & Rekomendasi
                        </span>
                    </div>

                    <p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">
                        Pantau status investigasi, masalah (CMP/SDP), penyebab langsung, akar masalah, dan rekomendasi perbaikan.
                    </p>

                    {{-- Quick Counter Badges --}}
                    <div class="mt-2 flex flex-wrap items-center gap-2 no-print">
                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            <span>Total Laporan: <strong class="font-semibold text-slate-900 dark:text-white">{{ $totalReports ?? 0 }}</strong></span>
                        </span>

                        <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                            <span>Masalah Dianalisis: <strong class="font-semibold text-blue-900 dark:text-blue-200">{{ $totalProblems ?? 0 }}</strong></span>
                        </span>
                    </div>
                </div>

                {{-- Export Actions --}}
                <div class="flex items-center gap-2 self-start no-print">
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-export-modal')"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-emerald-300
                               bg-emerald-50 px-3 py-1.5 text-[11px] font-medium text-emerald-700 transition
                               hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/20
                               dark:text-emerald-400 dark:hover:bg-emerald-900/40"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-3.5 w-3.5" />
                        Export Excel
                    </button>

                    <button
                        type="button"
                        x-data
                        @click="
                            const widget = $el.closest('.printable-widget');
                            if (widget) {
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
                            } else {
                                window.print();
                            }
                        "
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-300
                               bg-slate-50 px-3 py-1.5 text-[11px] font-medium text-slate-700 transition
                               hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900/20
                               dark:text-slate-400 dark:hover:bg-slate-900/40"
                    >
                        <x-filament::icon icon="heroicon-o-printer" class="h-3.5 w-3.5" />
                        Print PDF
                    </button>
                </div>
            </div>

            {{-- Accordion Filter --}}
            <details class="group mt-3">
                <summary
                    class="flex cursor-pointer list-none items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-200 dark:hover:bg-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <x-filament::icon
                            icon="heroicon-o-funnel"
                            class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400"
                        />

                        <span>Filter laporan</span>
                    </div>

                    <x-filament::icon
                        icon="heroicon-o-chevron-down"
                        class="h-3.5 w-3.5 text-slate-400 transition group-open:rotate-180"
                    />
                </summary>

                <div
                    class="mt-2 rounded-lg border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
                    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Tahun
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedYear">
                                    <option value="">Semua tahun</option>
                                    @foreach ($this->getAvailableYears() as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Bulan
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedMonth">
                                    <option value="">Semua bulan</option>
                                    @foreach ($this->getMonthOptions() as $monthValue => $monthLabel)
                                        <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Jenis Insiden
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedJenisInsiden">
                                    @foreach ($this->getIncidentTypeOptions() as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Status
                            </label>

                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="selectedStatus">
                                    @foreach ($this->getStatusOptions() as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </div>
            </details>
        </div>

        {{-- Table --}}
        @php
            // Style only: dibuat lebih compact
            $thPadding = 'px-2.5 py-2';
            $tdPadding = 'px-2.5 py-2';
            $textSize = 'text-[11px]';
        @endphp

        <div
            class="p-3 w-full min-w-0 max-w-full"
            x-data="{
                canScrollLeft: false,
                canScrollRight: false,
                scrollEl: null,
                topScrollEl: null,
                isSyncing: false,
                scrollProgress: 0,
                init() {
                    this.$nextTick(() => {
                        this.scrollEl = this.$refs.tableWrapper ? this.$refs.tableWrapper.querySelector('.investigasi-scroll-container, .overflow-x-auto') : null;
                        this.topScrollEl = this.$refs.topScroll;
                        if (!this.scrollEl) return;
                        
                        this.updateScroll();

                        // Sync scroll between main table and top scrollbar
                        this.scrollEl.addEventListener('scroll', () => {
                            if (this.isSyncing) return;
                            this.isSyncing = true;
                            if (this.topScrollEl) {
                                this.topScrollEl.scrollLeft = this.scrollEl.scrollLeft;
                            }
                            this.updateScroll();
                            this.isSyncing = false;
                        }, { passive: true });

                        if (this.topScrollEl) {
                            this.topScrollEl.addEventListener('scroll', () => {
                                if (this.isSyncing) return;
                                this.isSyncing = true;
                                this.scrollEl.scrollLeft = this.topScrollEl.scrollLeft;
                                this.updateScroll();
                                this.isSyncing = false;
                            }, { passive: true });
                        }

                        // Mouse drag-to-scroll
                        let isDown = false;
                        let startX = 0;
                        let scrollStart = 0;
                        this.scrollEl.addEventListener('mousedown', (e) => {
                            if (['BUTTON', 'A', 'INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;
                            isDown = true;
                            startX = e.pageX - this.scrollEl.offsetLeft;
                            scrollStart = this.scrollEl.scrollLeft;
                            this.scrollEl.style.cursor = 'grabbing';
                            this.scrollEl.style.userSelect = 'none';
                        });
                        window.addEventListener('mouseup', () => {
                            if (!isDown) return;
                            isDown = false;
                            if (this.scrollEl) {
                                this.scrollEl.style.cursor = '';
                                this.scrollEl.style.userSelect = '';
                            }
                        });
                        this.scrollEl.addEventListener('mousemove', (e) => {
                            if (!isDown) return;
                            e.preventDefault();
                            const x = e.pageX - this.scrollEl.offsetLeft;
                            const walk = (x - startX) * 1.5;
                            this.scrollEl.scrollLeft = scrollStart - walk;
                        });

                        window.addEventListener('resize', () => this.updateScroll(), { passive: true });
                    });
                },
                updateScroll() {
                    if (!this.scrollEl) return;
                    const max = this.scrollEl.scrollWidth - this.scrollEl.clientWidth;
                    this.canScrollLeft = this.scrollEl.scrollLeft > 6;
                    this.canScrollRight = this.scrollEl.scrollLeft < (max - 6);
                    this.scrollProgress = max > 0 ? Math.round((this.scrollEl.scrollLeft / max) * 100) : 0;
                },
                scrollLeft() {
                    if (this.scrollEl) {
                        this.scrollEl.scrollBy({ left: -380, behavior: 'smooth' });
                    }
                },
                scrollRight() {
                    if (this.scrollEl) {
                        this.scrollEl.scrollBy({ left: 380, behavior: 'smooth' });
                    }
                }
            }"
        >
            {{-- Top Scroll Hint & Navigation Controls --}}
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2 px-1 no-print">
                <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-3.5 w-3.5 text-primary-500" />
                        Tabel dapat digeser ke samping (klik drag / scrollbar)
                    </span>
                    <span
                        class="hidden sm:inline text-[10px] text-slate-400 dark:text-slate-500"
                        x-text="'Posisi: ' + scrollProgress + '%'"
                    ></span>
                </div>

                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="scrollLeft()"
                        :disabled="!canScrollLeft"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-35 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-white/[0.06]"
                        title="Geser tabel ke kiri"
                    >
                        <x-filament::icon icon="heroicon-o-chevron-left" class="h-3.5 w-3.5" />
                        <span>Geser Kiri</span>
                    </button>

                    <button
                        type="button"
                        @click="scrollRight()"
                        :disabled="!canScrollRight"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-35 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-white/[0.06]"
                        title="Geser tabel ke kanan"
                    >
                        <span>Geser Kanan</span>
                        <x-filament::icon icon="heroicon-o-chevron-right" class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            {{-- Top Scrollbar for Quick Horizontal Scrolling without scrolling down to bottom --}}
            <div
                x-ref="topScroll"
                class="investigasi-top-scrollbar mb-1.5 rounded bg-slate-100/80 dark:bg-slate-900/50 no-print"
                title="Geser scrollbar ini untuk navigasi samping"
            >
                <div style="width: 1780px; height: 1px;"></div>
            </div>

            <div x-ref="tableWrapper" class="relative w-full min-w-0 max-w-full">
                {{-- Left Scroll Shadow Cue --}}
                <div
                    x-show="canScrollLeft"
                    x-transition.opacity
                    class="pointer-events-none absolute left-0 top-0 bottom-0 z-10 w-6 bg-gradient-to-r from-slate-900/10 to-transparent dark:from-black/40"
                ></div>

                {{-- Right Scroll Shadow Cue --}}
                <div
                    x-show="canScrollRight"
                    x-transition.opacity
                    class="pointer-events-none absolute right-0 top-0 bottom-0 z-10 w-6 bg-gradient-to-l from-slate-900/10 to-transparent dark:from-black/40"
                ></div>

                <x-report-table
                    containerClass="w-full min-w-0 max-w-full"
                    tableClass="investigasi-table-fixed"
                    scrollClass="investigasi-scroll-container custom-investigasi-scroll rounded-lg border border-slate-200 dark:border-white/10"
                    style="min-width: 1780px !important; width: 1780px !important;"
                >
                    <x-slot:colgroup>
                        <colgroup>
                            <col style="width: 130px; min-width: 130px;">
                            <col style="width: 290px; min-width: 290px;">
                            <col style="width: 115px; min-width: 115px;">
                            <col style="width: 155px; min-width: 155px;">
                            <col style="width: 330px; min-width: 330px;">
                            <col style="width: 250px; min-width: 250px;">
                            <col style="width: 250px; min-width: 250px;">
                            <col style="width: 260px; min-width: 260px;">
                        </colgroup>
                    </x-slot:colgroup>

                    <x-slot:header>
                        <tr class="bg-slate-100/80 text-slate-700 dark:bg-white/[0.05] dark:text-slate-200">
                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-calendar" class="h-3.5 w-3.5 text-slate-400" />
                                    <span>Tanggal</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-document-text" class="h-3.5 w-3.5 text-slate-400" />
                                    <span>Insiden</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-shield-exclamation" class="h-3.5 w-3.5 text-slate-400" />
                                    <span>Jenis</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-building-office-2" class="h-3.5 w-3.5 text-slate-400" />
                                    <span>Unit Kerja</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-3.5 w-3.5 text-blue-500" />
                                    <span>Masalah (CMP / SDP)</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-3.5 w-3.5 text-amber-500" />
                                    <span>Penyebab Langsung</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-3.5 w-3.5 text-rose-500" />
                                    <span>Akar Masalah</span>
                                </div>
                            </x-report-table.th>

                            <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-check-badge" class="h-3.5 w-3.5 text-emerald-500" />
                                    <span>Rekomendasi</span>
                                </div>
                            </x-report-table.th>
                        </tr>
                    </x-slot:header>

                    @forelse ($rows ?? [] as $groupIndex => $group)
                        @php
                            $base = $group['base'] ?? [];
                            $problems = $group['problems'] ?? [];
                            $rowspan = count($problems) ?: 1;
                            $zebraBg = $loop->even ? 'bg-slate-50/40 dark:bg-white/[0.015]' : 'bg-white dark:bg-slate-950';
                        @endphp

                        @foreach ($problems as $i => $p)
                            @php
                                $isLastInGroup = ($i === count($problems) - 1);
                                $rowBorder = $isLastInGroup
                                    ? 'border-b-2 border-slate-300 dark:border-slate-800'
                                    : 'border-b border-slate-100 dark:border-white/5';
                            @endphp

                            <tr class="align-top {{ $zebraBg }} transition-colors hover:bg-primary-50/30 dark:hover:bg-white/[0.035]">
                                @if ($i === 0)
                                    {{-- Tanggal --}}
                                    <x-report-table.td
                                        rowspan="{{ $rowspan }}"
                                        class="{{ $tdPadding }} {{ $textSize }} align-top whitespace-nowrap {{ $rowBorder }} text-slate-600 dark:text-slate-300"
                                    >
                                        <div class="inline-flex items-center gap-1.5 rounded-md bg-slate-100/80 px-2 py-1 font-medium text-slate-700 dark:bg-slate-900/80 dark:text-slate-300">
                                            <x-filament::icon icon="heroicon-m-calendar" class="h-3.5 w-3.5 text-slate-400" />
                                            <span>{{ $base['tanggal_insiden'] ?? '-' }}</span>
                                        </div>
                                    </x-report-table.td>

                                    {{-- Insiden --}}
                                    <x-report-table.td
                                        rowspan="{{ $rowspan }}"
                                        class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-900 dark:text-white"
                                    >
                                        <div class="space-y-1">
                                            @if (!empty($base['nomor_laporan']))
                                                <span class="inline-block font-mono text-[10px] tracking-wide text-slate-400 dark:text-slate-500">
                                                    {{ $base['nomor_laporan'] }}
                                                </span>
                                            @endif

                                            <div
                                                class="font-semibold leading-snug line-clamp-2 text-slate-900 dark:text-white"
                                                title="{{ $base['deskripsi_kategori_insiden'] ?? '-' }}"
                                            >
                                                {{ $base['deskripsi_kategori_insiden'] ?? '-' }}
                                            </div>

                                            @if (!empty($base['grading_risiko']))
                                                @php
                                                    $grading = strtolower((string) $base['grading_risiko']);
                                                @endphp
                                                <div>
                                                    @if (str_contains($grading, 'merah'))
                                                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-semibold text-rose-700 bg-rose-50 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-400">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                            Grading Merah
                                                        </span>
                                                    @elseif (str_contains($grading, 'kuning'))
                                                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-semibold text-amber-700 bg-amber-50 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                            Grading Kuning
                                                        </span>
                                                    @elseif (str_contains($grading, 'hijau'))
                                                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Grading Hijau
                                                        </span>
                                                    @elseif (str_contains($grading, 'biru'))
                                                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-semibold text-blue-700 bg-blue-50 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-950/40 dark:text-blue-400">
                                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                            Grading Biru
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </x-report-table.td>

                                    {{-- Jenis Insiden --}}
                                    <x-report-table.td
                                        rowspan="{{ $rowspan }}"
                                        class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-600 dark:text-slate-300"
                                    >
                                        @php
                                            $jenis = strtoupper((string) ($base['jenis_insiden'] ?? ''));
                                        @endphp
                                        @if (str_contains($jenis, 'SENTINEL'))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-950/40 dark:text-rose-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                Sentinel
                                            </span>
                                        @elseif (str_contains($jenis, 'KTD'))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                KTD
                                            </span>
                                        @elseif (str_contains($jenis, 'KTC'))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2 py-0.5 text-[10px] font-semibold text-yellow-800 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-950/40 dark:text-yellow-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span>
                                                KTC
                                            </span>
                                        @elseif (str_contains($jenis, 'KNC'))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-950/40 dark:text-sky-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                                KNC
                                            </span>
                                        @elseif (str_contains($jenis, 'KPC'))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-semibold text-teal-700 ring-1 ring-inset ring-teal-600/20 dark:bg-teal-950/40 dark:text-teal-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                                KPC
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $base['jenis_insiden'] ?? '-' }}
                                            </span>
                                        @endif
                                    </x-report-table.td>

                                    {{-- Unit Kerja --}}
                                    <x-report-table.td
                                        rowspan="{{ $rowspan }}"
                                        class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-600 dark:text-slate-300"
                                    >
                                        <div class="inline-flex items-center gap-1.5 rounded-md bg-slate-50 px-2 py-1 font-medium text-slate-700 ring-1 ring-inset ring-slate-200/80 dark:bg-slate-900/40 dark:text-slate-300 dark:ring-white/10" title="{{ $base['unit_kerja'] ?? '-' }}">
                                            <x-filament::icon icon="heroicon-m-building-office-2" class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                                            <span class="break-words line-clamp-2 leading-tight">{{ $base['unit_kerja'] ?? '-' }}</span>
                                        </div>
                                    </x-report-table.td>
                                @endif

                                {{-- Masalah (CMP / SDP) --}}
                                @if (!empty($p['is_first_subrow']))
                                    <x-report-table.td
                                        rowspan="{{ $p['problem_rowspan'] ?? 1 }}"
                                        class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-700 dark:text-slate-200"
                                    >
                                        @if (filled($p['problem_type']) || (filled($p['problem_description']) && $p['problem_description'] !== '-'))
                                            @php
                                                $typeUpper = strtoupper((string) $p['problem_type']);
                                            @endphp
                                            <div class="rounded-lg border border-slate-200/90 bg-white/90 p-2.5 shadow-xs transition hover:border-slate-300 dark:border-white/10 dark:bg-slate-900/70">
                                                <div class="mb-1.5 flex items-center justify-between gap-1.5">
                                                    @if ($typeUpper === 'CMP')
                                                        <span
                                                            class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-blue-700 ring-1 ring-inset ring-blue-700/20 dark:bg-blue-950/50 dark:text-blue-300"
                                                            title="Care Management Problem"
                                                        >
                                                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                            CMP
                                                        </span>
                                                    @elseif ($typeUpper === 'SDP')
                                                        <span
                                                            class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-purple-700 ring-1 ring-inset ring-purple-700/20 dark:bg-purple-950/50 dark:text-purple-300"
                                                            title="Service Delivery Problem"
                                                        >
                                                            <span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>
                                                            SDP
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-600/10 dark:bg-slate-800 dark:text-slate-300">
                                                            {{ $typeUpper }}
                                                        </span>
                                                    @endif

                                                    @if (($base['problems_count'] ?? 1) > 1)
                                                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                            Masalah #{{ $p['problem_index'] ?? ($i + 1) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <p
                                                    class="text-[11px] leading-relaxed text-slate-800 dark:text-slate-200 break-words"
                                                    title="{{ $p['problem_description'] ?? '-' }}"
                                                >
                                                    {{ $p['problem_description'] ?? '-' }}
                                                </p>
                                            </div>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">- Belum ada masalah tercatat -</span>
                                        @endif
                                    </x-report-table.td>
                                @endif

                                {{-- Penyebab Langsung --}}
                                <x-report-table.td
                                    class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-700 dark:text-slate-300"
                                >
                                    @if (filled($p['penyebab_langsung']) && $p['penyebab_langsung'] !== '-')
                                        <div class="rounded-lg border border-amber-200/80 bg-amber-50/40 p-2.5 text-[11px] leading-relaxed text-slate-700 dark:border-amber-800/40 dark:bg-amber-950/15 dark:text-slate-300">
                                            <div class="mb-1 flex items-center gap-1 text-[10px] font-semibold text-amber-700 dark:text-amber-400">
                                                <x-filament::icon icon="heroicon-m-arrow-trending-down" class="h-3 w-3 shrink-0" />
                                                <span>Penyebab Langsung (Why 1)</span>
                                            </div>
                                            <div class="break-words line-clamp-3" title="{{ $p['penyebab_langsung'] }}">
                                                {{ $p['penyebab_langsung'] }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">-</span>
                                    @endif
                                </x-report-table.td>

                                {{-- Akar Masalah --}}
                                <x-report-table.td
                                    class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-700 dark:text-slate-300"
                                >
                                    @if (filled($p['akar_masalah']) && $p['akar_masalah'] !== '-')
                                        <div class="rounded-lg border border-rose-200/80 bg-rose-50/40 p-2.5 text-[11px] leading-relaxed text-slate-700 dark:border-rose-800/40 dark:bg-rose-950/15 dark:text-slate-300">
                                            <div class="mb-1 flex items-center gap-1 text-[10px] font-semibold text-rose-700 dark:text-rose-400">
                                                <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-3 w-3 shrink-0" />
                                                <span>Akar Masalah (Root Cause)</span>
                                            </div>
                                            <div class="break-words line-clamp-3" title="{{ $p['akar_masalah'] }}">
                                                {{ $p['akar_masalah'] }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">-</span>
                                    @endif
                                </x-report-table.td>

                                {{-- Rekomendasi --}}
                                <x-report-table.td
                                    class="{{ $tdPadding }} {{ $textSize }} align-top {{ $rowBorder }} text-slate-700 dark:text-slate-300"
                                >
                                    @if (filled($p['rekomendasi']) && $p['rekomendasi'] !== '-')
                                        <div class="rounded-lg border border-emerald-200/80 bg-emerald-50/40 p-2.5 text-[11px] leading-relaxed text-slate-700 dark:border-emerald-800/40 dark:bg-emerald-950/15 dark:text-slate-300">
                                            <div class="mb-1 flex items-center gap-1 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">
                                                <x-filament::icon icon="heroicon-m-check-badge" class="h-3 w-3 shrink-0" />
                                                <span>Rekomendasi Tindakan</span>
                                            </div>
                                            <div class="break-words line-clamp-3" title="{{ $p['rekomendasi'] }}">
                                                {{ $p['rekomendasi'] }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">-</span>
                                    @endif
                                </x-report-table.td>
                            </tr>
                        @endforeach
                    @empty
                        <x-report-table.empty
                            :colspan="8"
                            title="Belum ada data investigasi"
                            description="Tidak ada laporan yang sesuai dengan filter yang dipilih."
                        />
                    @endforelse
                </x-report-table>
            </div>


            {{-- Pagination Footer --}}
            @if ($paginator->total() > 0)
                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Per-page selector --}}
                    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Tampilkan</span>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        <span>item per halaman</span>
                    </div>

                    {{-- Info + Nav --}}
                    <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center">

                        {{-- Info --}}
                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                            Menampilkan
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                            dari
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span>
                            laporan
                        </span>

                        {{-- Prev / Page Numbers / Next --}}
                        @if ($paginator->hasPages())
                            <nav class="flex items-center gap-1" aria-label="Pagination">

                                {{-- Prev --}}
                                <button
                                    wire:click="previousPage"
                                    wire:loading.attr="disabled"
                                    @disabled($paginator->onFirstPage())
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.06]"
                                >
                                    <x-filament::icon icon="heroicon-o-chevron-left" class="h-3 w-3" />
                                    Prev
                                </button>

                                {{-- Page Numbers (window ±2) --}}
                                @foreach (
                                    $paginator->getUrlRange(
                                        max($paginator->currentPage() - 2, 1),
                                        min($paginator->currentPage() + 2, $paginator->lastPage()),
                                    )
                                    as $page => $url
                                )
                                    @if ($page === $paginator->currentPage())
                                        <span
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-primary-600 text-[11px] font-semibold text-white"
                                        >
                                            {{ $page }}
                                        </span>
                                    @else
                                        <button
                                            wire:click="gotoPage({{ $page }})"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-[11px] font-medium text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.06]"
                                        >
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                <button
                                    wire:click="nextPage"
                                    wire:loading.attr="disabled"
                                    @disabled($paginator->onLastPage())
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-white/[0.06]"
                                >
                                    Next
                                    <x-filament::icon icon="heroicon-o-chevron-right" class="h-3 w-3" />
                                </button>

                            </nav>
                        @endif

                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Export Column Picker Modal (Alpine.js)                       --}}
    {{-- ============================================================ --}}
    <div
        x-data="{
            open: false,
            columns: {
                tanggal_insiden:            { label: 'Tanggal Insiden',   checked: true },
                deskripsi_kategori_insiden: { label: 'Judul Insiden',     checked: true },
                jenis_insiden:              { label: 'Jenis Insiden',     checked: true },
                unit_kerja:                 { label: 'Unit Kerja',        checked: true },
                status:                     { label: 'Status',            checked: true },
                masalah:                    { label: 'Masalah (CMP/SDP)', checked: true },
                penyebab_langsung:          { label: 'Penyebab Langsung', checked: true },
                akar_masalah:               { label: 'Akar Masalah',      checked: true },
                rekomendasi:                { label: 'Rekomendasi',       checked: true },
            },
            get anyChecked() {
                return Object.values(this.columns).some(c => c.checked);
            },
            toggleAll(value) {
                Object.values(this.columns).forEach(c => c.checked = value);
            }
        }"
        @open-export-modal.window="open = true"
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/40 dark:bg-black/60"
            @click="open = false"
        ></div>

        {{-- Modal Box --}}
        <div class="relative w-full max-w-sm rounded-xl border border-slate-200 bg-white
                    p-5 shadow-xl dark:border-white/10 dark:bg-slate-900">

            {{-- Modal Header --}}
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                        Pilih Kolom yang Diekspor
                    </h3>
                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                        Filter aktif akan diterapkan otomatis pada hasil export.
                    </p>
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="mt-0.5 rounded p-0.5 text-slate-400 transition hover:bg-slate-100
                           hover:text-slate-600 dark:hover:bg-white/[0.06] dark:hover:text-slate-300"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                </button>
            </div>

            {{-- Select All / Deselect All --}}
            <div class="mt-3 flex items-center gap-3 border-b border-slate-100 pb-3 dark:border-white/10">
                <button
                    type="button"
                    @click="toggleAll(true)"
                    class="text-[11px] font-medium text-primary-600 hover:underline dark:text-primary-400"
                >
                    Pilih semua
                </button>
                <span class="text-slate-300 dark:text-slate-600">|</span>
                <button
                    type="button"
                    @click="toggleAll(false)"
                    class="text-[11px] font-medium text-slate-500 hover:underline dark:text-slate-400"
                >
                    Hapus semua
                </button>
            </div>

            {{-- Checkbox List --}}
            <div class="mt-3 space-y-1">
                <template x-for="(col, key) in columns" :key="key">
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5
                                  transition hover:bg-slate-50 dark:hover:bg-white/[0.04]">
                        <input
                            type="checkbox"
                            x-model="col.checked"
                            class="h-4 w-4 rounded border-slate-300 text-primary-600
                                   focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800"
                        >
                        <span
                            class="select-none text-[12px] text-slate-700 dark:text-slate-200"
                            x-text="col.label"
                        ></span>
                    </label>
                </template>
            </div>

            {{-- Filter info badge --}}
            @if ($this->selectedYear || $this->selectedMonth || $this->selectedJenisInsiden || $this->selectedStatus)
                <div class="mt-3 flex flex-wrap gap-1.5 rounded-lg border border-amber-200 bg-amber-50
                            p-2 dark:border-amber-800/50 dark:bg-amber-900/20">
                    <span class="text-[10px] font-medium text-amber-700 dark:text-amber-400">
                        Filter aktif:
                    </span>
                    @if ($this->selectedYear)
                        <span class="inline-flex rounded bg-amber-100 px-1.5 py-0.5 text-[10px]
                                     font-medium text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                            {{ $this->selectedYear }}
                        </span>
                    @endif
                    @if ($this->selectedMonth)
                        <span class="inline-flex rounded bg-amber-100 px-1.5 py-0.5 text-[10px]
                                     font-medium text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                            {{ $this->getMonthOptions()[(int) $this->selectedMonth] ?? $this->selectedMonth }}
                        </span>
                    @endif
                    @if ($this->selectedJenisInsiden)
                        <span class="inline-flex rounded bg-amber-100 px-1.5 py-0.5 text-[10px]
                                     font-medium text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                            {{ $this->getIncidentTypeOptions()[$this->selectedJenisInsiden] ?? $this->selectedJenisInsiden }}
                        </span>
                    @endif
                    @if ($this->selectedStatus)
                        <span class="inline-flex rounded bg-amber-100 px-1.5 py-0.5 text-[10px]
                                     font-medium text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                            {{ $this->getStatusOptions()[$this->selectedStatus] ?? $this->selectedStatus }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="mt-4 flex items-center justify-end gap-2">
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px]
                           font-medium text-slate-700 transition hover:bg-slate-50
                           dark:border-white/10 dark:bg-slate-800 dark:text-slate-300
                           dark:hover:bg-white/[0.06]"
                >
                    Batal
                </button>

                {{-- Form POST: filter sebagai hidden input, kolom di-inject Alpine sebelum submit --}}
                <form
                    method="POST"
                    action="{{ route('export.investigated-reports') }}"
                    x-ref="exportForm"
                >
                    @csrf
                    <input type="hidden" name="year"          value="{{ $this->selectedYear }}">
                    <input type="hidden" name="month"         value="{{ $this->selectedMonth }}">
                    <input type="hidden" name="jenis_insiden" value="{{ $this->selectedJenisInsiden }}">
                    <input type="hidden" name="status"        value="{{ $this->selectedStatus }}">

                    {{-- Placeholder untuk kolom terpilih (di-inject Alpine saat submit) --}}
                    <div x-ref="columnsContainer"></div>

                    <button
                        type="button"
                        :disabled="!anyChecked"
                        @click="
                            $refs.columnsContainer.innerHTML = '';
                            Object.entries(columns).forEach(([key, col]) => {
                                if (col.checked) {
                                    const inp = document.createElement('input');
                                    inp.type  = 'hidden';
                                    inp.name  = 'columns[]';
                                    inp.value = key;
                                    $refs.columnsContainer.appendChild(inp);
                                }
                            });
                            $refs.exportForm.submit();
                            open = false;
                        "
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5
                               text-[11px] font-medium text-white transition hover:bg-emerald-700
                               disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-3.5 w-3.5" />
                        Download
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>