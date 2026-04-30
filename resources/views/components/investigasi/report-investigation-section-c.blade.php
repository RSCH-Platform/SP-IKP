@props(['laporan'])

<section class="mb-6 print:block">
    <div class="bg-white print:block">
        <x-section-header title="BAGIAN C: FLOWCHART ANALISA 5 WHY" />
        @if($laporan->problems && $laporan->problems->count() > 0)
        <div class="flex flex-col items-center mb-12">
            <div class="px-6 py-3 rounded-lg bg-slate-800 text-white text-sm font-semibold text-center shadow">
                INSIDEN
                <div class="text-xs text-slate-300 mt-1">
                    {{ $laporan->deskripsi_kategori_insiden ?? 'Insiden' }}
                </div>
            </div>
            <div class="w-px h-8 bg-slate-300"></div>
            <div class="relative w-full max-w-5xl">
                <div class="absolute top-0 left-0 right-0 h-px bg-slate-300"></div>
                <div class="flex justify-between">
                    @foreach($laporan->problems as $idx => $problem)
                    <div class="flex flex-col items-center w-full">
                        <div class="w-px h-6 bg-slate-300"></div>
                        <div class="w-56 p-2 rounded-lg border border-slate-300 bg-white shadow-sm text-center">
                            <span @class([ 'text-[10px] px-2 rounded font-semibold' , 'bg-red-100 text-red-700'=> strtoupper($problem->problem_type) === 'CMP', 'bg-orange-100 text-orange-700' => strtoupper($problem->problem_type) === 'SDP', 'bg-slate-100 text-slate-600' => ! in_array(strtoupper($problem->problem_type), ['CMP', 'SDP']) ])>
                                {{ $problem->problem_type }}
                            </span>
                            <p class="text-xs font-semibold text-slate-800 mt-1">Masalah {{ $idx + 1 }}</p>
                            <p class="text-[10px] text-slate-600 mt-1 leading-snug">{{ $problem->problem_description }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-8">
            @foreach($laporan->problems as $problemIdx => $problem)
            <div class="break-inside-avoid">
                <div class="bg-white border border-slate-300 p-4 mb-4 rounded shadow-sm">
                    <p class="text-xs font-semibold text-slate-900 uppercase">Masalah #{{ $problemIdx + 1 }}: {{ $problem->problem_type }}</p>
                    <p class="text-sm text-slate-700 mt-2">{{ $problem->problem_description }}</p>
                </div>

                @if($problem->whys->count() > 0)
                <div class="mb-4 p-4 bg-white border-l-4 border-l-slate-400 border border-slate-200 rounded">
                    <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Penyebab Langsung</p>
                    <div class="space-y-3">
                        @foreach($problem->whys->sortBy('why_level') as $why)
                        <div class="bg-slate-50 p-3 rounded border border-slate-200">
                            <p class="text-xs font-semibold text-slate-700">Level {{ $why->why_level }}</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $why->problem_statement }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-center mb-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M12 5v10m0 0l-3-3m3 3l3-3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                @endif

                <div class="mb-4 p-4 bg-white border-l-4 border-l-yellow-400 border border-slate-200 rounded">
                    <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Akar Masalah (Root Cause)</p>
                    <div class="bg-slate-50 p-3 rounded border border-slate-200">
                        <p class="text-sm text-slate-800">
                            @if($problem->whys->count() > 0)
                            {{ $problem->whys->sortBy('why_level')->last()?->problem_statement ?? '-' }}
                            @else
                            -
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex justify-center mb-3">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="2" d="M12 5v10m0 0l-3-3m3 3l3-3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                @if($problem->contributors->count() > 0)
                <div class="mb-4 p-4 bg-white border-l-4 border-l-purple-500 border border-slate-200 rounded">
                    <p class="text-xs font-semibold text-slate-800 uppercase mb-3 border-b border-slate-200 pb-2">Faktor Kontributor</p>
                    @php
                    $contributorsByCategory = $problem->contributors->groupBy(function ($contrib) {
                    return $contrib->category?->id ?? 'uncategorized';
                    });
                    @endphp

                    @foreach($contributorsByCategory as $categoryId => $contributors)
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-slate-700 mb-2">{{ $contributors->first()->category?->name ?? 'Lainnya' }}</p>
                        <div class="grid gap-2 md:grid-cols-2">
                            @foreach($contributors as $contrib)
                            <div class="rounded border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                                {{ $contrib->description ?? '-' }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
            <p class="text-xs text-yellow-800">Belum ada masalah/problem yang diidentifikasi untuk laporan ini.</p>
        </div>
        @endif
    </div>
    </div>