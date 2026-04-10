@props([
'title' => 'Laporan Insiden',
'subtitle' => 'Rumah Sakit Citra Husada Jember',
'documentNumber' => 'IKP/LAP/001/2026',
'pageNumber' => '1 dari 1',
'additionalInfo' => []
])

<div class="report-header border-b-2 border-gray-800 pb-4 mb-8">
    <div class="flex items-start justify-between gap-6 mb-3">
        <!-- Logo Kiri -->
        <div class="w-32 h-32 flex-shrink-0">
            <img src="{{ asset('images/assets/logo-rs.webp') }}"
                alt="Logo RS Citra Husada Jember" class="w-full h-full object-contain">
        </div>

        <!-- Text Content -->
        <div class="flex-1 text-center">
            <h2 class="text-base font-bold text-gray-800 uppercase" style="letter-spacing: 1.5px;">{{ $title }}</h2>
            <h1 class="text-xl font-bold text-gray-900 mb-1" style="letter-spacing: 1px;">RUMAH SAKIT CITRA HUSADA JEMBER</h1>
            <div class="text-xs text-gray-600 mb-2" style="letter-spacing: 0.5px;">Jl. Teratai No. 22, Kab. Jember, Jawa Timur | Telp. (0331) 486200 </div>
            <div class="text-xs text-gray-600 mb-2" style="letter-spacing: 0.5px;">Telp. (0331) 486200 | Fax. (0331) 427088</div>
        </div>

        <!-- Logo Kanan -->
        <div class="w-14 h-14 md:w-16 md:h-16 mr-5 mt-4">
            <x-logo-report />
        </div>
    </div>
</div>