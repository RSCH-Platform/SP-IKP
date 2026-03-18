<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Filament\Resources\LaporanInsidens\Schemas\LaporanInsidenFormOptions;
use App\Filament\Resources\LaporanInsidens\Schemas\Sections\GradingResikoSection;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class InsidenSection
{
    public static function make(bool $withGrading = false): Section
    {
        $schema = [
            Grid::make(2)->schema([
                LaporanInsidenFormOptions::makeSelect('jenis_insiden', 'Jenis Insiden', LaporanInsidenFormOptions::JENIS_INSIDEN_OPTIONS)
                    ->required()
                    ->prefixIcon('heroicon-m-document-text')
                    ->helperText('Pilih jenis insiden yang terjadi'),

                TextInput::make('lokasi_insiden')
                    ->label('Lokasi Kejadian')
                    ->required()
                    ->prefixIcon('heroicon-m-map-pin')
                    ->placeholder('Contoh: Ruang IGD, Lantai 2 Bangsal A'),
            ]),

            Grid::make(2)->schema([
                DatePicker::make('tanggal_insiden')
                    ->label('Tanggal Insiden')
                    ->required()
                    ->native(false)
                    ->maxDate(now())
                    ->prefixIcon('heroicon-m-calendar-days')
                    ->displayFormat('d F Y')
                    ->helperText('Tanggal terjadinya insiden'),

                TimePicker::make('waktu_insiden')
                    ->label('Waktu Insiden')
                    ->required()
                    ->prefixIcon('heroicon-m-clock')
                    ->seconds(false)
                    ->helperText('Jam terjadinya insiden (format 24 jam)'),
            ]),

            Grid::make(2)->schema([
                LaporanInsidenFormOptions::makeSelect('kategori_insiden', 'Kategori Insiden', LaporanInsidenFormOptions::KATEGORI_INSIDEN_OPTIONS)
                    ->required()
                    ->searchable(),

                LaporanInsidenFormOptions::makeSelect('dampak_insiden', 'Dampak Insiden', LaporanInsidenFormOptions::DAMPAK_INSIDEN_OPTIONS)
                    ->required()
                    ->default('Tidak ada cedera')
                    ->prefixIcon('heroicon-m-heart')
                    ->helperText('Tingkat dampak yang dialami'),

                Textarea::make('deskripsi_kategori_insiden')
                    ->label('Deskripsi Insiden')
                    ->required()
                    ->rows(8)
                    ->helperText('Jelaskan secara rinci kategori insiden yang dipilih, termasuk faktor penyebab dan kondisi yang berkontribusi.')
                    ->placeholder('Contoh: Insiden terkait medication terjadi karena kesalahan pemberian obat oleh petugas, dimana pasien menerima obat yang salah dosisnya. Faktor penyebabnya adalah kurangnya komunikasi antara petugas dan kurang teliti dalam membaca label obat.')
                    ->columnSpanFull(),
            ]),

            Radio::make('insiden_terjadi_pada')
                ->label('Insiden Terjadi Pada')
                ->required()
                ->options([
                    'Pasien'    => 'Pasien',
                    'Petugas'   => 'Petugas / Staf',
                    'Pengunjung' => 'Pengunjung / Keluarga',
                    'Lainnya'   => 'Lainnya',
                ])
                ->default('Pasien')
                ->inline()
                ->inlineLabel(false)
                ->descriptions([
                    'Pasien'    => 'Insiden menimpa pasien yang sedang dirawat',
                    'Petugas'   => 'Insiden menimpa petugas/staf rumah sakit',
                    'Pengunjung' => 'Insiden menimpa pengunjung atau keluarga pasien',
                    'Lainnya'   => 'Selain ketiga kategori di atas',
                ])
                ->live(),

            TextInput::make('insiden_terjadi_pada_lainnya')
                ->label('Sebutkan Lainnya')
                ->placeholder('Jelaskan kepada siapa insiden terjadi')
                ->prefixIcon('heroicon-m-pencil')
                ->visible(fn(Get $get) => $get('insiden_terjadi_pada') === 'Lainnya')
                ->required(fn(Get $get) => $get('insiden_terjadi_pada') === 'Lainnya'),
        ];

        if ($withGrading) {
            $schema[] = GradingResikoSection::make();
        }

        return Section::make('📍 BAGIAN B: RINCIAN KEJADIAN INSIDEN')
            ->description('Informasi lengkap tentang waktu dan tempat terjadinya insiden')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema($schema)
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }
}
