<x-filament-widgets::widget class="printable-widget">
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
    <div
        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-950">

        {{-- Header --}}
        <div class="border-b border-slate-200 px-4 py-3 dark:border-white/10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold leading-5 text-slate-900 dark:text-white">
                        Pemantauan Investigasi
                    </h3>

                    <p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400">
                        Pantau status laporan insiden dalam proses investigasi.
                    </p>
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

        <div class="p-3">
            <x-report-table
                tableClass="min-w-[1320px] border-separate border-spacing-0"
                scrollClass="max-w-full overflow-x-auto rounded-lg border border-slate-200 dark:border-white/10"
            >
                <x-slot:colgroup>
                    <colgroup>
                        <col class="w-[8%]">
                        <col class="w-[27%]">
                        <col class="w-[11%]">
                        <col class="w-[13%]">
                        <col class="w-[20.5%]">
                        <col class="w-[20.5%]">
                    </colgroup>
                </x-slot:colgroup>

                <x-slot:header>
                    <tr class="bg-slate-50 text-slate-700 dark:bg-white/[0.04] dark:text-slate-200">
                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Tanggal
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Insiden
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Jenis
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Unit
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Akar Masalah
                        </x-report-table.th>

                        <x-report-table.th class="{{ $thPadding }} text-left {{ $textSize }} font-semibold uppercase tracking-wide border-b border-slate-200 dark:border-white/10">
                            Rekomendasi
                        </x-report-table.th>
                    </tr>
                </x-slot:header>

                @forelse ($rows ?? [] as $group)
                    @php
                        $base = $group['base'] ?? [];
                        $problems = $group['problems'] ?? [];
                        $rowspan = count($problems) ?: 1;
                    @endphp

                    @foreach ($problems as $i => $p)
                        <tr class="align-top transition hover:bg-slate-50/80 dark:hover:bg-white/[0.035]">
                            @if ($i === 0)
                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top whitespace-nowrap border-b border-slate-200 font-medium text-slate-600 dark:border-white/10 dark:text-slate-300"
                                >
                                    {{ $base['tanggal_insiden'] ?? '-' }}
                                </x-report-table.td>

                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 font-medium leading-5 text-slate-900 dark:border-white/10 dark:text-white"
                                >
                                    <div
                                        class="line-clamp-2"
                                        title="{{ $base['deskripsi_kategori_insiden'] ?? '-' }}"
                                    >
                                        {{ $base['deskripsi_kategori_insiden'] ?? '-' }}
                                    </div>
                                </x-report-table.td>

                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                                >
                                    <div
                                        class="line-clamp-2 break-words"
                                        title="{{ $base['jenis_insiden'] ?? '-' }}"
                                    >
                                        {{ $base['jenis_insiden'] ?? '-' }}
                                    </div>
                                </x-report-table.td>

                                <x-report-table.td
                                    rowspan="{{ $rowspan }}"
                                    class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                                >
                                    <div
                                        class="line-clamp-2 break-words"
                                        title="{{ $base['unit_kerja'] ?? '-' }}"
                                    >
                                        {{ $base['unit_kerja'] ?? '-' }}
                                    </div>
                                </x-report-table.td>
                            @endif

                            <x-report-table.td
                                class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                            >
                                <div
                                    class="line-clamp-3 break-words"
                                    title="{{ $p['akar_masalah'] ?? '-' }}"
                                >
                                    {{ $p['akar_masalah'] ?? '-' }}
                                </div>
                            </x-report-table.td>

                            <x-report-table.td
                                class="{{ $tdPadding }} {{ $textSize }} align-top border-b border-slate-200 leading-5 text-slate-600 dark:border-white/10 dark:text-slate-300"
                            >
                                <div
                                    class="line-clamp-3 break-words"
                                    title="{{ $p['rekomendasi'] ?? '-' }}"
                                >
                                    {{ $p['rekomendasi'] ?? '-' }}
                                </div>
                            </x-report-table.td>
                        </tr>
                    @endforeach
                @empty
                    <x-report-table.empty
                        :colspan="6"
                        title="Belum ada data investigasi"
                        description="Tidak ada laporan yang sesuai dengan filter yang dipilih."
                    />
                @endforelse
            </x-report-table>


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
                tanggal_insiden:            { label: 'Tanggal Insiden',  checked: true },
                deskripsi_kategori_insiden: { label: 'Judul Insiden',    checked: true },
                jenis_insiden:              { label: 'Jenis Insiden',    checked: true },
                unit_kerja:                 { label: 'Unit Kerja',       checked: true },
                status:                     { label: 'Status',           checked: true },
                akar_masalah:               { label: 'Akar Masalah',     checked: true },
                rekomendasi:                { label: 'Rekomendasi',      checked: true },
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