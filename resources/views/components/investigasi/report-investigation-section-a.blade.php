@props(['investigationDataGrouped'])

<div class="break-inside-avoid mb-6">
    <x-section-header title="BAGIAN A: Pengumpulan Data Investigasi" />
    <div class="bg-white border border-slate-300 p-2 space-y-4">
        @forelse ($investigationDataGrouped as $categoryKey => $categoryData)
        <div class="border-l-4 border-blue-500 pl-4 py-2">
            <h3 class="text-sm font-bold text-slate-800 uppercase mb-3">
                {{ $categoryData['label'] }}
            </h3>

            <div class="space-y-3">
                @forelse ($categoryData['items'] as $item)
                <div class="bg-slate-50 border border-slate-200 rounded p-3">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-700 mb-1">Sumber: <span class="font-normal">{{ $item->sumber ?? '-' }}</span></p>
                            <p class="text-xs text-slate-600">Lokasi: {{ $item->lokasi ?? '-' }}</p>
                        </div>
                        <p class="text-xs text-slate-500">{{ $item->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
                    </div>

                    <div class="mt-3 bg-white border border-slate-200 rounded p-2">
                        <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-1">Hasil Investigasi:</p>
                        <p class="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap break-words">{{ $item->hasil ?? '-' }}</p>
                    </div>

                    @if($item->file_path)
                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded p-2 flex items-center">
                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM18.5 1a2.5 2.5 0 00-2.5 2.5V4H5V3.5A2.5 2.5 0 002.5 1h-1a2.5 2.5 0 00-2.5 2.5v12A2.5 2.5 0 001.5 18h1A2.5 2.5 0 005 15.5V15h8v.5a2.5 2.5 0 001.5 2.5h1a2.5 2.5 0 002.5-2.5v-12A2.5 2.5 0 0018.5 1z" />
                        </svg>
                        <span class="text-xs text-blue-700 font-medium truncate">{{ basename($item->file_path) }}</span>
                    </div>
                    @endif

                    @if($item->creator)
                    <div class="mt-2 text-xs text-slate-600 border-t border-slate-200 pt-2">
                        <p>Diinput oleh: <span class="font-medium">{{ $item->creator->name ?? '-' }}</span></p>
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-xs text-slate-500 italic">Tidak ada data {{ strtolower($categoryData['label']) }}</p>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
            <p class="text-xs text-yellow-800">Belum ada data pengumpulan investigasi untuk laporan ini.</p>
        </div>
        @endforelse
    </div>
</div>