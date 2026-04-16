<div class="px-6 py-5 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 rounded-t-xl">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span
                @class([ 'px-2.5 py-1 text-xs font-semibold rounded-md' , 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'=> $problem['problem_type'] === 'CMP',
                'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' => $problem['problem_type'] !== 'CMP',
                ])
                >
                {{ $problem['problem_type'] }}
            </span>

            <span class="text-sm text-gray-500 dark:text-gray-400 italic">
                {{ $problem['problem_type'] === 'CMP' ? 'Conformance Issue' : 'Management Issue' }}
            </span>
        </div>
    </div>

    <p class="mt-3 text-base font-semibold text-gray-800 dark:text-gray-100 leading-relaxed">
        {{ $problem['problem_description'] }}
    </p>

    <div class="flex gap-2 flex-shrink-0 mt-3">
        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded font-semibold whitespace-nowrap">
            📊 {{ count($problem['whys'] ?? []) }} WHY
        </span>
        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded font-semibold whitespace-nowrap">
            🎯 {{ count($problem['contributors'] ?? []) }} Faktor
        </span>
        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded font-semibold whitespace-nowrap">
            💡 {{ count($problem['recommendations'] ?? []) }} Rekom
        </span>
        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded font-semibold whitespace-nowrap">
            ✅ {{ count($problem['actions'] ?? []) }} Aksi
        </span>
    </div>
</div>