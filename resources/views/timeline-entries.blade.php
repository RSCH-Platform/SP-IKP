<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Timeline Kejadian Pasien</title>

    <!-- Font modern -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
        }

        h1 {
            margin-bottom: 4px;
        }

        p {
            margin-top: 0;
            color: #64748b;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: max-content;
            /* 🔥 ini kunci */
            width: max-content;
            /* 🔥 ini juga penting */
        }

        th,
        td {
            padding: 12px;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            width: 220px;
            white-space: normal;
            overflow-wrap: break-word;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f1f5f9;
            text-align: center;
            font-weight: 600;
        }

        .date-row th {
            border-bottom: 3px solid #64748b;
            background: #e2e8f0;
        }

        .time-row th {
            background: #f8fafc;
            border-top: 3px solid #64748b;
            font-weight: 600;
        }

        thead th {
            border-bottom: 2px solid #64748b;
        }

        .sub-header {
            background: #f8fafc;
            font-weight: 500;
        }

        .label {
            background: #facc15;
            font-weight: 600;
            min-width: 200px;
            position: sticky;
            left: 0;
            z-index: 3;
            border-right: 1px solid #e5e7eb;
        }

        tbody tr:hover td {
            background: #f9fafb;
        }

        .empty {
            color: #cbd5f5;
        }

        .section-divider td {
            height: 10px;
            background: #f1f5f9;
            border: none;
        }
    </style>
</head>

<body>

    <h1>Timeline Kejadian Pasien</h1>
    <p>Monitoring kronologi pelayanan pasien dari IGD hingga rawat inap</p>

    @php
    $timeline = [
    [
    'date' => '5 Januari 2025',
    'entries' => [
    '22.15',
    '23.40',
    ],
    ],
    [
    'date' => '6 Januari 2025',
    'entries' => [
    '00.30',
    '08.00',
    '16.00',
    '21.00',
    ],
    ],
    [
    'date' => '7 Januari 2025',
    'entries' => [
    '08.00',
    '14.00',
    '20.00',
    ],
    ],
    [
    'date' => '8 Januari 2025',
    'entries' => [
    '09.51',
    ],
    ],
    ];

    $rows = [
    'KEJADIAN' => [
    'Pasien datang ke IGD dengan keluhan demam tinggi (39°C), lemas, dan mual muntah',
    'Dilakukan triase, masuk kategori kuning. Pemeriksaan awal oleh dokter jaga',
    'Pasien dipindahkan ke ruang observasi IGD',
    'Dokter DPJP melakukan visit, advis cek lab lengkap + tubex',
    'Pasien dipindahkan ke ruang rawat inap Alamanda',
    'Perawat melakukan input order lab dan farmasi',
    'Hasil lab keluar sebagian (hematologi), hasil tubex pending',
    'Dokter melakukan evaluasi kondisi pasien',
    'Perawat melaporkan keterlambatan hasil ke DPJP',
    'Pasien mulai menunjukkan perbaikan klinis',
    ],

    'INFORMASI TAMBAHAN' => [
    'Riwayat demam 3 hari, belum konsumsi antibiotik',
    'Tekanan darah stabil, nadi meningkat',
    'Pasien dalam pengawasan ketat',
    'Permintaan lab dikirim pukul 00.45',
    'Keluarga menyetujui rawat inap',
    'Farmasi mengkonfirmasi sebagian obat tersedia',
    'Lab menginformasikan keterlambatan reagen tubex',
    'Observasi kondisi vital tiap 4 jam',
    'DPJP meminta follow up ke lab',
    'Pasien sudah bisa makan ringan',
    ],

    'GOOD PRACTICE' => [
    '',
    'Triase dilakukan sesuai SOP',
    '',
    'Perawat langsung input order tanpa delay',
    '',
    'Koordinasi antar unit berjalan baik',
    '',
    '',
    'Pelaporan dilakukan cepat ke DPJP',
    '',
    ],

    'MASALAH (CMP)' => [
    'Antrian IGD cukup padat',
    '',
    '',
    '',
    '',
    'Keterlambatan pengiriman obat tertentu',
    'Reagen tubex kosong',
    '',
    '',
    '',
    ],

    'MASALAH (SDP)' => [
    '',
    '',
    '',
    '',
    '',
    'Petugas farmasi terbatas saat shift malam',
    '',
    '',
    '',
    '',
    ],
    ];

    $totalCols = collect($timeline)->sum(fn($d) => count($d['entries']));
    @endphp

    <div class="table-container">
        <table>

            <!-- HEADER -->
            <thead>
                <tr class="date-row">
                    <th class="label">WAKTU</th>
                    @foreach($timeline as $dateBlock)
                    <th colspan="{{ count($dateBlock['entries']) }}">
                        {{ $dateBlock['date'] }}
                    </th>
                    @endforeach
                </tr>

                <tr class="time-row">
                    <th class="label"></th>
                    @foreach($timeline as $dateBlock)
                    @foreach($dateBlock['entries'] as $time)
                    <th class="sub-header">{{ $time }}</th>
                    @endforeach
                    @endforeach
                </tr>
            </thead>

            <!-- BODY -->
            <tbody>
                @foreach($rows as $label => $cells)
                <tr>
                    <th class="label" scope="row">{{ $label }}</th>

                    @foreach($cells as $cell)
                    <td>
                        @if($cell)
                        {!! nl2br(e($cell)) !!}
                        @else
                        <span class="empty">—</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach

                <tr class="section-divider">
                    <td colspan="{{ $totalCols + 1 }}"></td>
                </tr>

            </tbody>
        </table>
    </div>

</body>

</html>