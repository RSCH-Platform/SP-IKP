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
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: #dbeafe;
            color: #1e40af;
        }

        .dark .ikp-badge {
            background-color: rgba(30, 64, 175, 0.3);
            color: #93c5fd;
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

        .ikp-notice-list {
            font-size: 0.875rem;
            color: #b45309;
            list-style: disc;
            padding-left: 1.25rem;
        }

        .dark .ikp-notice-list {
            color: #fcd34d;
        }

        .ikp-notice-list li {
            margin-bottom: 0.25rem;
        }

        .ikp-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .ikp-step {
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .ikp-step-1 {
            background-color: #eff6ff;
        }

        .dark .ikp-step-1 {
            background-color: rgba(59, 130, 246, 0.1);
        }

        .ikp-step-2 {
            background-color: #f0fdf4;
        }

        .dark .ikp-step-2 {
            background-color: rgba(34, 197, 94, 0.1);
        }

        .ikp-step-3 {
            background-color: #faf5ff;
        }

        .dark .ikp-step-3 {
            background-color: rgba(168, 85, 247, 0.1);
        }

        .ikp-step-number {
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .ikp-step-1 .ikp-step-number {
            background-color: #3b82f6;
        }

        .ikp-step-2 .ikp-step-number {
            background-color: #22c55e;
        }

        .ikp-step-3 .ikp-step-number {
            background-color: #a855f7;
        }

        .ikp-step-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
        }

        .dark .ikp-step-title {
            color: white;
        }

        .ikp-step-desc {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .dark .ikp-step-desc {
            color: #9ca3af;
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="ikp-title">Laporan Insiden Internal</h2>
                        <p class="ikp-subtitle">Sistem Pelaporan Insiden Keselamatan Pasien (IKP)</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="ikp-badge">
                        <svg style="width: 1rem; height: 1rem; margin-right: 0.25rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Rahasia & Konfidensial
                    </div>
                    <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                        Tanggal: {{ now()->format('d F Y') }}
                    </p>
                </div>
            </div>

            {{-- Important Notice --}}
            <div class="ikp-notice">
                <h3 class="ikp-notice-title">⚠️ Catatan Penting</h3>
                <ul class="ikp-notice-list">
                    <li><strong>RAHASIA:</strong> Laporan ini tidak boleh difotocopy dan dilaporkan maksimal 2x24 jam</li>
                    <li>Mohon isi semua informasi dengan lengkap dan akurat</li>
                    <li>Laporan dapat disimpan sebagai <strong>Draft</strong> untuk dilanjutkan kemudian</li>
                    <li>Setelah <strong>Submit</strong>, laporan akan masuk ke sistem review</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="ikp-form-wrapper">
        <form wire:submit="submit">
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
                    <div style="display: flex; gap: 0.75rem;">
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
        <p style="margin-top: 0.25rem;">Untuk bantuan, hubungi Tim Keselamatan Pasien</p>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>