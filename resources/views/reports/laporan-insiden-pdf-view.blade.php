<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/pdf-test.css') }}">

    .section {
    margin-bottom: 16px;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    }

    .section-title {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 12px;
    color: #0f172a;
    }

    .field-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    }

    .field {
    flex: 1 1 45%;
    min-width: 140px;
    }

    .field-label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #475569;
    margin-bottom: 4px;
    }

    .field-value {
    font-size: 13px;
    color: #0f172a;
    line-height: 1.4;
    }

    .title {
    font-size: 18px;
    font-weight: 800;
    margin: 0 0 8px;
    }

    .subtitle {
    margin: 0;
    font-size: 12px;
    color: #475569;
    }

    .note {
    font-size: 12px;
    color: #334155;
    margin-top: 8px;
    }
    </style>
</head>

<body>
    <div class="page">
        <header class="section">
            <h1 class="title">LAPORAN INSIDEN</h1>
            <p class="subtitle">No. Laporan: {{ $laporan->nomor_laporan ?? '-' }}</p>
            <p class="subtitle">Unit Kerja: {{ $laporan->unit_kerja ?? '-' }} | Status: {{ ucfirst($laporan->status ?? 'Draft') }}</p>
        </header>

        <div class="section">
            <div class="section-title">Data Pasien</div>
            <div class="field-row">
                <div class="field">
                    <span class="field-label">Nama Pasien</span>
                    <span class="field-value">{{ $laporan->nama_pasien ?? '-' }}</span>
                </div>
                <div class="field">
                    <span class="field-label">No. Rekam Medis</span>
                    <span class="field-value">{{ $laporan->nomor_rekam_medis ?? '-' }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Umur</span>
                    <span class="field-value">{{ $laporan->umur ?? '-' }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Jenis Kelamin</span>
                    <span class="field-value">{{ $laporan->jenis_kelamin ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Rincian Insiden</div>
            <div class="field-row">
                <div class="field">
                    <span class="field-label">Tanggal Insiden</span>
                    <span class="field-value">{{ $laporan->tanggal_insiden?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Waktu Insiden</span>
                    <span class="field-value">{{ $laporan->waktu_insiden ?? '-' }}</span>
                </div>
            </div>
            <div class="field-row" style="margin-top:12px;">
                <div class="field" style="flex:1 1 100%;">
                    <span class="field-label">Deskripsi</span>
                    <span class="field-value">{{ $laporan->deskripsi_kategori_insiden ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Keterangan</div>
            <p class="note">Dokumen ini dibuat oleh Browsershot menggunakan route PDF view sederhana dengan CSS vanilla.</p>
        </div>
    </div>
</body>

</html>