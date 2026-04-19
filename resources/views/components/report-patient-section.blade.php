@props(['laporan'])

@php
$ageGroups = [
'0-1 bulan' => '0-1 bulan',
'> 1 bulan - 1 tahun' => '> 1 bulan - 1 tahun',
'> 1 tahun - 5 tahun' => '> 1 tahun - 5 tahun',
'> 5 tahun - 15 tahun' => '> 5 tahun - 15 tahun',
'> 15 tahun - 30 tahun' => '> 15 tahun - 30 tahun',
'>30 tahun - 65 tahun' => '>30 tahun - 65 tahun',
'> 65 tahun' => '> 65 tahun',
];

$selectedAge = trim($laporan->kelompok_umur ?? '');
$selectedGender = trim($laporan->jenis_kelamin ?? '');
$selectedPayment = trim($laporan->penanggung_biaya ?? '');
@endphp

<div class="break-inside-avoid mb-6">
    <x-section-header title="BAGIAN A: Data Pasien" />

    <div class="bg-white border border-slate-300 p-2 space-y-4">

        {{-- IDENTITAS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="border border-slate-200 p-2">
                <p class="report-field-title">Nama Pasien</p>
                <p class="text-xs text-slate-800 font-medium">{{ $laporan->nama_pasien ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2">
                <p class="report-field-title">No. Rekam Medis</p>
                <p class="text-xs text-slate-800 font-medium">{{ $laporan->nomor_rekam_medis ?? '-' }}</p>
            </div>
        </div>

        <div class="border-t border-slate-200"></div>

        {{-- DEMOGRAFI --}}
        <div class="grid grid-cols-3 md:grid-cols-3 gap-2">
            <div class="border border-slate-200 p-2">
                <p class="report-field-title">Umur</p>
                <p class="text-xs text-slate-800 font-medium">{{ $laporan->umur ?? '-' }} tahun</p>
            </div>

            <div class="border border-slate-200 p-2">
                <p class="report-field-title">Kelompok Umur</p>
                <div class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($ageGroups as $key => $label)
                        <x-checkbox-display :checked="trim($key) === $selectedAge" :label="$label" disabled />
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="border border-slate-200 p-2">
                <p class="report-field-title">Jenis Kelamin</p>
                <div class="grid grid-cols-2 max-w-xs gap-2">
                    <x-checkbox-display :checked="trim('Laki-laki') === $selectedGender" label="Laki-laki" disabled />
                    <x-checkbox-display :checked="trim('Perempuan') === $selectedGender" label="Perempuan" disabled />
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200"></div>

        {{-- ADMINISTRASI --}}
        <div class="grid grid-cols-2 gap-2">
            <div class="border border-slate-200 p-2">
                <p class="report-field-title">Ruangan</p>
                <p class="text-xs text-slate-800">{{ $laporan->ruangan ?? '-' }}</p>
            </div>

            <div class="border border-slate-200 p-2">
                <p class="report-field-title">Penanggung Biaya Pasien</p>
                <div class="grid grid-cols-2 gap-3">
                    <x-checkbox-display :checked="trim('Pribadi') === $selectedPayment" label="Pribadi" disabled />
                    <x-checkbox-display :checked="trim('Asuransi Swasta') === $selectedPayment" label="Asuransi Swasta" disabled />
                    <x-checkbox-display :checked="trim('BPJS') === $selectedPayment" label="BPJS" disabled />
                    <x-checkbox-display :checked="trim('Lainnya') === $selectedPayment" label="Lainnya" disabled />
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200"></div>

        {{-- WAKTU --}}
        <div class="border border-slate-200 p-2">
            <p class="report-field-title">Tanggal Masuk RS</p>
            <p class="text-xs text-slate-800">
                @if($laporan->tanggal_masuk_rs)
                Pada tanggal {{ $laporan->tanggal_masuk_rs->translatedFormat('d F Y') }} di jam {{ $laporan->tanggal_masuk_rs->translatedFormat('H:i') }} WIB
                @else
                -
                @endif
            </p>
        </div>

    </div>
</div>