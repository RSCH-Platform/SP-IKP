@props(['laporan', 'title' => 'BAGIAN B: Rincian Kejadian'])

@php
use App\Models\LaporanInsiden;
use Illuminate\Support\Str;

$formatValue = function ($value) {
if (is_null($value)) {
return '-';
}

$value = trim((string) $value);
return $value === '' ? '-' : $value;
};

$formatDate = function ($value) use ($formatValue) {
if (is_null($value)) {
return '-';
}

if (method_exists($value, 'translatedFormat')) {
return $value->translatedFormat('d F Y');
}

return $formatValue($value);
};

$normalise = function ($value) use ($formatValue) {
$clean = $formatValue($value);

if ($clean === '-') {
return $clean;
}

return Str::of($clean)->replace('_', ' ')->title();
};

$tanggalInsiden = $formatDate($laporan->tanggal_insiden ?? null);
$waktuInsiden = $formatValue($laporan->waktu_insiden ?? null);
$jenisInsiden = $formatValue($laporan->jenis_insiden ?? null);
$lokasiInsiden = $formatValue($laporan->lokasi_insiden ?? null);
$rawKategoriInsiden = trim((string) ($laporan->kategori_insiden ?? ''));
$rawDampakInsiden = trim((string) ($laporan->dampak_insiden ?? ''));
$rawPelaporInsidenPasien = trim((string) ($laporan->pelapor_insiden_pasien ?? ''));
$pelaporInsidenPasienLainnya = trim((string) ($laporan->pelapor_insiden_pasien_lainnya ?? ''));
$rawInsidenMenyangkutPasien = trim((string) ($laporan->insiden_menyangkut_pasien ?? ''));
$insidenMenyangkutPasienLainnya = trim((string) ($laporan->insiden_menyangkut_pasien_lainnya ?? ''));
$rawSpesialisasiPasien = trim((string) ($laporan->spesialisasi_pasien ?? ''));
$spesialisasiPasienLainnya = trim((string) ($laporan->spesialisasi_pasien_lainnya ?? ''));
$deskripsiInsiden = $formatValue($laporan->deskripsi_kategori_insiden ?? null);
$previousIncident = trim((string) ($laporan->kejadian_pernah_terjadi_sebelumnya ?? ''));
$previousIncidentDescription = $formatValue($laporan->kejadian_pernah_terjadi_sebelumnya_deskripsi ?? null);
@endphp

<section class="mb-4 break-inside-auto print:block break-inside-avoid print:break-inside-avoid">
    <x-section-header :title="$title" />

    <div class="bg-white border border-slate-300 p-2 space-y-3 break-inside-avoid print:break-inside-avoid">
        <x-long-text-display label="Insiden" :text="$deskripsiInsiden" />

        <section class="mb-4 break-inside-auto break-inside-avoid print:break-inside-avoid">
            <div class="grid grid-cols-3 gap-2 mb-4 bg-white border border-slate-300 items-center text-left">
                <x-data-row label="Tanggal Insiden" :value="$tanggalInsiden" />
                <x-data-row label="Waktu Insiden" :value="$waktuInsiden" />
                <x-data-row label="Lokasi Insiden" :value="$lokasiInsiden" />
            </div>
        </section>

        <div class="grid grid-cols-2 gap-2 print:block">
            <div class="col-span-2 border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Jenis Insiden</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach(LaporanInsiden::JENIS_INSIDEN_OPTIONS as $optionValue => $optionLabel)
                    <x-checkbox-display
                        :checked="$jenisInsiden === $optionValue"
                        :label="$optionLabel"
                        disabled />
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2">
            <div class="border border-slate-200 p-2 col-span-4">
                <p class="report-field-label">Kategori Insiden</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach(LaporanInsiden::KATEGORI_INSIDEN_OPTIONS as $optionValue => $optionLabel)
                    <x-checkbox-display
                        :checked="$rawKategoriInsiden === $optionValue"
                        :label="$optionLabel"
                        disabled />
                    @endforeach
                </div>
            </div>

            <div class="border border-slate-200 p-2 col-span-2">
                <p class="report-field-label">Orang yang Melapor</p>
                <div class="mt-2 grid grid-cols-1 gap-2">
                    @foreach(LaporanInsiden::PELAPOR_INSIDEN_PASIEN_OPTIONS as $optionValue => $optionLabel)
                    <x-checkbox-display
                        :checked="$rawPelaporInsidenPasien === $optionValue"
                        :label="$optionLabel"
                        disabled />
                    @endforeach
                </div>
                @if($rawPelaporInsidenPasien === 'Lainnya' && $pelaporInsidenPasienLainnya)
                <p class="mt-2 text-xs text-slate-500">Lainnya: {{ $pelaporInsidenPasienLainnya }}</p>
                @endif
            </div>

            <div class="border border-slate-200 p-2 col-span-2">
                <p class="report-field-label">Insiden Menyangkut</p>
                <div class="mt-2 grid grid-cols-1 gap-2">
                    @foreach(LaporanInsiden::INSIDEN_MENYANGKUT_PASIEN_OPTIONS as $optionValue => $optionLabel)
                    <x-checkbox-display
                        :checked="$rawInsidenMenyangkutPasien === $optionValue"
                        :label="$optionLabel"
                        disabled />
                    @endforeach
                </div>
                @if($rawInsidenMenyangkutPasien === 'Lainnya' && $insidenMenyangkutPasienLainnya)
                <p class="mt-2 text-xs text-slate-500">Lainnya: {{ $insidenMenyangkutPasienLainnya }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 print:block">
            <div class="border border-slate-200 p-2 col-span-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Spesialisasi Pasien</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach(LaporanInsiden::SPESIALISASI_PASIEN_OPTIONS as $optionValue => $optionLabel)
                    <x-checkbox-display
                        :checked="$rawSpesialisasiPasien === $optionValue"
                        :label="$optionLabel"
                        disabled />
                    @endforeach
                </div>
                @if($rawSpesialisasiPasien === 'Lainnya' && $spesialisasiPasienLainnya)
                <p class="mt-2 text-xs text-slate-500">Lainnya: {{ $spesialisasiPasienLainnya }}</p>
                @endif
            </div>
            <div class="border border-slate-200 p-2 col-span-2">
                <p class="report-field-label">Dampak Insiden</p>

                <div class="mt-2 grid grid-cols-5 gap-1.5">
                    @foreach(LaporanInsiden::DAMPAK_INSIDEN_OPTIONS as $optionValue => $optionLabel)
                    @php
                    $isSelected = $rawDampakInsiden === $optionValue;
                    @endphp

                    <div class="flex items-center justify-between rounded-md border px-2 py-1.5 text-xs leading-tight {{ $isSelected ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 bg-white text-slate-700' }}">
                        <span class="font-medium truncate">
                            {{ $optionLabel }}
                        </span>

                        @if($isSelected)
                        <span class="text-[10px] text-white font-semibold">✓</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <x-previous-incident-display
            :previousIncident="$previousIncident"
            :description="$previousIncidentDescription" />
    </div>
    </div>