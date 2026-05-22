@props(['action' => [], 'isLast' => false])

@php
    $status = $action['status'] ?? 'pending';
@endphp

<div wire:key="action-{{ $action['id'] }}" class="flex gap-4">
    <div class="flex flex-col items-center">
        <span @class([
            'mt-4 h-3 w-3 rounded-full ring-4',
            $action['status_dot_classes'] ?? 'bg-gray-500 dark:bg-gray-400' => true,
            'ring-yellow-100 dark:ring-yellow-950/40' => $status === 'pending',
            'ring-blue-100 dark:ring-blue-950/40' => $status === 'ongoing',
            'ring-green-100 dark:ring-green-950/40' => $status === 'completed',
        ])></span>

        @if (! $isLast)
            <span class="mt-2 h-full w-px bg-slate-200 dark:bg-white/10"></span>
        @endif
    </div>

    <div @class(['min-w-0 flex-1 rounded-xl border px-4 py-3 transition hover:bg-white hover:shadow-sm dark:hover:bg-slate-900', $action['status_panel_classes'] ?? 'border-slate-200 bg-slate-50 text-slate-700 dark:border-white/10 dark:bg-slate-950 dark:text-slate-300'])>
        <div class="flex justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold leading-6 text-slate-900 dark:text-white">
                    {{ $action['text'] }}
                </p>

                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ $action['responsible_person'] ?: '-' }}</span>
                    <span>·</span>
                    <span>{{ $action['deadline'] ?: '-' }}</span>
                    <span>·</span>
                    <span>{{ $action['media_count'] }} bukti</span>
                </div>
            </div>

            <details class="group relative shrink-0">
                <summary @class([
                    'flex cursor-pointer list-none items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-sm transition',
                    'border-yellow-200 bg-yellow-50 text-yellow-700 dark:border-yellow-900/40 dark:bg-yellow-950/30 dark:text-yellow-300' => $status === 'pending',
                    'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300' => $status === 'ongoing',
                    'border-green-200 bg-green-50 text-green-700 dark:border-green-900/40 dark:bg-green-950/30 dark:text-green-300' => $status === 'completed',
                ])>
                    <span @class([
                        'h-2 w-2 rounded-full',
                        'bg-yellow-500 dark:bg-yellow-400' => $status === 'pending',
                        'bg-blue-500 dark:bg-blue-400' => $status === 'ongoing',
                        'bg-green-500 dark:bg-green-400' => $status === 'completed',
                    ])></span>

                    <span>
                        {{ $action['status_label'] ?? ucfirst((string) $status) }}
                    </span>

                    <x-heroicon-m-chevron-down class="text-slate-400 transition group-open:rotate-180 dark:text-slate-500" style="width: 13px; height: 13px; flex-shrink: 0;" />
                </summary>

                <div class="absolute right-0 z-20 mt-2 w-36 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-slate-900">
                    @foreach (['pending' => 'Pending', 'ongoing' => 'Ongoing', 'completed' => 'Selesai'] as $statusKey => $label)
                        <button
                            type="button"
                            wire:click="updateActionStatus({{ $action['id'] }}, '{{ $statusKey }}')"
                            @class([
                                'flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-medium transition',
                                'bg-slate-50 text-slate-900 dark:bg-white/5 dark:text-white' => $status === $statusKey,
                                'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' => $status !== $statusKey,
                            ])
                        >
                            <span @class([
                                'h-2 w-2 rounded-full',
                                'bg-yellow-500 dark:bg-yellow-400' => $statusKey === 'pending',
                                'bg-blue-500 dark:bg-blue-400' => $statusKey === 'ongoing',
                                'bg-green-500 dark:bg-green-400' => $statusKey === 'completed',
                            ])></span>

                            <span>{{ $label }}</span>

                            @if ($status === $statusKey)
                                <span class="ml-auto text-slate-400">✓</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </details>
        </div>
    </div>
</div>