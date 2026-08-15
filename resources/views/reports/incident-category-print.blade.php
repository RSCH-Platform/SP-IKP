<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kategori Insiden</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; margin: 0 0 5px 0; }
        .subtitle { font-size: 14px; margin: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: right; }
        @media print {
            body { font-size: 12px; }
            th { background-color: #e0e0e0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .total-row { background-color: #e0e0e0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        window.onload = function() {
            var seriesData = {!! json_encode($series) !!};
            if (seriesData.length > 0) {
                var options = {
                    series: seriesData,
                    chart: {
                        type: 'pie',
                        width: 500,
                        animations: { enabled: false },
                        toolbar: { show: false }
                    },
                    labels: {!! json_encode($labels) !!},
                    dataLabels: { enabled: true },
                    colors: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'],
                    legend: { position: 'bottom' }
                };

                var chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render().then(function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                });
            } else {
                window.print();
            }
        };
    </script>
</head>
<body>
    <div class="header">
        <h1 class="title">Laporan Distribusi Kategori Insiden</h1>
        <p class="subtitle">Periode: {{ $period }}</p>
    </div>

    <div style="display: flex; justify-content: center; margin-bottom: 20px;">
        <div id="chart"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%">No</th>
                <th style="width: 50%">Kategori Insiden</th>
                <th style="width: 20%">Jumlah</th>
                <th style="width: 20%">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['kategori'] }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center">{{ $row['persentase'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data insiden pada periode ini.</td>
                </tr>
            @endforelse
            @if($totalAll > 0)
                <tr class="total-row">
                    <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-center">{{ $totalAll }}</td>
                    <td class="text-center">100%</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: {{ $user }} | Tanggal Cetak: {{ $generatedAt }}
    </div>
</body>
</html>
