<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Insiden Dummy PDF</title>
    <link rel="stylesheet" href="{{ asset('css/pdf-test.css') }}">
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.4;
            background: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
        }

        .section {
            margin-bottom: 24px;
        }

        .section h1,
        .section h2 {
            margin: 0;
            font-weight: 700;
        }

        .section h1 {
            font-size: 20px;
        }

        .section h2 {
            font-size: 16px;
            padding-left: 8px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 12px;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            background: #f9fafb;
        }

        .card-small {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
        }

        .text-xs {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .font-semibold {
            font-weight: 600;
        }

        .bordered-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }

        .bordered-table th,
        .bordered-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .bordered-table thead {
            background: #f3f4f6;
        }

        .text-center {
            text-align: center;
        }

        .text-red {
            color: #b91c1c;
            font-weight: 700;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            font-size: 10px;
            text-align: center;
            color: #6b7280;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="section">
            <h1>LAPORAN INSIDEN</h1>
            <div class="grid-2" style="margin-top: 12px;">
                <div class="card-small">
                    <p class="text-xs">Nomor</p>
                    <p class="font-semibold">IKP-2026-001</p>
                    <p class="text-xs" style="margin-top: 8px;">Tanggal</p>
                    <p class="font-semibold">21 April 2026</p>
                </div>
                <div class="card-small">
                    <p class="text-xs">Unit</p>
                    <p class="font-semibold">IT Support</p>
                    <p class="text-xs" style="margin-top: 8px;">Status</p>
                    <p class="font-semibold">Selesai</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>A. Informasi Pelapor</h2>
            <div class="grid-3">
                <div class="card">
                    <p class="text-xs">Nama</p>
                    <p class="font-semibold">Ahmad Ilyas</p>
                </div>
                <div class="card">
                    <p class="text-xs">Email</p>
                    <p class="font-semibold">ahmad@mail.com</p>
                </div>
                <div class="card">
                    <p class="text-xs">No HP</p>
                    <p class="font-semibold">08123456789</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>B. Detail Insiden</h2>
            <div class="card" style="margin-bottom: 12px;">
                <p>Terjadi gangguan pada sistem server yang menyebabkan downtime selama beberapa jam. Analisis awal menunjukkan adanya overload traffic serta kegagalan pada sistem load balancer.</p>
            </div>
            <div class="grid-2">
                <div class="card">
                    <p class="text-xs">Kategori</p>
                    <p class="font-semibold">Server</p>
                </div>
                <div class="card">
                    <p class="text-xs">Severity</p>
                    <p class="font-semibold text-red">High</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>C. Timeline</h2>
            <table class="bordered-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Event</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 12; $i++)
                        <tr>
                        <td>10:{{ sprintf('%02d', $i) }}</td>
                        <td>Event {{ $i }}</td>
                        <td>Deskripsi kejadian ke-{{ $i }} pada sistem.</td>
                        </tr>
                        @endfor
                </tbody>
            </table>
        </div>

        <div class="page-break"></div>

        <div class="section">
            <h2>D. Analisis & Akar Masalah</h2>
            <div class="grid-2">
                <div class="card">
                    <p class="font-semibold" style="margin-bottom: 8px;">Root Cause</p>
                    <p>Overload server karena traffic spike mendadak.</p>
                </div>
                <div class="card">
                    <p class="font-semibold" style="margin-bottom: 8px;">Contributing Factors</p>
                    <p>Load balancer tidak auto-scale.</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>E. Tindakan Perbaikan</h2>
            <div class="card" style="padding: 16px;">
                <ul style="margin: 0; padding-left: 18px;">
                    <li>Upgrade server capacity</li>
                    <li>Implement auto scaling</li>
                    <li>Monitoring real-time</li>
                </ul>
            </div>
        </div>

        <div class="footer">Generated by System • 2026</div>
    </div>
</body>

</html>