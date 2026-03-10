<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use App\Models\UnitKerja;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;

class LaporanInsidenFormSchema
{

    public static function steps(bool $withAdminFields = false): array
    {
        $steps = [
            Step::make('Pelapor')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    static::sectionPelapor(),
                ]),

            Step::make('Insiden')
                ->icon('heroicon-o-exclamation-triangle')
                ->schema([
                    static::sectionInsiden(),
                ]),

            Step::make('Pasien')
                ->icon('heroicon-o-identification')
                ->schema([
                    static::sectionPasien(),
                ]),

            Step::make('Kronologi')
                ->icon('heroicon-o-document-text')
                ->schema([
                    static::sectionKronologi(),
                ]),

            Step::make('Kategori & Dampak')
                ->icon('heroicon-o-shield-exclamation')
                ->schema([
                    static::sectionKategoriDampak($withAdminFields),
                ]),

            Step::make('Tindakan')
                ->icon('heroicon-o-hand-raised')
                ->schema([
                    static::sectionTindakan($withAdminFields),
                ]),
        ];

        if ($withAdminFields) {
            $steps[] = Step::make('Catatan')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    static::sectionCatatanTambahan(),
                ]);

            $steps[] = Step::make('Status')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    static::sectionStatus(),
                ]);
        }

        return $steps;
    }

    /**
     * Kembalikan semua section form.
     *
     * @param bool $withAdminFields Sertakan field khusus admin (grading, status, catatan)
     */
    public static function sections(bool $withAdminFields = false): array
    {
        $sections = [
            static::sectionPelapor(),
            static::sectionInsiden(),
            static::sectionPasien(),
            static::sectionKronologi(),
            static::sectionKategoriDampak($withAdminFields),
            static::sectionTindakan($withAdminFields),
        ];

        if ($withAdminFields) {
            $sections[] = static::sectionCatatanTambahan();
            $sections[] = static::sectionStatus();
        }

        return $sections;
    }

    public static function sectionPelapor(): Section
    {
        return Section::make('📋 BAGIAN A: DATA PELAPOR')
            ->description('Identitas dan informasi kontak pelapor insiden')
            ->icon('heroicon-o-user-circle')
            ->schema([
                Grid::make(2)->schema([
                    Hidden::make('nama_pelapor')
                        ->label(''),

                    Forms\Components\Select::make('user_id')
                        ->label('Nama Lengkap Pelapor')
                        ->required()
                        ->live()
                        ->options(function (): array {
                            $authUser = static::getAuthenticatedUser();

                            if (static::shouldLockPelaporIdentityFields() && $authUser instanceof User) {
                                return User::query()
                                    ->whereKey($authUser->getKey())
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            }

                            return User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->default(fn(): ?int => Auth::id())
                        ->afterStateHydrated(function ($state, callable $set): void {
                            if (! static::shouldLockPelaporIdentityFields()) {
                                return;
                            }

                            $authUser = static::getAuthenticatedUser();
                            if (! $authUser instanceof User) {
                                return;
                            }

                            $set('user_id', $authUser->getKey());
                            $set('unit_kerja_id', $authUser->unitKerja()->first()?->id);
                        })
                        ->afterStateUpdated(function ($state, callable $set): void {
                            $selectedUser = User::with('unitKerja')->find($state);

                            if (! $selectedUser instanceof User) {
                                $set('unit_kerja_id', null);
                                return;
                            }

                            $set('unit_kerja_id', $selectedUser->unitKerja()->first()?->id);
                        })
                        ->disabled(fn(): bool => static::shouldLockPelaporIdentityFields())
                        ->dehydrated()
                        ->prefixIcon('heroicon-m-user')
                        ->placeholder('Pilih Pelapor'),

                    Forms\Components\Select::make('unit_kerja_id')
                        ->label('Unit Kerja / Departemen')
                        ->relationship('unitKerja', 'unit_name')
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required()
                        ->prefixIcon('heroicon-m-building-office')
                        ->helperText('Unit kerja pelapor')
                        ->placeholder('Pilih unit kerja')
                        ->default(function (): ?int {
                            return static::getAuthenticatedUser()?->unitKerja()->first()?->id;
                        })
                        ->options(function (): array {
                            $authUser = static::getAuthenticatedUser();

                            if (! $authUser instanceof User) {
                                return [];
                            }

                            if (static::shouldLockPelaporIdentityFields()) {
                                return $authUser->unitKerja()
                                    ->pluck('unit_name', 'unit_kerja.id')
                                    ->all();
                            }

                            return UnitKerja::query()->pluck('unit_name', 'id')->all();
                        })
                        ->afterStateHydrated(function ($state, callable $set): void {
                            if (! static::shouldLockPelaporIdentityFields() || filled($state)) {
                                return;
                            }

                            $set('unit_kerja_id', static::getAuthenticatedUser()?->unitKerja()->first()?->id);
                        })
                        ->disabled(fn(): bool => static::shouldLockPelaporIdentityFields())
                        ->dehydrated(),


                ]),

                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('nomor_telepon')
                        ->label('Nomor Telepon / HP')
                        ->tel()
                        ->prefixIcon('heroicon-m-phone')
                        ->placeholder('08xx-xxxx-xxxx'),

                    Forms\Components\DatePicker::make('tanggal_lapor')
                        ->label('Tanggal Pelaporan')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->prefixIcon('heroicon-m-calendar')
                        ->displayFormat('d F Y'),
                ]),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }

    protected static function getAuthenticatedUser(): ?User
    {
        $authUser = Auth::user();

        return $authUser instanceof User ? $authUser : null;
    }

    protected static function shouldLockPelaporIdentityFields(): bool
    {
        $authUser = static::getAuthenticatedUser();

        if (! $authUser instanceof User) {
            return false;
        }

        return ! $authUser->can('ViewAllData:LaporanInsiden');
    }

    public static function sectionInsiden(): Section
    {
        return Section::make('📍 BAGIAN B: RINCIAN KEJADIAN INSIDEN')
            ->description('Informasi lengkap tentang waktu dan tempat terjadinya insiden')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('jenis_insiden')
                        ->label('Jenis Insiden')
                        ->required()
                        ->options([
                            'KPC (Kondisi Potensial Cedera)' => 'KPC (Kondisi Potensial Cedera)',
                            'KNC (Kejadian Nyaris Cedera)'      => 'KNC (Kejadian Nyaris Cedera)',
                            'KTD (Kejadian Tidak Diharapkan)'   => 'KTD (Kejadian Tidak Diharapkan)',
                            'KTC (Kejadian Tidak Cedera)'       => 'KTC (Kejadian Tidak Cedera)',
                            'Sentinel'                          => 'Sentinel',
                        ])
                        ->native(false)
                        ->prefixIcon('heroicon-m-document-text')
                        ->helperText('Pilih jenis insiden yang terjadi'),

                    Forms\Components\TextInput::make('lokasi_insiden')
                        ->label('Lokasi Kejadian')
                        ->required()
                        ->prefixIcon('heroicon-m-map-pin')
                        ->placeholder('Contoh: Ruang IGD, Lantai 2 Bangsal A'),
                ]),

                Grid::make(2)->schema([
                    Forms\Components\DatePicker::make('tanggal_insiden')
                        ->label('Tanggal Insiden')
                        ->required()
                        ->native(false)
                        ->maxDate(now())
                        ->prefixIcon('heroicon-m-calendar-days')
                        ->displayFormat('d F Y')
                        ->helperText('Tanggal terjadinya insiden'),

                    Forms\Components\TimePicker::make('waktu_insiden')
                        ->label('Waktu Insiden')
                        ->required()
                        ->prefixIcon('heroicon-m-clock')
                        ->seconds(false)
                        ->helperText('Jam terjadinya insiden (format 24 jam)'),
                ]),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }

    public static function sectionPasien(): Section
    {
        return Section::make('👤 BAGIAN C: DATA PASIEN (Jika Terkait)')
            ->description('Lengkapi informasi pasien jika insiden melibatkan pasien')
            ->icon('heroicon-o-identification')
            ->schema([
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
            ])
            ->collapsible()
            ->compact();
    }

    public static function sectionKronologi(): Section
    {
        return Section::make('📝 BAGIAN D: KRONOLOGI KEJADIAN')
            ->description('Uraikan secara detail dan kronologis bagaimana insiden terjadi')
            ->icon('heroicon-o-document-text')
            ->schema([
                Forms\Components\Textarea::make('kronologi')
                    ->label('Kronologi Lengkap Insiden')
                    ->required()
                    ->rows(8)
                    ->helperText('Jelaskan secara detail, runtut, dan kronologis bagaimana insiden terjadi dari awal hingga akhir.')
                    ->placeholder('Contoh: Pada pukul 10.00 WIB, pasien sedang berada di ruang rawat inap ketika...')
                    ->columnSpanFull(),

                Forms\Components\Radio::make('insiden_terjadi_pada')
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

                Forms\Components\TextInput::make('insiden_terjadi_pada_lainnya')
                    ->label('Sebutkan Lainnya')
                    ->placeholder('Jelaskan kepada siapa insiden terjadi')
                    ->prefixIcon('heroicon-m-pencil')
                    ->visible(fn(Get $get) => $get('insiden_terjadi_pada') === 'Lainnya')
                    ->required(fn(Get $get) => $get('insiden_terjadi_pada') === 'Lainnya'),
            ])
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }

    /**
     * @param bool $withGrading Tampilkan field grading_risiko (admin) atau note info (publik)
     */
    public static function sectionKategoriDampak(bool $withGrading = false): Section
    {
        $schema = [
            Grid::make(2)->schema([
                Select::make('kategori_insiden')
                    ->label('Kategori Insiden')
                    ->options([
                        'Medication / Cairan IV'              => 'Medication / Cairan IV',
                        'Prosedur Klinis'                    => 'Prosedur Klinis',
                        'Diagnostik'                         => 'Diagnostik',
                        'Infeksi Terkait Pelayanan Kesehatan' => 'Infeksi Terkait Pelayanan Kesehatan',
                        'Pasien Jatuh'                       => 'Pasien Jatuh',
                        'Identifikasi Pasien'                => 'Identifikasi Pasien',
                        'Komunikasi'                         => 'Komunikasi',
                        'Dokumentasi Klinis'                 => 'Dokumentasi Klinis',
                        'Peralatan Medis'                    => 'Peralatan Medis',
                        'Transfusi Darah / Produk Darah'     => 'Transfusi Darah / Produk Darah',
                        'Administrasi / Proses Pelayanan'    => 'Administrasi / Proses Pelayanan',
                        'Lingkungan / Fasilitas'             => 'Lingkungan / Fasilitas',
                        'Faktor Manusia'                     => 'Faktor Manusia',
                        'Lainnya'                            => 'Lainnya',
                    ])
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('dampak_insiden')
                    ->label('Dampak Insiden')
                    ->required()
                    ->options([
                        'Tidak ada cedera' => '✅ Tidak ada cedera',
                        'Cedera ringan'    => '🟡 Cedera ringan',
                        'Cedera sedang'    => '🟠 Cedera sedang',
                        'Cedera berat'     => '🔴 Cedera berat',
                        'Meninggal'        => '⚫ Meninggal',
                    ])
                    ->native(false)
                    ->default('Tidak ada cedera')
                    ->prefixIcon('heroicon-m-heart')
                    ->helperText('Tingkat dampak yang dialami'),

                Forms\Components\TextArea::make('deskripsi_kategori_insiden')
                    ->label('Deskripsi Insiden')
                    ->required()
                    ->rows(8)
                    ->helperText('Jelaskan secara rinci kategori insiden yang dipilih, termasuk faktor penyebab dan kondisi yang berkontribusi.')
                    ->placeholder('Contoh: Insiden terkait medication terjadi karena kesalahan pemberian obat oleh petugas, dimana pasien menerima obat yang salah dosisnya. Faktor penyebabnya adalah kurangnya komunikasi antara petugas dan kurang teliti dalam membaca label obat.')
                    ->columnSpanFull(),
            ]),
        ];

        if ($withGrading) {
            $schema[] = static::sectionGradingResiko();
        } else {
            $schema[] = TextEntry::make('info_grading')
                ->state('ℹ️ Catatan: Grading risiko akan diisi oleh Tim IKP / Validator setelah laporan disubmit.')
                ->columnSpanFull();
        }

        return Section::make('⚠️ BAGIAN E: KATEGORI DAN DAMPAK INSIDEN')
            ->description('Klasifikasi jenis dan tingkat dampak insiden yang terjadi')
            ->icon('heroicon-o-shield-exclamation')
            ->schema($schema)
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }

    public static function sectionGradingResiko(): ToggleButtons
    {
        return Forms\Components\ToggleButtons::make('grading_risiko')
            ->label('Grading Risiko')
            ->required()
            ->options([
                'Biru'   => '🔵 Biru (Tidak signifikan)',
                'Hijau'  => '🟢 Hijau (Minor)',
                'Kuning' => '🟡 Kuning (Moderat)',
                'Merah'  => '🔴 Merah (Mayor)',
                'Hitam'  => '⚫ Hitam (Katastropik)',
            ])
            ->colors([
                'Biru'   => 'info',
                'Hijau'  => 'success',
                'Kuning' => 'warning',
                'Merah'  => 'danger',
                'Hitam'  => 'gray',
            ])
            ->inline()
            ->helperText('Hanya diisi oleh Validator / Tim IKP')
            ->visibleOn('edit');
    }

    public static function sectionTindakan(bool $withAnalysis = false): Section
    {
        $fields = [
            Forms\Components\Textarea::make('tindakan_dilakukan')
                ->label('Tindakan yang Telah Dilakukan Setelah Insiden')
                ->required()
                ->rows(6)
                ->helperText('Jelaskan seluruh tindakan yang telah dilakukan setelah insiden terjadi.')
                ->placeholder("Contoh:\n1. Segera memberikan pertolongan pertama\n2. Menghubungi dokter jaga\n3. Melaporkan kepada kepala ruangan")
                ->columnSpanFull(),
        ];

        if ($withAnalysis) {
            $fields[] = Forms\Components\Textarea::make('analisis_penyebab')
                ->label('Analisis Penyebab Insiden')
                ->rows(6)
                ->helperText('Analisis mendalam tentang penyebab insiden, faktor yang berkontribusi, dan rencana tindakan pencegahan ke depan.')
                ->placeholder("Contoh:\nPenyebab utama insiden adalah kurangnya komunikasi antara petugas saat shift change. Faktor yang berkontribusi termasuk kurangnya standar komunikasi yang jelas dan tidak adanya checklist handover. Rencana tindakan pencegahan meliputi implementasi SBAR untuk komunikasi antar shift dan pelatihan ulang bagi seluruh staf.")
                ->columnSpanFull();
        };

        return Section::make('🩹 BAGIAN F: TINDAKAN YANG DILAKUKAN')
            ->description('Tindakan yang telah dilakukan setelah terjadinya insiden')
            ->icon('heroicon-o-hand-raised')
            ->schema($fields)
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }

    public static function sectionCatatanTambahan(): Section
    {
        return Section::make('📎 CATATAN TAMBAHAN')
            ->description('Informasi tambahan (opsional)')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Forms\Components\Textarea::make('catatan_tambahan')
                    ->label('Catatan Tambahan')
                    ->rows(5)
                    ->helperText('(Opsional) Informasi tambahan yang belum tercakup di bagian sebelumnya')
                    ->placeholder('Tuliskan informasi tambahan jika diperlukan')
                    ->columnSpanFull(),
            ])
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->compact();
    }

    public static function sectionStatus(): Section
    {
        return Section::make('📌 Status Laporan')
            ->icon('heroicon-o-check-circle')
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Status Laporan')
                    ->options([
                        'draft'        => 'Draft',
                        'dilaporkan'   => 'Dilaporkan',
                        'revisi'       => 'Revisi',
                        'diverifikasi' => 'Diverifikasi',
                        'revisi_unit'  => 'Revisi Unit',
                        'investigasi'  => 'Investigasi',
                    ])
                    ->default('draft')
                    ->required()
                    ->native(false),
            ])
            ->visibleOn('edit');
    }
}
