<!-- FILTER TOOLBAR -->
<div class="flex flex-wrap items-end gap-2">

    <!-- Tahun -->
    <div class="w-28">
        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">
            Tahun
        </label>

        <x-filament::input.wrapper>
            <x-filament::input.select
                wire:model.live="year"
                class="!text-sm"
            >
                @foreach($this->getAvailableYears() as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <!-- Grouping -->
    <div class="w-32">
        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">
            Grouping
        </label>

        <x-filament::input.wrapper>
            <x-filament::input.select
                wire:model.live="grouping"
                class="!text-sm"
            >
                <option value="quarter">Quarterly</option>
                <option value="semester">Semester</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <!-- Periode -->
    <div class="w-24">
        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">
            Periode
        </label>

        <x-filament::input.wrapper>
            <x-filament::input.select
                wire:model.live="period"
                class="!text-sm"
            >
                @if($this->grouping === 'quarter')
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}">Q{{ $i }}</option>
                    @endfor
                @else
                    @for($i = 1; $i <= 2; $i++)
                        <option value="{{ $i }}">S{{ $i }}</option>
                    @endfor
                @endif
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <!-- Tampilan -->
    <div class="w-36">
        <label class="mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400">
            Tampilan
        </label>

        <x-filament::input.wrapper>
            <x-filament::input.select
                wire:model.live="breakdownMode"
                class="!text-sm"
            >
                <option value="period">Akumulasi Periode</option>
                <option value="monthly">Per Bulan</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <!-- Actions -->
    <div class="ml-auto flex items-center gap-1 border-l border-gray-200 pl-2 dark:border-gray-700">
        <x-filament::button
            wire:click="exportCSV"
            color="gray"
            size="sm"
            icon="heroicon-o-arrow-down-tray"
            tooltip="Export CSV"
        >
            Export
        </x-filament::button>

        <x-filament::button
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
            color="gray"
            size="sm"
            icon="heroicon-o-printer"
            tooltip="Print PDF"
        >
            Print
        </x-filament::button>
    </div>

</div>