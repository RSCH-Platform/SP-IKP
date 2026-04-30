@props(['laporan', 'investigationDataGrouped'])

<div class="mb-6">
    <div class="grid grid-cols-4 gap-2 mb-6 bg-white border border-slate-300 p-1 items-center text-left">
        <div class="border border-slate-200 p-2">
            <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">No. Laporan</p>
            <p class="text-xs text-slate-800">{{ $laporan->nomor_laporan ?? '-' }}</p>
        </div>
        <div class="border border-slate-200 p-2">
            <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Unit Kerja</p>
            <p class="text-xs text-slate-800">{{ $laporan->unit_kerja ?? '-' }}</p>
        </div>
        <div class="border border-slate-200 p-2">
            <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Investigator</p>
            <p class="text-xs text-slate-800">{{ $laporan->investigationStarter->name ?? '-' }}</p>
        </div>
        <div class="border border-slate-200 p-2">
            <p class="text-xs uppercase tracking-wide text-slate-700 font-medium mb-0.5">Tanggal Investigasi</p>
            <p class="text-xs text-slate-800">{{ $laporan->investigation_started_at?->translatedFormat('d F Y') ?? '-' }} - {{ $laporan->investigation_ended_at?->translatedFormat('d F Y') ?? '-' }}</p>
        </div>
    </div>

    <x-section-header title="BAGIAN A: PENGUMPULAN DATA" />

    @php
    $categoryOrder = ['interview', 'review_dokumen', 'observasi'];
    $orderedInvestigationDataGrouped = collect($investigationDataGrouped)
    ->sortBy(function ($categoryData, $categoryKey) use ($categoryOrder) {
    $index = array_search($categoryKey, $categoryOrder, true);
    return $index === false ? count($categoryOrder) : $index;
    })
    ->toArray();
    @endphp
    <div class="bg-white space-y-2 print:block">
        @forelse ($orderedInvestigationDataGrouped as $categoryKey => $categoryData)
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-2">
            <h3 class="text-xs uppercase tracking-wide text-slate-700 font-semibold mb-3">{{ $categoryData['label'] }}</h3>

            <div class="space-y-2">
                @forelse ($categoryData['items'] as $item)
                <div class="bg-white border border-slate-200 rounded-lg p-2">
                    @php
                    $sourceLabel = 'Sumber';
                    $resultLabel = 'Hasil Investigasi';
                    $showLocation = false;

                    if ($categoryKey === 'interview') {
                    $sourceLabel = 'Narasumber';
                    $resultLabel = 'Hasil Interview';
                    } elseif ($categoryKey === 'review_dokumen') {
                    $sourceLabel = 'Nama Dokumen';
                    $resultLabel = 'Hasil Review';
                    $showLocation = false;
                    } elseif ($categoryKey === 'observasi') {
                    $sourceLabel = 'Lokasi';
                    $resultLabel = 'Hasil Observasi';
                    $showLocation = true;
                    }
                    @endphp

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <p class="report-field-label">{{ $sourceLabel }}</p>
                            <p class="report-field-title">{{ $item->sumber ?? ($categoryKey === 'observasi' ? ($item->lokasi ?? '-') : '-') }}</p>
                        </div>

                    </div>

                    @if($categoryKey === 'review_dokumen')
                    <div class="hidden">
                        <input type="hidden" name="investigation_location_{{ $item->id }}" value="{{ $item->lokasi ?? '' }}" />
                    </div>
                    @endif

                    <p class="mt-4 report-field-label">{{ $resultLabel }}</p>
                    <div class="mt-1 bg-slate-50 border border-slate-200 rounded-md p-1">
                        <p class="text-xs text-slate-800 leading-relaxed whitespace-pre-wrap break-words">{{ $item->hasil ?? '-' }}</p>
                    </div>

                    @if($item->file_path)
                    @php
                    $filePath = $item->file_path;
                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif']);
                    $isPdf = $fileExtension === 'pdf';
                    $isRemoteUrl = preg_match('/^https?:\/\//i', $filePath) === 1;
                    if ($isRemoteUrl && preg_match('/^(https?:\/\/[^\/]+)::/', $filePath)) {
                    $filePath = preg_replace('/^(https?:\/\/[^\/]+)::/', '$1:', $filePath);
                    }
                    $fileUrl = $isRemoteUrl
                    ? $filePath
                    : route('investigasi-file', ['encryptedPath' => encrypt($filePath)]);
                    @endphp
                    <div class="mt-3 bg-white border border-slate-200 rounded-lg p-3">
                        <p class="report-field-label">Lampiran</p>
                        @if($isImage)
                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="block mt-2">
                            <img src="{{ $fileUrl }}" alt="{{ basename($filePath) }}" class="max-w-full rounded-lg border border-slate-200 object-contain" loading="lazy" />
                        </a>
                        <p class="text-xs text-slate-700 font-medium truncate mt-2">{{ basename($filePath) }}</p>
                        @elseif($isPdf)
                        <p class="text-xs mt-2">
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800 underline">
                                {{ basename($filePath) }}
                            </a>
                        </p>
                        @else
                        <div class="mt-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM18.5 1a2.5 2.5 0 00-2.5 2.5V4H5V3.5A2.5 2.5 0 002.5 1h-1a2.5 2.5 0 00-2.5 2.5v12A2.5 2.5 0 001.5 18h1A2.5 2.5 0 005 15.5V15h8v.5a2.5 2.5 0 001.5 2.5h1a2.5 2.5 0 002.5-2.5v-12A2.5 2.5 0 0018.5 1z" />
                            </svg>
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="text-xs text-slate-700 font-medium truncate underline">
                                {{ basename($filePath) }}
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($item->creator)
                    <div class="mt-3 text-end border-t border-slate-200 pt-3 text-xs text-slate-600">
                        <p>Diinput oleh <span class="font-medium">{{ $item->creator->name ?? '-' }}</span></p>
                    </div>
                    @endif
                </div>
                @empty
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                    <p class="text-xs text-slate-500 italic">Tidak ada data {{ strtolower($categoryData['label']) }}</p>
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
            <p class="text-xs text-slate-500">Belum ada data pengumpulan investigasi untuk laporan ini.</p>
        </div>
        @endforelse
    </div>
</div>