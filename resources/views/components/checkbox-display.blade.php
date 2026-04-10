@props([
'checked' => false,
'label' => '',
'disabled' => false,
])

<label class="flex items-center gap-2 {{ $disabled ? 'cursor-not-allowed' : '' }}">
    <span class="relative flex items-center justify-center w-4 h-4 rounded-sm border {{ $checked ? 'bg-blue-500 border-blue-500' : 'border-slate-400 bg-white' }}">
        @if($checked)
        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        @endif
    </span>
    <span class="text-xs text-slate-700 leading-none">{{ $label }}</span>
</label>