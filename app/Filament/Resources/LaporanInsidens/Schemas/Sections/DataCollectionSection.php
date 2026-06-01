<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Dom\Text;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Cache;

class DataCollectionSection
{
    private static function documentUpload(string $label, array $acceptedFileTypes, string $helperText): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('document_upload')
            ->label($label)
            ->collection('investigation_documents')
            ->disk(config('media-library.disk_name', 'public'))
            ->directory(function (callable $get, $record) {
                return $record?->laporanInsiden?->getMediaFolderPath() ?? '';
            })
            ->downloadable()
            ->preserveFilenames()
            ->columnSpanFull()
            ->maxSize(20480)
            ->acceptedFileTypes($acceptedFileTypes)
            ->helperText($helperText);
    }

    public static function make(): Section
    {
        return Section::make('📊 Data Investigasi')
            ->description('Kumpulan data pengumpulan investigasi sederhana')
            ->icon('heroicon-o-chart-bar')
            ->schema([
                Tabs::make('InvestigationTabs')
                    ->tabs(function ($record) {
                        if (! $record) {
                            return [
                                Tab::make('setup')
                                    ->label('⚙️ Setup')
                                    ->schema([
                                        TextEntry::make('info')
                                            ->content('Simpan laporan terlebih dahulu untuk menambahkan investigasi.'),
                                    ]),
                            ];
                        }

                        // Cache investigation data counts untuk mengurangi queries
                        $cacheKey = "investigation_counts_{$record->id}";
                        $counts = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($record) {
                            return [
                                'interview' => $record->investigationData()->where('kategori', 'interview')->count(),
                                'review_dokumen' => $record->investigationData()->where('kategori', 'review_dokumen')->count(),
                                'observasi' => $record->investigationData()->where('kategori', 'observasi')->count(),
                            ];
                        });

                        return [
                            Tab::make('Interview')
                                ->icon('heroicon-m-microphone')
                                ->badge($counts['interview'] ?: null)
                                ->schema([
                                    Repeater::make('interview_data')
                                        ->relationship(
                                            'investigationData',
                                            fn($query) => $query->where('kategori', 'interview')
                                        )
                                        ->schema([
                                            TextInput::make('sumber')
                                                ->label('Narasumber')
                                                ->required()
                                                ->placeholder('Contoh: Perawat Rina'),

                                            Textarea::make('hasil')
                                                ->label('Hasil Interview')
                                                ->rows(5)
                                                ->required(),

                                            Hidden::make('kategori')
                                                ->default('interview'),
                                        ])
                                        ->addActionLabel('Tambah Interview')
                                        ->reorderable()
                                        ->collapsible()
                                        ->minItems(0)
                                        ->itemLabel(function (array $state): ?string {
                                            $sumber = $state['sumber'] ?? null;
                                            $hasil = $state['hasil'] ?? null;

                                            if ($sumber && $hasil) {
                                                return "Interview dengan {$sumber}";
                                            }

                                            return null;
                                        }),
                                ]),

                            Tab::make('Review Dokumen')
                                ->icon('heroicon-m-document-text')
                                ->badge($counts['review_dokumen'] ?: null)
                                ->schema([
                                    Repeater::make('review_data')
                                        ->relationship(
                                            'investigationData',
                                            fn($query) => $query->where('kategori', 'review_dokumen')
                                        )
                                        ->schema([
                                            TextInput::make('sumber')
                                                ->label('Nama Dokumen')
                                                ->required()
                                                ->placeholder('Contoh: SOP Keselamatan Pasien'),

                                            self::documentUpload(
                                                'Unggah Dokumen Pendukung',
                                                [
                                                    'application/pdf',
                                                    'image/jpeg',
                                                    'image/png',
                                                    'application/msword',
                                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                    'application/vnd.ms-excel',
                                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                ],
                                                'File yang didukung: PDF, Word, Excel, Gambar. Maks. 20MB',
                                            ),

                                            Textarea::make('hasil')
                                                ->label('Hasil Review')
                                                ->rows(5)
                                                ->required(),

                                            Hidden::make('kategori')
                                                ->default('review_dokumen'),
                                        ])
                                        ->addActionLabel('Tambah Review Dokumen')
                                        ->reorderable()
                                        ->collapsible()
                                        ->minItems(0)
                                        ->itemLabel(function (array $state): ?string {
                                            $sumber = $state['sumber'] ?? null;
                                            $hasil = $state['hasil'] ?? null;

                                            if ($sumber && $hasil) {
                                                return "Review Dokumen: {$sumber}";
                                            }

                                            return null;
                                        }),
                                ]),

                            Tab::make('Observasi')
                                ->icon('heroicon-m-eye')
                                ->badge($counts['observasi'] ?: null)
                                ->schema([
                                    Repeater::make('observasi_data')
                                        ->relationship(
                                            'investigationData',
                                            fn($query) => $query->where('kategori', 'observasi')
                                        )
                                        ->schema([
                                            TextInput::make('lokasi')
                                                ->label('Lokasi Observasi')
                                                ->required()
                                                ->placeholder('Contoh: Ruang IGD'),

                                            self::documentUpload(
                                                'Upload Foto / Bukti',
                                                [
                                                    'image/jpeg',
                                                    'image/png',
                                                    'image/gif',
                                                    'application/pdf',
                                                ],
                                                'File yang didukung: JPG, PNG, GIF, PDF. Maks. 20MB',
                                            ),

                                            Textarea::make('hasil')
                                                ->label('Hasil Observasi')
                                                ->rows(5)
                                                ->required(),

                                            Hidden::make('kategori')
                                                ->default('observasi'),
                                        ])
                                        ->addActionLabel('Tambah Observasi')
                                        ->reorderable()
                                        ->collapsible()
                                        ->minItems(0)
                                        ->itemLabel(function (array $state): ?string {
                                            $lokasi = $state['lokasi'] ?? null;
                                            $hasil = $state['hasil'] ?? null;

                                            if ($lokasi && $hasil) {
                                                return "Observasi di {$lokasi}";
                                            }

                                            return null;
                                        }),
                                ]),
                        ];
                    })
                    ->persistTabInQueryString(),
            ])
            ->collapsible();
    }
}
