<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\LaporanInsiden;
use Filament\Forms;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PasienSection
{
    public static function make(bool $collapsed = true): Section
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

            Grid::make(2)->schema([
                Forms\Components\DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk RS')
                    ->native(false)
                    ->maxDate(now())
                    ->prefixIcon('heroicon-m-calendar-days')
                    ->displayFormat('d F Y')
                    ->helperText('Tanggal pasien masuk rumah sakit')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\DatePicker $component, mixed $state, ?LaporanInsiden $record): void {
                        if (blank($state) && !empty($record?->tanggal_masuk_rs)) {
                            $tgl = $record->tanggal_masuk_rs instanceof \DateTimeInterface
                                ? $record->tanggal_masuk_rs
                                : \Carbon\Carbon::parse($record->tanggal_masuk_rs);
                            $component->state($tgl->format('Y-m-d'));
                        }
                    })
                    ->afterStateUpdated(function (Forms\Components\DatePicker $component, Get $get, Set $set): void {
                        $tanggal = $component->getState();
                        if (blank($tanggal)) {
                            $set('tanggal_masuk_rs', null);
                            return;
                        }
                        $dateOnly = substr(trim((string)$tanggal), 0, 10);
                        $jam = $get('jam_masuk');
                        $jamString = filled($jam) ? (strlen($jam) === 5 ? "{$jam}:00" : substr($jam, 0, 8)) : '00:00:00';
                        $set('tanggal_masuk_rs', "{$dateOnly} {$jamString}");
                    }),

                Forms\Components\TimePicker::make('jam_masuk')
                    ->label('Waktu Masuk RS')
                    ->prefixIcon('heroicon-m-clock')
                    ->seconds(false)
                    ->helperText('Jam pasien masuk rumah sakit (format 24 jam)')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\TimePicker $component, mixed $state, ?LaporanInsiden $record): void {
                        if (blank($state) && !empty($record?->tanggal_masuk_rs)) {
                            $tgl = $record->tanggal_masuk_rs instanceof \DateTimeInterface
                                ? $record->tanggal_masuk_rs
                                : \Carbon\Carbon::parse($record->tanggal_masuk_rs);
                            $component->state($tgl->format('H:i'));
                        }
                    })
                    ->afterStateUpdated(function (Forms\Components\TimePicker $component, Get $get, Set $set): void {
                        $jam = $component->getState();
                        $tanggal = $get('tanggal_masuk');
                        if (blank($tanggal)) {
                            $set('tanggal_masuk_rs', null);
                            return;
                        }
                        $dateOnly = substr(trim((string)$tanggal), 0, 10);
                        $jamString = filled($jam) ? (strlen($jam) === 5 ? "{$jam}:00" : substr($jam, 0, 8)) : '00:00:00';
                        $set('tanggal_masuk_rs', "{$dateOnly} {$jamString}");
                    }),
            ]),

            Forms\Components\Hidden::make('tanggal_masuk_rs')
                ->dehydrated(true)
                ->afterStateHydrated(function (Forms\Components\Hidden $component, mixed $state, ?LaporanInsiden $record): void {
                    if (blank($state) && !empty($record?->tanggal_masuk_rs)) {
                        $tgl = $record->tanggal_masuk_rs instanceof \DateTimeInterface
                            ? $record->tanggal_masuk_rs
                            : \Carbon\Carbon::parse($record->tanggal_masuk_rs);
                        $component->state($tgl->format('Y-m-d H:i:s'));
                    }
                })
                ->dehydrateStateUsing(function (mixed $state, Get $get): ?string {
                    $tanggal = $get('tanggal_masuk');
                    if (blank($tanggal)) {
                        return null;
                    }

                    $dateOnly = substr(trim((string)$tanggal), 0, 10);
                    $jam = $get('jam_masuk');
                    $jamString = filled($jam) ? (strlen($jam) === 5 ? "{$jam}:00" : substr($jam, 0, 8)) : '00:00:00';

                    return "{$dateOnly} {$jamString}";
                }),

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
            // ->visible(fn(Get $get) => $get('insiden_terjadi_pada') === 'Pasien')
            ->schema($schema)
            ->collapsible()
            ->collapsed($collapsed)
            ->compact();
    }
}
