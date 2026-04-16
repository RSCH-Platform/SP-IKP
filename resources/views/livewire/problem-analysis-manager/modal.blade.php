@props([
'openState',
'sizeClass' => 'md:w-2/3 lg:w-1/2',
'closeAction' => '$wire.resetForm()',
])

<div x-show="{{ $openState }}" x-cloak wire:ignore.self class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="{{ $openState }} = false; {{ $closeAction }}"></div>
    <div class="relative w-full {{ $sizeClass }} max-w-screen-lg mx-4 p-4 border rounded-lg bg-white dark:bg-slate-800">
        {{ $slot }}
    </div>
</div>