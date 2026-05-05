<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\LaporanInsiden;
use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class PasienSection
{
    public static function make(): Section
    {
        $schema = [
            Grid::make(3)->schema([
                Forms\Components\TextInput::make('nama_pasien')
                    ->label('Nama Pasien')
                    ->prefixIcon('heroicon-m-user')
                    ->placeholder('Nama lengkap pasien'),

                Forms\Components\TextInput::make('nomor_rekam_medis')
                    ->label('No. Rekam Medis')
                    ->prefixIcon('heroicon-m-document-duplicate')
                    ->placeholder('No. RM'),

                Forms\Components\TextInput::make('ruangan')
                    ->label('Ruangan / Bangsal')
                    ->prefixIcon('heroicon-m-home')
                    ->placeholder('Contoh: Ruang Anggrek'),
            ]),

            Fieldset::make('Informasi Demografi')
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\TextInput::make('umur')
                                ->label('Umur')
                                ->numeric()
                                ->suffix('tahun')
                                ->minValue(0)
                                ->maxValue(150)
                                ->placeholder('0'),

                            Forms\Components\Select::make('kelompok_umur')
                                ->label('Kelompok Umur')
                                ->options([
                                    '0-1 bulan'           => '0-1 bulan',
                                    '>1 bulan - 1 tahun'  => '>1 bulan - 1 tahun',
                                    '>1 tahun - 5 tahun'  => '>1 tahun - 5 tahun',
                                    '>5 tahun - 15 tahun' => '>5 tahun - 15 tahun',
                                    '>15 tahun - 30 tahun' => '>15 tahun - 30 tahun',
                                    '>30 tahun - 65 tahun' => '>30 tahun - 65 tahun',
                                    '>65 tahun'           => '>65 tahun',
                                ])
                                ->native(false)
                                ->placeholder('Pilih kelompok'),

                            Forms\Components\Select::make('jenis_kelamin')
                                ->label('Jenis Kelamin')
                                ->options([
                                    'Laki-laki' => '👨 Laki-laki',
                                    'Perempuan' => '👩 Perempuan',
                                ])
                                ->native(false)
                                ->placeholder('Pilih'),

                            Forms\Components\Select::make('penanggung_biaya')
                                ->label('Penanggung Biaya')
                                ->options([
                                    'Pribadi'        => 'Pribadi',
                                    'BPJS'           => 'BPJS',
                                    'Asuransi Swasta' => 'Asuransi Swasta',
                                    'Lainnya'        => 'Lainnya',
                                ])
                                ->native(false)
                                ->placeholder('Pilih'),
                        ]),
                ]),

            Forms\Components\DateTimePicker::make('tanggal_masuk_rs')
                ->label('Tanggal & Waktu Masuk RS')
                ->native(false)
                ->maxDate(now())
                ->prefixIcon('heroicon-m-arrow-right-on-rectangle')
                ->displayFormat('d F Y, H:i')
                ->seconds(false),

            Fieldset::make('Detail Insiden Terkait Pasien')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Select::make('pelapor_insiden_pasien')
                        ->columnSpanFull()
                        ->label('Orang Pertama Yang Melaporkan Insiden')
                        ->required()
                        ->options(LaporanInsiden::PELAPOR_INSIDEN_PASIEN_OPTIONS)
                        ->live()
                        ->native(false)
                        ->placeholder('Pilih'),

                    Forms\Components\TextInput::make('pelapor_insiden_pasien_lainnya')
                        ->label('Sebutkan Lainnya')
                        ->placeholder('Jelaskan siapa yang melaporkan insiden terkait pasien')
                        ->prefixIcon('heroicon-m-pencil')
                        ->visible(fn(Get $get) => $get('pelapor_insiden_pasien') === 'Lainnya')
                        ->required(fn(Get $get) => $get('pelapor_insiden_pasien') === 'Lainnya'),

                    Forms\Components\Select::make('insiden_menyangkut_pasien')
                        ->label('Insiden menyangkut pasien')
                        ->columnSpanFull()
                        ->options(LaporanInsiden::INSIDEN_MENYANGKUT_PASIEN_OPTIONS)
                        ->live()
                        ->required()
                        ->native(false)
                        ->placeholder('Pilih'),

                    Forms\Components\TextInput::make('insiden_menyangkut_pasien_lainnya')
                        ->label('Sebutkan Lainnya')
                        ->placeholder('Jelaskan siapa yang melaporkan insiden terkait pasien')
                        ->prefixIcon('heroicon-m-pencil')
                        ->visible(fn(Get $get) => $get('insiden_menyangkut_pasien') === 'Lainnya')
                        ->required(fn(Get $get) => $get('insiden_menyangkut_pasien') === 'Lainnya'),
                    Forms\Components\Select::make('spesialisasi_pasien')
                        ->label('Insiden terjadi pada pasien : (sesuai kasus penyakit / spesialisasi) ')
                        ->columnSpanFull()
                        ->options(LaporanInsiden::SPESIALISASI_PASIEN_OPTIONS)
                        ->live()
                        ->required()
                        ->native(false)
                        ->placeholder('Pilih'),

                    Forms\Components\TextInput::make('spesialisasi_pasien_lainnya')
                        ->label('Sebutkan Lainnya')
                        ->placeholder('Jelaskan spesialisasi pasien sesuai kasus penyakitnya')
                        ->prefixIcon('heroicon-m-pencil')
                        ->visible(fn(Get $get) => $get('spesialisasi_pasien') === 'Lainnya')
                        ->required(fn(Get $get) => $get('spesialisasi_pasien') === 'Lainnya'),
                ]),
        ];

        return Section::make('BAGIAN B: DATA PASIEN')
            ->description('Lengkapi informasi pasien jika insiden melibatkan pasien')
            ->icon('heroicon-o-identification')
            ->visible(fn(Get $get) => $get('insiden_terjadi_pada') === 'Pasien')
            ->schema($schema)
            ->collapsible()
            ->collapsed()
            ->compact();
    }
}
