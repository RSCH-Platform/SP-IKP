<!DOCTYPE html>
<html>
<head>
    <title>Laporan Monitoring Investigasi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; margin: 0 0 5px 0; }
        .subtitle { font-size: 12px; margin: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: right; }
        .badge { padding: 2px 6px; border-radius: 4px; display: inline-block; font-size: 10px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-indigo { background: #e0e7ff; color: #3730a3; }
        .badge-orange { background: #ffedd5; color: #9a3412; }
        @media print {
            body { font-size: 10px; }
            th { background-color: #e0e0e0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .total-row { background-color: #e0e0e0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>
    <div class="header">
        <h1 class="title">Laporan Monitoring Investigasi Insiden</h1>
        <p class="subtitle">
            Periode: {{ $periodLabel }} | Unit: {{ $unitLabel }} | Status: {{ $statusLabel }}
        </p>
    </div>

    <h3 style="font-size: 14px; margin-bottom: 5px;">A. Daftar Insiden & Target Penyelesaian</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Unit</th>
                <th>Tanggal</th>
                <th>Jenis Insiden</th>
                <th>Kategori Insiden</th>
                <th>Grading</th>
                <th>Target (Hari)</th>
                <th>Lama Inv. (Hari)</th>
                <th>Kesesuaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $index => $row)
                @php
                    $badgeClass = 'badge-gray';
                    if ($row->gradingColor === 'green') $badgeClass = 'badge-green';
                    elseif ($row->gradingColor === 'blue') $badgeClass = 'badge-blue';
                    elseif ($row->gradingColor === 'yellow') $badgeClass = 'badge-yellow';
                    elseif ($row->gradingColor === 'red') $badgeClass = 'badge-red';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->unit }}</td>
                    <td class="text-center">{{ $row->tanggal }}</td>
                    <td>{{ $row->jenis_insiden }}</td>
                    <td>{{ $row->insiden }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $row->grading }}</span>
                    </td>
                    <td class="text-center">{{ $row->target_hari }}</td>
                    <td class="text-center">
                        @if($row->lama_investigasi !== null)
                            {{ $row->lama_investigasi }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->has_started)
                            @if($row->is_sesuai_target)
                                <span class="badge badge-indigo">Sesuai</span>
                            @else
                                <span class="badge badge-orange">Tidak Sesuai</span>
                            @endif
                        @else
                            <span class="badge badge-gray">Belum Mulai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data insiden pada periode dan filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="font-size: 14px; margin-bottom: 5px;">B. Rekapitulasi Kinerja Unit</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Unit Kerja</th>
                <th>Total Insiden</th>
                <th>Selesai Investigasi</th>
                <th>Sedang Berjalan</th>
                <th>Tepat Waktu (Sesuai SLA)</th>
                <th>% Kepatuhan SLA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->unit }}</td>
                    <td class="text-center">{{ $row->total }}</td>
                    <td class="text-center">{{ $row->sudah }}</td>
                    <td class="text-center">{{ $row->belum }}</td>
                    <td class="text-center">{{ $row->sesuai }}</td>
                    <td class="text-center font-bold">{{ $row->persentase }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data rekapitulasi.</td>
                </tr>
            @endforelse
            @if(count($summary) > 0)
                <tr class="total-row">
                    <td colspan="2" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-center">{{ $summary->sum('total') }}</td>
                    <td class="text-center">{{ $summary->sum('sudah') }}</td>
                    <td class="text-center">{{ $summary->sum('belum') }}</td>
                    <td class="text-center">{{ $summary->sum('sesuai') }}</td>
                    <td class="text-center">
                        @php
                            $sumTotal = $summary->sum('total');
                            $sumSesuai = $summary->sum('sesuai');
                            $avgPercent = $sumTotal > 0 ? round(($sumSesuai / $sumTotal) * 100) : 0;
                        @endphp
                        {{ $avgPercent }}%
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: {{ $user }} | Tanggal Cetak: {{ $generatedAt }}
    </div>
</body>
</html>
