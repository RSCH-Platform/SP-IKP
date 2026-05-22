@props(['recommendation' => []])

<div @class([
    'rounded-xl border bg-white p-4 shadow-sm transition hover:shadow-md dark:bg-slate-900',
    'border-l-4 border-l-yellow-400 border-slate-200 dark:border-slate-800' => ($recommendation['priority'] ?? null) === 'high',
    'border-l-4 border-l-blue-400 border-slate-200 dark:border-slate-800' => ($recommendation['priority'] ?? null) === 'medium',
    'border-l-4 border-l-slate-300 border-slate-200 dark:border-slate-800' => ! in_array(($recommendation['priority'] ?? null), ['high', 'medium'], true),
])>
    <div class="mb-2">
        <span @class([
            'rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase',
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300' => ($recommendation['priority'] ?? null) === 'high',
            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' => ($recommendation['priority'] ?? null) === 'medium',
            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => ! in_array(($recommendation['priority'] ?? null), ['high', 'medium'], true),
        ])>
            {{ strtoupper($recommendation['priority'] ?? 'normal') }}
        </span>
    </div>

    <p class="text-sm leading-6 text-slate-700 dark:text-slate-200">
        {{ $recommendation['text'] }}
    </p>
</div>