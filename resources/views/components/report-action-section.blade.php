@props(['laporan'])

@php
use App\Models\LaporanInsiden;
$selectedTindakanDilakukanOleh = trim((string) ($laporan->tindakan_dilakukan_oleh ?? ''));
$tindakanDilakukanOlehLainnya = trim((string) ($laporan->tindakan_dilakukan_oleh_lainnya ?? ''));
@endphp

<div class="break-inside-avoid mb-8">
    <x-section-header title="BAGIAN C: Tindakan Setelah Kejadian" />
    <div class="bg-white border border-slate-300 p-2 space-y-3">
        <x-long-text-display label="Tindakan yang Dilakukan Segera Setelah Kejadian" :text="$laporan->tindakan_dilakukan ?? '-'" />
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="border border-slate-200 p-2">
                <p class="report-field-label">Tindakan Dilakukan Oleh</p>
                <div class="mt-2 grid grid-cols-1 gap-1">
                    @foreach(LaporanInsiden::TINDAKAN_DILAKUKAN_OLEH_OPTIONS as $optionValue => $optionLabel)
                    <x-checkbox-display
                        :checked="$selectedTindakanDilakukanOleh === $optionValue"
                        :label="$optionLabel"
                        disabled />
                    @endforeach
                </div>
                @if($selectedTindakanDilakukanOleh === 'Lainnya' && $tindakanDilakukanOlehLainnya)
                <p class="mt-2 text-xs text-slate-500">Lainnya: {{ $tindakanDilakukanOlehLainnya }}</p>
                @endif
            </div>
            <x-data-row label="Unit Penyebab" :value="$laporan->unit_kerja ?? '-'" />
        </div>
    </div>
</div>