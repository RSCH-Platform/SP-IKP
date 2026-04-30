@props(['laporan'])

<section class="mb-6 print:block">
    <div class="bg-white space-y-2 print:block">
        <x-section-header title="BAGIAN C: FLOWCHART ANALISA 5 WHY" />
        @if($laporan->problems && $laporan->problems->count() > 0)
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mb-6">
            <div class="flex flex-col items-center">
                <div class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold text-center">
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
                            <div class="w-56 p-2 rounded-lg border border-slate-200 bg-white text-center">
                                <span @class([ 'inline-flex items-center justify-center px-2 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide' , 'bg-red-100 text-red-700'=> strtoupper($problem->problem_type) === 'CMP', 'bg-orange-100 text-orange-700' => strtoupper($problem->problem_type) === 'SDP', 'bg-slate-100 text-slate-600' => ! in_array(strtoupper($problem->problem_type), ['CMP', 'SDP']) ])>
                                    {{ strtoupper($problem->problem_type) }}
                                </span>
                                <p class="text-xs font-semibold text-slate-800 mt-2">Masalah {{ $idx + 1 }}</p>
                                <p class="text-[10px] text-slate-600 mt-1 leading-snug">{{ $problem->problem_description }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">

            @forelse($laporan->problems as $problemIdx => $problem)
            <div class="break-inside-avoid border border-slate-300 rounded-lg overflow-hidden">

                {{-- HEADER MASALAH --}}
                <div class="flex items-center justify-between bg-slate-100 px-3 py-2 border-b border-slate-300">
                    <div class="grid grid-cols-[auto_1fr] gap-x-2 items-start">
                        <p class="text-xs uppercase tracking-wide text-slate-600 font-medium whitespace-nowrap">
                            Masalah #{{ $problemIdx + 1 }}:
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-600 font-semibold">
                            {{ $problem->problem_description ?? '-' }}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded
                {{ strtoupper($problem->problem_type) === 'CMP' ? 'bg-red-100 text-red-700' :
                   (strtoupper($problem->problem_type) === 'SDP' ? 'bg-orange-100 text-orange-700' : 'bg-slate-200 text-slate-600') }}">
                        {{ strtoupper($problem->problem_type) ?? '-' }}
                    </span>
                </div>

                {{-- WHY CHAIN --}}
                <div class="px-3 pb-3 mt-2">
                    <p class="text-[10px] uppercase text-slate-500 font-medium mb-2">
                        Analisis Penyebab (5 Why)
                    </p>

                    @if($problem->whys->count() > 0)
                    <div class="border-l-2 border-slate-300 pl-3 space-y-3">
                        @foreach($problem->whys->sortBy('why_level') as $why)
                        <div class="relative">
                            <div class="absolute -left-[5px] top-1 w-2 h-2 bg-slate-400 rounded-full"></div>

                            <p class="text-[10px] uppercase ml-2 text-slate-400 font-medium">
                                Why {{ $why->why_level }}
                            </p>

                            <p class="text-[10px] ml-2 text-slate-600 leading-snug">
                                {{ $why->problem_statement ?? '-' }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-slate-500 italic border border-dashed border-slate-300 rounded p-2">
                        Belum ada analisis 5 Why untuk masalah ini.
                    </div>
                    @endif
                </div>

                {{-- ROOT CAUSE --}}
                <div class="px-3 pb-3">
                    <div class="border-1 border-red-400 bg-red-50 p-3 rounded">
                        <p class="text-[10px] uppercase text-red-600 font-semibold mb-1">
                            Root Cause
                        </p>

                        <p class="text-sm font-semibold text-red-800 leading-snug">
                            @if($problem->whys->count() > 0)
                            {{ $problem->whys->sortBy('why_level')->last()?->problem_statement ?? '-' }}
                            @else
                            <span class="italic text-red-600">Belum dapat ditentukan karena analisis Why belum tersedia.</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- CONTRIBUTORS --}}
                <div class="px-3 pb-4">
                    <p class="text-[10px] uppercase text-slate-500 font-medium mb-2">
                        Faktor Kontributor
                    </p>

                    @if($problem->contributors->count() > 0)

                    @php
                    $contributorsByCategory = $problem->contributors->groupBy(function ($contrib) {
                    return $contrib->category_name ?: 'Lainnya';
                    });
                    @endphp

                    <div class="space-y-4">
                        @foreach($contributorsByCategory as $categoryLabel => $contributors)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <p class="text-xs font-semibold text-slate-700">
                                    Kategori: {{ $categoryLabel }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                @foreach($contributors as $contrib)
                                <div class="grid grid-cols-2 gap-2 mb-2 bg-white border border-slate-300 p-1 items-center text-left">
                                    <div class="border border-slate-200 p-2">
                                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Komponen</p>
                                        <p class="text-xs text-slate-800">{{ $contrib->component_name ?: '-'  }}</p>
                                    </div>
                                    <div class="border border-slate-200 p-2">
                                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Sub Komponen</p>
                                        <p class="text-xs text-slate-800">{{ $contrib->sub_component_name ?: '-' }}</p>
                                    </div>
                                </div>
                                <x-long-text-display label="Deskripsi" :text="$contrib->description ?? 'Belum ada justifikasi grading'" />
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @else
                    <div class="text-xs text-slate-500 italic border border-dashed border-slate-300 rounded p-2">
                        Tidak ada faktor kontributor yang diidentifikasi.
                    </div>
                    @endif
                </div>

                {{-- RECOMMENDATIONS --}}
                <div class="px-3 pb-4">
                    <p class="text-[10px] uppercase text-slate-500 font-medium mb-2">
                        Rekomendasi Perbaikan
                    </p>

                    @if($problem->recommendations->count() > 0)
                    <div class="space-y-3">
                        @foreach($problem->recommendations as $recommendation)
                        <div class="bg-white border border-slate-200 rounded-lg p-3">
                            <div class="grid grid-cols-4 gap-2 mb-2 bg-white items-center text-left">
                                <div class="border border-slate-200 p-2">
                                    <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Prioritas</p>
                                    <p @class([ 'text-xs font-bold uppercase' , 'text-red-700'=> ($recommendation->priority ?? '') === 'high',
                                        'text-amber-700' => ($recommendation->priority ?? '') === 'medium',
                                        'text-emerald-700' => ($recommendation->priority ?? '') === 'low',
                                        'text-slate-800' => !in_array(($recommendation->priority ?? ''), ['high', 'medium', 'low']),
                                        ])>
                                        {{ ucfirst($recommendation->priority ?? 'normal') }}
                                    </p>
                                </div>
                            </div>
                            <x-long-text-display label="Rekomendasi" :text="$recommendation->recommendation_text ?? 'Belum ada rekomendasi'" />
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-slate-500 italic border border-dashed border-slate-300 rounded p-2">
                        Belum ada rekomendasi.
                    </div>
                    @endif
                </div>

                {{-- ACTIONS --}}
                <div class="px-3 pb-4">
                    <p class="text-[10px] uppercase text-slate-500 font-medium mb-2">
                        Tindakan
                    </p>

                    @if($problem->actions->count() > 0)
                    <div class="space-y-3">
                        @foreach($problem->actions as $action)
                        <div class="bg-white border border-slate-200 rounded-lg p-3">
                            <div class="flex flex-col gap-2">
                                <p class="text-[12px] font-semibold text-slate-800">{{ $action->action_text ?? '-' }}</p>
                                <div class="grid grid-cols-3 gap-2 mb-2 bg-white border border-slate-300 p-1 items-center text-left">
                                    <div class="border border-slate-200 p-2">
                                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Penanggung Jawab</p>
                                        <p class="text-xs text-slate-800">{{ $action->responsible_person ?: '-'  }}</p>
                                    </div>
                                    <div class="border border-slate-200 p-2">
                                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Deadline</p>
                                        <p class="text-xs text-slate-800">{{ $action->deadline ?: '-' }}</p>
                                    </div>
                                    <div class="border border-slate-200 p-2">
                                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Status</p>
                                        <p class="text-xs text-slate-800">{{ $action->status ?: 'Pending' }}</p>
                                    </div>
                                </div>
                                @php
                                $actionMedia = $action->media->where('collection_name', 'action_evidence');
                                @endphp
                                @if($actionMedia->isNotEmpty())
                                <div class="mt-3 border-t border-slate-200 pt-3">
                                    <p class="text-xs font-semibold text-slate-700 mb-2">Bukti</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($actionMedia as $media)
                                        <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-slate-100 text-slate-700 rounded border border-slate-200 hover:bg-slate-200 transition">
                                            @if(str_contains($media->mime_type, 'image'))
                                            🖼️
                                            @elseif($media->mime_type === 'application/pdf')
                                            📄
                                            @else
                                            📎
                                            @endif
                                            {{ $media->name ?? basename($media->file_name ?? '') }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-xs text-slate-500 italic border border-dashed border-slate-300 rounded p-2">
                        Belum ada tindakan.
                    </div>
                    @endif
                </div>

            </div>

            @empty
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-sm text-yellow-800 font-medium">
                    Belum ada masalah yang dianalisis.
                </p>
                <p class="text-xs text-yellow-700 mt-1">
                    Silakan tambahkan data masalah terlebih dahulu untuk menampilkan analisis 5 Why.
                </p>
            </div>
            @endforelse

        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-xs text-yellow-800">Belum ada masalah/problem yang diidentifikasi untuk laporan ini.</p>
        </div>
        @endif
    </div>
</section>