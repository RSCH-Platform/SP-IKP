<!-- FILTERS -->
<div class="flex flex-wrap items-end gap-3">
    <div class="min-w-[140px]">
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Tahun
        </label>
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="year">
                @foreach($this->getAvailableYears() as $y)
                <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <div class="min-w-[160px]">
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Grouping
        </label>
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="grouping">
                <option value="quarter">Quarterly</option>
                <option value="semester">Semester</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <div class="min-w-[140px]">
        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Periode
        </label>
        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="period">
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
</div>