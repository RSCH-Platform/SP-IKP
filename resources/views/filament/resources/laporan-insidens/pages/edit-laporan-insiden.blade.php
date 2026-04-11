<x-filament-panels::page x-data="{ activeTab: 'form' }">
    <style>
        .ikp-tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .dark .ikp-tabs {
            border-color: rgb(55 65 81);
        }

        .ikp-tab-button {
            padding: 0.75rem 1.5rem;
            border-bottom: 3px solid transparent;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dark .ikp-tab-button {
            color: #9ca3af;
        }

        .ikp-tab-button:hover {
            color: #111827;
            background-color: #f9fafb;
        }

        .dark .ikp-tab-button:hover {
            color: white;
            background-color: rgba(59, 130, 246, 0.1);
        }

        .ikp-tab-button.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }

        .dark .ikp-tab-button.active {
            color: #60a5fa;
            border-bottom-color: #60a5fa;
        }

        .ikp-tab-content {
            display: none;
        }

        .ikp-tab-content.active {
            display: block;
        }

        .preview-container {
            background-color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .dark .preview-container {
            background-color: rgb(31 41 55);
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .dark .ikp-form-footer {
            border-color: rgb(55 65 81);
        }
    </style>

    {{-- Tab Navigation --}}
    <div class="ikp-tabs">
        <button
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'form' }"
            @click="activeTab = 'form'">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Form Edit
        </button>
        <button
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'preview' }"
            @click="activeTab = 'preview'">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Preview Laporan
        </button>
        @if($record->investigation_started_at)
        <button
            class="ikp-tab-button"
            :class="{ 'active': activeTab === 'investigasi' }"
            @click="activeTab = 'investigasi'">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Preview Investigasi
        </button>
        @else
        <button
            class="ikp-tab-button"
            disabled
            style="opacity: 0.5; cursor: not-allowed;"
            title="Investigasi belum dimulai">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Preview Investigasi
        </button>
        @endif
    </div>

    {{-- Tab Contents --}}
    {{-- Tab 1: Form Edit --}}
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'form' }">
        <div class="ikp-form-wrapper">
            @if($record->investigation_started_at && $record->investigationStarter)
            <div style="background-color: #ecfdf5; border: 1px solid #86efac; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                <p style="font-size: 0.875rem; color: #166534; margin: 0;">
                    <svg style="width: 1rem; height: 1rem; display: inline-block; margin-right: 0.5rem; vertical-align: middle;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <strong>Investigasi dimulai oleh:</strong> {{ $record->investigationStarter->name ?? '-' }} pada {{ $record->investigation_started_at->translatedFormat('d F Y H:i') ?? '-' }}
                </p>
            </div>
            @endif
            {{ $this->form }}

            <div class="ikp-form-footer">
                <div style="font-size: 0.875rem; color: #6b7280;">
                    <p style="display: flex; align-items: center; margin: 0;">
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
    </div>

    {{-- Tab 2: Preview Laporan --}}
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'preview' }">
        <div class="preview-container">
            @include('filament.resources.laporan-insidens.pages.preview-laporan-insiden-content')
        </div>
    </div>

    {{-- Tab 3: Preview Investigasi --}}
    @if($record->investigation_started_at)
    <div class="ikp-tab-content" :class="{ 'active': activeTab === 'investigasi' }">
        <div class="preview-container">
            @include('filament.resources.laporan-insidens.pages.preview-investigasi-laporan-insiden-content')
        </div>
    </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>