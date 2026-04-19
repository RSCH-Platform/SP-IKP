<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insiden - {{ $laporan->nomor_laporan ?? 'Laporan' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }



        /* Typography */
        .text-10px {
            font-size: 10px;
            line-height: 1.4;
        }

        .text-12px {
            font-size: 12px;
            line-height: 1.5;
        }

        .text-14px {
            font-size: 14px;
            line-height: 1.6;
        }

        .text-16px {
            font-size: 16px;
            line-height: 1.6;
        }

        .font-bold {
            font-weight: bold;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-medium {
            font-weight: 500;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .tracking-wide {
            letter-spacing: 0.05em;
        }

        /* Spacing */
        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-3 {
            margin-bottom: 0.75rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .p-1 {
            padding: 0.25rem;
        }

        .p-2 {
            padding: 0.5rem;
        }

        .p-3 {
            padding: 0.75rem;
        }

        .p-4 {
            padding: 1rem;
        }

        /* Layout */
        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 0.75rem;
        }

        .gap-4 {
            gap: 1rem;
        }

        .grid {
            display: grid;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        /* Border & Colors */
        .border {
            border: 1px solid #d1d5db;
        }

        .border-t-4 {
            border-top: 4px solid #6b7280;
        }

        .border-b-4 {
            border-bottom: 4px solid #6b7280;
        }

        .border-r {
            border-right: 1px solid #d1d5db;
        }

        .border-b {
            border-bottom: 1px solid #d1d5db;
        }

        .bg-white {
            background: white;
        }

        .bg-slate-50 {
            background: #f8fafc;
        }

        .bg-slate-100 {
            background: #f1f5f9;
        }

        .bg-slate-200 {
            background: #e2e8f0;
        }

        .bg-blue-50 {
            background: #eff6ff;
        }

        .bg-blue-700 {
            background: #1d4ed8;
            color: white;
        }

        .text-white {
            color: white;
        }

        .text-slate-500 {
            color: #64748b;
        }

        .text-slate-700 {
            color: #334155;
        }

        .text-slate-800 {
            color: #1e293b;
        }

        .text-blue-700 {
            color: #1d4ed8;
        }

        /* Components */
        .header-section {
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-bottom: 2px solid #1f2937;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .info-item {
            border: 1px solid #d1d5db;
            padding: 0.5rem;
            background: white;
        }

        .info-label {
            font-size: 9px;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 10px;
            color: #1e293b;
            font-weight: 500;
        }

        .section-header {
            background: #f1f5f9;
            border-top: 2px solid #1f2937;
            border-bottom: 2px solid #1f2937;
            padding: 0.5rem;
            margin-bottom: 0.75rem;
            margin-top: 1rem;
        }

        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 0.5rem;
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.5rem;
            background: white;
        }

        .data-label {
            font-size: 9px;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
        }

        .data-value {
            font-size: 10px;
            color: #1e293b;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        thead {
            background: #e2e8f0;
            border-bottom: 2px solid #6b7280;
        }

        th {
            padding: 0.5rem;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-right: 1px solid #cbd5e1;
        }

        th:last-child {
            border-right: none;
        }

        td {
            padding: 0.5rem;
            font-size: 9px;
            color: #334155;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        td:last-child {
            border-right: none;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        /* Footer */
        .footer-section {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid #d1d5db;
            font-size: 9px;
            color: #64748b;
        }

        .footer-note {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        /* Break */
        .break-inside-avoid {
            break-inside: avoid;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header-section">
        <div class="header-title">LAPORAN INSIDEN KESELAMATAN PASIEN</div>
        <div class="text-12px">Nomor Laporan: {{ $laporan->nomor_laporan ?? '-' }}</div>
    </div>

    <!-- Info Summary -->
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">No. Laporan</div>
            <div class="info-value">{{ $laporan->nomor_laporan ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Unit Kerja</div>
            <div class="info-value">{{ $laporan->unit_kerja ?? '-' }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value">{{ ucfirst($laporan->status ?? 'Draft') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Tanggal Cetak</div>
            <div class="info-value">{{ now()->translatedFormat('d F Y') }}</div>
        </div>
    </div>

    <!-- SECTION A: DATA PASIEN -->
    <div class="break-inside-avoid">
        <div class="section-header">
            <div class="section-title">BAGIAN A: DATA PASIEN</div>
        </div>

        <div class="data-row">
            <div class="data-label">Nama Pasien</div>
            <div class="data-value">{{ $laporan->nama_pasien ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">No. Rekam Medis</div>
            <div class="data-value">{{ $laporan->nomor_rekam_medis ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Ruangan</div>
            <div class="data-value">{{ $laporan->ruangan ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Umur</div>
            <div class="data-value">{{ $laporan->umur ?? '-' }} tahun</div>
        </div>

        <div class="data-row">
            <div class="data-label">Jenis Kelamin</div>
            <div class="data-value">{{ $laporan->jenis_kelamin ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Penanggung Biaya</div>
            <div class="data-value">{{ $laporan->penanggung_biaya ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Tanggal Masuk RS</div>
            <div class="data-value">
                @if($laporan->tanggal_masuk_rs)
                {{ $laporan->tanggal_masuk_rs->translatedFormat('d F Y H:i') }} WIB
                @else
                -
                @endif
            </div>
        </div>
    </div>

    <!-- SECTION B: RINCIAN KEJADIAN -->
    <div class="break-inside-avoid">
        <div class="section-header">
            <div class="section-title">BAGIAN B: RINCIAN KEJADIAN</div>
        </div>

        <div class="data-row">
            <div class="data-label">Tanggal Insiden</div>
            <div class="data-value">{{ $laporan->tanggal_insiden?->translatedFormat('d F Y') ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Waktu Insiden</div>
            <div class="data-value">{{ $laporan->waktu_insiden ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Jenis Insiden</div>
            <div class="data-value">{{ $laporan->jenis_insiden ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Lokasi Insiden</div>
            <div class="data-value">{{ $laporan->lokasi_insiden ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Kategori Insiden</div>
            <div class="data-value">{{ $laporan->kategori_insiden ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Dampak Insiden</div>
            <div class="data-value">{{ $laporan->dampak_insiden ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Kejadian Sebelumnya</div>
            <div class="data-value">{{ $laporan->kejadian_pernah_terjadi_sebelumnya ?? '-' }}</div>
        </div>
    </div>

    <!-- SECTION C: TINDAKAN YANG DILAKUKAN -->
    <div class="break-inside-avoid">
        <div class="section-header">
            <div class="section-title">BAGIAN C: TINDAKAN SETELAH KEJADIAN</div>
        </div>

        <div class="data-row">
            <div class="data-label">Tindakan</div>
            <div class="data-value">{{ substr($laporan->tindakan_dilakukan ?? '-', 0, 100) }}...</div>
        </div>

        <div class="data-row">
            <div class="data-label">Dilakukan Oleh</div>
            <div class="data-value">{{ $laporan->tindakan_dilakukan_oleh ?? '-' }}</div>
        </div>
    </div>

    <!-- SECTION D: KRONOLOGI TIMELINE -->
    <div class="break-inside-avoid">
        <div class="section-header">
            <div class="section-title">BAGIAN D: KRONOLOGI TIMELINE</div>
        </div>

        @forelse($timelineData['eventsByDate'] as $date => $dateEvents)
        <div style="margin-bottom: 1rem;">
            <div style="background: #f1f5f9; padding: 0.5rem; border-top: 2px solid #6b7280; border-bottom: 2px solid #6b7280; margin-bottom: 0.5rem;">
                <div style="font-size: 10px; font-weight: 600; color: #1e293b; text-transform: uppercase;">
                    TANGGAL: {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)?->translatedFormat('l, d F Y') ?? 'Tanggal tidak tersedia' }}
                </div>
            </div>

            @if($dateEvents->flatMap(fn($event) => $event->entries ?? [])->count() > 0)
            @php
            $categories = $timelineData['allCategories'];
            $timeGroups = $dateEvents->groupBy(fn($event) => \Carbon\Carbon::parse($event->event_datetime)->format('H:i'));
            @endphp

            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">WAKTU</th>
                        @foreach($categories as $category)
                        <th style="width: {{ 85 / count($categories) }}%;">{{ $category->name ?? 'Kategori' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeGroups as $time => $eventsAtSameTime)
                    @php
                    $mergedEntries = collect($eventsAtSameTime)
                    ->flatMap(fn($event) => $event->entries ?? [])
                    ->groupBy('category_id');
                    @endphp
                    <tr>
                        <td style="width: 15%; font-weight: 600;">{{ $time }}</td>
                        @foreach($categories as $category)
                        @php
                        $entries = $mergedEntries[$category->id] ?? collect();
                        $descriptions = collect($entries)->pluck('description')->filter()->all();
                        @endphp
                        <td style="width: {{ 85 / count($categories) }}%;">
                            @if(count($descriptions) > 0)
                            @foreach($descriptions as $description)
                            <div style="margin-bottom: 0.25rem;">{{ $description }}</div>
                            @endforeach
                            @else
                            <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align: center; padding: 1rem; background: #f8fafc; border: 1px solid #e5e7eb;">
                <div style="font-size: 9px; color: #64748b; font-style: italic;">Tidak ada entri untuk tanggal ini</div>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align: center; padding: 2rem;">
            <div style="font-size: 9px; color: #64748b; font-style: italic;">Belum ada kronologi timeline yang tersedia</div>
        </div>
        @endforelse
    </div>

    <!-- SECTION E: GRADING RISIKO -->
    @if(in_array($laporan->status, ['dilaporkan', 'revisi_unit']))
    <div class="break-inside-avoid">
        <div class="section-header">
            <div class="section-title">BAGIAN E: GRADING RISIKO</div>
        </div>

        <div class="data-row">
            <div class="data-label">Grading Risiko</div>
            <div class="data-value">{{ $laporan->grading_risiko ?? '-' }}</div>
        </div>

        <div class="data-row">
            <div class="data-label">Justifikasi</div>
            <div class="data-value">{{ $laporan->catatan_tambahan ?? 'Belum ada justifikasi grading' }}</div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer-section">
        <div class="footer-note">
            <strong>Catatan Penting:</strong>
        </div>
        <div class="footer-note">
            • Dokumen ini bersifat RAHASIA dan tidak boleh difotocopy<br>
            • Laporan harus diserahkan maksimal 2 x 24 jam setelah kejadian<br>
            • Semua field harus diisi dengan lengkap dan jelas<br>
            • Grading risiko harus ditentukan oleh kepala unit kerja
        </div>
        <div class="footer-note" style="margin-top: 1rem; text-align: center; border-top: 1px solid #d1d5db; padding-top: 0.5rem;">
            Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
        </div>
    </div>
</body>

</html>