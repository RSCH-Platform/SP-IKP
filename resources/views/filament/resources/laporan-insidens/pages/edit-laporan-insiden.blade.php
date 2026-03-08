<x-filament-panels::page>
    <style>
        .ikp-header {
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .dark .ikp-header {
            background-color: rgb(31 41 55);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .ikp-header-content {
            padding: 1.5rem;
        }

        .ikp-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }

        .dark .ikp-header-top {
            border-color: rgb(55 65 81);
        }

        .ikp-logo-circle {
            width: 4rem;
            height: 4rem;
            background: linear-gradient(to bottom right, #3b82f6, #06b6d4);
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ikp-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }

        .dark .ikp-title {
            color: white;
        }

        .ikp-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .dark .ikp-subtitle {
            color: #9ca3af;
        }

        .ikp-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .dark .ikp-badge {
            background-color: rgba(30, 64, 175, 0.2);
            color: #93c5fd;
            border-color: rgba(93, 191, 253, 0.3);
        }

        .ikp-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-draft {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        .dark .status-draft {
            background-color: rgba(75, 85, 99, 0.2);
            color: #d1d5db;
        }

        .status-dilaporkan {
            background-color: #fef08a;
            color: #92400e;
        }

        .dark .status-dilaporkan {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .status-revisi {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dark .status-revisi {
            background-color: rgba(220, 38, 38, 0.2);
            color: #fca5a5;
        }

        .status-diverifikasi {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .dark .status-diverifikasi {
            background-color: rgba(30, 64, 175, 0.2);
            color: #93c5fd;
        }

        .status-revisi-unit {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dark .status-revisi-unit {
            background-color: rgba(220, 38, 38, 0.2);
            color: #fca5a5;
        }

        .status-investigasi {
            background-color: #dcfce7;
            color: #15803d;
        }

        .dark .status-investigasi {
            background-color: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }

        .ikp-notice {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        .dark .ikp-notice {
            background-color: rgba(245, 158, 11, 0.1);
        }

        .ikp-notice-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: #92400e;
            margin-bottom: 0.5rem;
        }

        .dark .ikp-notice-title {
            color: #fbbf24;
        }

        .ikp-notice-text {
            font-size: 0.875rem;
            color: #b45309;
        }

        .dark .ikp-notice-text {
            color: #fcd34d;
        }

        .ikp-form-wrapper {
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .dark .ikp-form-wrapper {
            background-color: rgb(31 41 55);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .ikp-form-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .dark .ikp-form-footer {
            border-color: rgb(55 65 81);
        }

        .ikp-footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .ikp-footer-text {
            color: #9ca3af;
        }

        .ikp-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .ikp-info-item {
            background-color: #f9fafb;
            padding: 1rem;
            border-radius: 0.375rem;
            border-left: 3px solid #3b82f6;
        }

        .dark .ikp-info-item {
            background-color: rgba(59, 130, 246, 0.1);
            border-left-color: #60a5fa;
        }

        .ikp-info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .ikp-info-label {
            color: #9ca3af;
        }

        .ikp-info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
            margin-top: 0.25rem;
        }

        .dark .ikp-info-value {
            color: white;
        }
    </style>

    {{-- Header Section --}}
    <div class="ikp-header">
        <div class="ikp-header-content">
            {{-- Hospital Info Header --}}
            <div class="ikp-header-top">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <div class="ikp-logo-circle">
                            <svg style="width: 2.5rem; height: 2.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="ikp-title">Edit Laporan Insiden</h2>
                        <p class="ikp-subtitle">Sistem Pelaporan Insiden Keselamatan Pasien (IKP)</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="ikp-status-badge status-{{ str_replace('_', '-', $record->status ?? 'draft') }}">
                        <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>
                            @switch($record->status ?? 'draft')
                            @case('draft')
                            Draft
                            @break
                            @case('dilaporkan')
                            Dilaporkan
                            @break
                            @case('revisi')
                            Perlu Revisi
                            @break
                            @case('diverifikasi')
                            Diverifikasi
                            @break
                            @case('revisi_unit')
                            Revisi Unit
                            @break
                            @case('investigasi')
                            Investigasi
                            @break
                            @default
                            Draft
                            @endswitch
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                        Dibuat: {{ $record->created_at?->format('d F Y H:i') ?? 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Laporan Info Grid --}}
            <div class="ikp-info-grid">
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Nomor Laporan</div>
                    <div class="ikp-info-value">{{ $record->nomor_laporan ?? '-' }}</div>
                </div>
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Pelapor</div>
                    <div class="ikp-info-value">{{ $record->nama_pelapor ?? '-' }}</div>
                </div>
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Tanggal Insiden</div>
                    <div class="ikp-info-value">{{ $record->tanggal_insiden?->format('d F Y') ?? '-' }}</div>
                </div>
                <div class="ikp-info-item">
                    <div class="ikp-info-label">Jenis Insiden</div>
                    <div class="ikp-info-value">{{ $record->jenis_insiden ?? '-' }}</div>
                </div>
            </div>

            {{-- Important Notice --}}
            @if(in_array($record->status ?? 'draft', ['dilaporkan', 'revisi', 'revisi_unit', 'diverifikasi', 'investigasi']))
            <div class="ikp-notice">
                <h3 class="ikp-notice-title">⚠️ Catatan Edit Laporan</h3>
                <p class="ikp-notice-text">
                    Laporan ini sedang dalam proses. Perubahan yang Anda lakukan akan tersimpan dan dapat mempengaruhi status verifikasi.
                    @if($record->rejection_reason ?? false)
                    <br><br><strong>Alasan Pengembalian:</strong> {{ $record->rejection_reason }}
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Form Section --}}
    <div class="ikp-form-wrapper">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="ikp-form-footer">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        <p style="display: flex; align-items: center;">
                            <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Pastikan semua data sudah benar sebelum submit
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        @foreach ($this->getFormActions() as $action)
                        {{ $action }}
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Footer Info --}}
    <div class="ikp-footer-text">
        <p>Sistem Pelaporan IKP - Informasi dalam laporan ini bersifat rahasia dan hanya untuk keperluan internal</p>
        <p style="margin-top: 0.25rem;">Terakhir diperbarui: {{ $record->updated_at?->format('d F Y H:i') ?? 'N/A' }}</p>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>