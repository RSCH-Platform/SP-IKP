<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;

class DataCollectionSection
{
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

                        return [
                            Tab::make('Interview')
                                ->icon('heroicon-m-microphone')
                                ->badge(
                                    $record->investigationData()
                                        ->where('kategori', 'interview')
                                        ->count()
                                )
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
                                ->badge(
                                    $record->investigationData()
                                        ->where('kategori', 'review_dokumen')
                                        ->count()
                                )
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

                                            FileUpload::make('file_path')
                                                ->label('Upload Dokumen')
                                                ->directory('investigasi')
                                                ->visibility('private')
                                                ->acceptedFileTypes([
                                                    'application/pdf',
                                                    'image/jpeg',
                                                    'image/png',
                                                    'application/msword',
                                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                ])
                                                ->maxSize(5120),

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
                                ->badge(
                                    $record->investigationData()
                                        ->where('kategori', 'observasi')
                                        ->count()
                                )
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

                                            FileUpload::make('file_path')
                                                ->label('Upload Foto / Bukti')
                                                ->directory('investigasi')
                                                ->visibility('private')
                                                ->acceptedFileTypes([
                                                    'image/jpeg',
                                                    'image/png',
                                                    'image/gif',
                                                    'application/pdf',
                                                ])
                                                ->maxSize(5120),

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
