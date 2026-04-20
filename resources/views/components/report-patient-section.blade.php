@props(['laporan'])

@php
$ageGroups = [
'0-1 bulan' => '0 - 1 bulan',
'> 1 bulan - 1 tahun' => '1 bulan - 1 tahun',
'> 1 tahun - 5 tahun' => '1 tahun - 5 tahun',
'> 5 tahun - 15 tahun' => '5 tahun - 15 tahun',
'> 15 tahun - 30 tahun' => '15 tahun - 30 tahun',
'>30 tahun - 65 tahun' => '30 tahun - 65 tahun',
'> 65 tahun' => '65 tahun ke atas',
];

$selectedAge = trim($laporan->kelompok_umur ?? '');
$selectedGender = trim($laporan->jenis_kelamin ?? '');
$selectedPayment = trim($laporan->penanggung_biaya ?? '');
@endphp

<section class="mb-4 break-inside-auto print:block">
    <x-section-header title="BAGIAN A: Data Pasien" />

    <div class="bg-white border border-slate-300 p-2 space-y-4 print:block break-inside-avoid print:break-inside-avoid">

        {{-- IDENTITAS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 print:block">
            <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Nama Pasien</p>
                <p class="report-field-title">{{ $laporan->nama_pasien ?? '-' }}</p>
            </div>
            <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">No. Rekam Medis</p>
                <p class="report-field-title">{{ $laporan->nomor_rekam_medis ?? '-' }}</p>
            </div>
        </div>

        <div class="border-t border-slate-200"></div>

        {{-- DEMOGRAFI --}}
        <div class="grid grid-cols-2 md:grid-cols-2 gap-2 print:block">
            <div class="col-span-2 border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Umur</p>
                <p class="report-field-title">{{ $laporan->umur ?? '-' }} tahun</p>
            </div>

            <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Kelompok Umur</p>
                <div class="space-y-2">
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach($ageGroups as $key => $label)
                        <x-checkbox-display :checked="trim($key) === $selectedAge" :label="$label" disabled />
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Jenis Kelamin</p>
                <div class="mt-2 grid grid-cols-1 gap-2">
                    <x-checkbox-display :checked="trim('Laki-laki') === $selectedGender" label="Laki-laki" disabled />
                    <x-checkbox-display :checked="trim('Perempuan') === $selectedGender" label="Perempuan" disabled />
                </div>
            </div>
        </div>

        {{-- ADMINISTRASI --}}
        <div class="grid grid-cols-2 gap-2 print:block">
            <div class="border border-slate-200 p-2 col-span-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Ruangan</p>
                <p class="report-field-title">{{ $laporan->ruangan ?? '-' }}</p>
            </div>

            <div class="border border-slate-200 p-2 col-span-2 break-inside-avoid print:break-inside-avoid">
                <p class="report-field-label">Penanggung Biaya Pasien</p>
                <div class="mt-2 grid grid-cols-4 gap-2">
                    <x-checkbox-display :checked="trim('Pribadi') === $selectedPayment" label="Pribadi" disabled />
                    <x-checkbox-display :checked="trim('Asuransi Swasta') === $selectedPayment" label="Asuransi Swasta" disabled />
                    <x-checkbox-display :checked="trim('BPJS') === $selectedPayment" label="BPJS" disabled />
                    <x-checkbox-display :checked="trim('Lainnya') === $selectedPayment" label="Lainnya" disabled />
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200"></div>

        {{-- WAKTU --}}
        <div class="border border-slate-200 p-2 break-inside-avoid print:break-inside-avoid">
            <p class="report-field-label">Tanggal Masuk RS</p>
            <p class="report-field-title">
                @if($laporan->tanggal_masuk_rs)
                Pada tanggal {{ $laporan->tanggal_masuk_rs->translatedFormat('d F Y') }} Pukul {{ $laporan->tanggal_masuk_rs->translatedFormat('H:i') }} WIB
                @else
                -
                @endif
            </p>
        </div>

    </div>
</section>