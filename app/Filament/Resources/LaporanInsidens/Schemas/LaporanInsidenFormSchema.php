<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use App\Models\TimelineCategory;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\ProblemContributor;
use App\Models\ProblemContributorCategory;
use App\Models\ProblemContributorComponent;
use App\Models\ProblemContributorSubComponent;
use App\Models\ProblemContributorDescription;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Form;

class LaporanInsidenFormSchema
{
    private const JENIS_INSIDEN_OPTIONS = [
        'KPC (Kondisi Potensial Cedera)' => 'KPC (Kondisi Potensial Cedera)',
        'KNC (Kejadian Nyaris Cedera)' => 'KNC (Kejadian Nyaris Cedera)',
        'KTD (Kejadian Tidak Diharapkan)' => 'KTD (Kejadian Tidak Diharapkan)',
        'KTC (Kejadian Tidak Cedera)' => 'KTC (Kejadian Tidak Cedera)',
        'Sentinel' => 'Sentinel',
    ];

    private const DAMPAK_INSIDEN_OPTIONS = [
        'Tidak ada cedera' => '✅ Tidak ada cedera',
        'Cedera ringan' => '🟡 Cedera ringan',
        'Cedera sedang' => '🟠 Cedera sedang',
        'Cedera berat' => '🔴 Cedera berat',
        'Meninggal' => '⚫ Meninggal',
    ];

    private const KATEGORI_INSIDEN_OPTIONS = [
        'Medication / Cairan IV' => 'Medication / Cairan IV',
        'Prosedur Klinis' => 'Prosedur Klinis',
        'Diagnostik' => 'Diagnostik',
        'Infeksi Terkait Pelayanan Kesehatan' => 'Infeksi Terkait Pelayanan Kesehatan',
        'Pasien Jatuh' => 'Pasien Jatuh',
        'Identifikasi Pasien' => 'Identifikasi Pasien',
        'Komunikasi' => 'Komunikasi',
        'Dokumentasi Klinis' => 'Dokumentasi Klinis',
        'Peralatan Medis' => 'Peralatan Medis',
        'Transfusi Darah / Produk Darah' => 'Transfusi Darah / Produk Darah',
        'Administrasi / Proses Pelayanan' => 'Administrasi / Proses Pelayanan',
        'Lingkungan / Fasilitas' => 'Lingkungan / Fasilitas',
        'Faktor Manusia' => 'Faktor Manusia',
        'Lainnya' => 'Lainnya',
    ];

    private const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'dilaporkan' => 'Dilaporkan',
        'revisi' => 'Revisi',
        'diverifikasi' => 'Diverifikasi',
        'revisi_unit' => 'Revisi Unit',
        'investigasi' => 'Investigasi',
    ];

    private static function makeSelect(string $name, string $label, array $options): Select
    {
        return Select::make($name)
            ->label($label)
            ->options($options)
            ->native(false)
            ->searchable();
    }

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
            static::sectionTindakan($withAdminFields),
        ];

        if ($withAdminFields) {
            $sections[] = static::sectionCatatanTambahan();
            $sections[] = static::sectionStatus();
        }

        return $sections;
    }

    // Section A: Data Pelapor
    public static function sectionPelapor(): Section
    {
        return Section::make('📋 BAGIAN A: DATA PELAPOR')
            ->description('Identitas dan informasi kontak pelapor insiden')
            ->icon('heroicon-o-user-circle')
            ->schema([
                Grid::make(2)->schema([
                    Hidden::make('nama_pelapor')
                        ->dehydrateStateUsing(function ($state, callable $get) {
                            $user = \App\Models\User::find($get('user_id'));
                            return $user?->name;
                        }),

                    Hidden::make('unit_kerja')
                        ->dehydrateStateUsing(function ($state, callable $get) {
                            $unit = \App\Models\UnitKerja::find($get('unit_kerja_id'));
                            return $unit?->unit_name;
                        }),

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

    // Section B: Data Pasien (jika terkait)
    public static function sectionPasien(): Section
    {
        return Section::make('👤 BAGIAN B: DATA PASIEN (Jika Terkait)')
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

    // Section C: Rincian Kejadian Insiden
    public static function sectionInsiden($withGrading = false): Section
    {
        $schema = [
            Grid::make(2)->schema([
                static::makeSelect('jenis_insiden', 'Jenis Insiden', self::JENIS_INSIDEN_OPTIONS)
                    ->required()
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

            Grid::make(2)->schema([
                static::makeSelect('kategori_insiden', 'Kategori Insiden', self::KATEGORI_INSIDEN_OPTIONS)
                    ->required()
                    ->searchable(),

                static::makeSelect('dampak_insiden', 'Dampak Insiden', self::DAMPAK_INSIDEN_OPTIONS)
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
        ];

        if ($withGrading) {
            $schema[] = static::sectionGradingResiko();
        }

        return Section::make('📍 BAGIAN C: RINCIAN KEJADIAN INSIDEN')
            ->description('Informasi lengkap tentang waktu dan tempat terjadinya insiden')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema($schema)
            ->collapsible()
            ->persistCollapsed()
            ->compact();
    }

    // Bagian D: Kronologi Kejadian (Tabular Timeline)
    public static function sectionKronologi(): Section
    {
        return static::getFieldTabularTimeline();
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

    // Bagian F: Tindakan yang Dilakukan & Analisis Penyebab (jika ada)
    public static function sectionTindakan(bool $withAnalysis = false): Section
    {
        $fields = [
            Forms\Components\Textarea::make('tindakan_dilakukan')
                ->label('Tindakan yang Telah Dilakukan Setelah Insiden')
                ->required()
                ->rows(16)
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

    // Catatan Tambahan (Opsional)
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
                static::makeSelect('status', 'Status Laporan', self::STATUS_OPTIONS)
                    ->default('draft')
                    ->required(),
            ])
            ->visibleOn('edit');
    }

    public static function getFieldDataCollection(): Section
    {
        return Section::make('📊 Data Investigasi')
            ->description('Kumpulan data pengumpulan investigasi sederhana')
            ->icon('heroicon-o-chart-bar')
            ->schema([

                Tabs::make('InvestigationTabs')
                    ->tabs(function ($record) {

                        if (!$record) {
                            return [
                                Tab::make('setup')
                                    ->label('⚙️ Setup')
                                    ->schema([
                                        TextEntry::make('info')
                                            ->content('Simpan laporan terlebih dahulu untuk menambahkan investigasi.')
                                    ])
                            ];
                        }

                        return [

                            /*
                            |--------------------------------------------------------------------------
                            | TAB INTERVIEW
                            |--------------------------------------------------------------------------
                            */

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
                                        })

                                ]),

                            /*
                            |--------------------------------------------------------------------------
                            | TAB REVIEW DOKUMEN
                            |--------------------------------------------------------------------------
                            */

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
                                        })

                                ]),

                            /*
                            |--------------------------------------------------------------------------
                            | TAB OBSERVASI
                            |--------------------------------------------------------------------------
                            */

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
                                                    'application/pdf'
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
                                        })

                                ]),

                        ];
                    })

                    ->persistTabInQueryString()

            ])
            ->collapsible();
    }

    public static function getFieldTabularTimeline(): Section
    {
        return Section::make('📊 Tabular Timeline')
            ->description('Timeline kejadian dalam format kronologi investigasi')
            ->schema([
                Repeater::make('timelineEvents')
                    ->relationship('timelineEvents')
                    ->label('Timeline Kejadian')
                    ->schema([
                        DateTimePicker::make('event_datetime')
                            ->label('Tanggal & Waktu Kejadian')
                            ->required()
                            ->seconds(false),

                        Repeater::make('entries')
                            ->relationship('entries')
                            ->label('Entri Kategori')
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name', fn($query) => $query->orderBy('id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->addActionLabel('Tambah Entri')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $category_id = $state['category_id'] ?? null;
                                $description = $state['description'] ?? null;

                                $category = TimelineCategory::find($category_id)?->name ?? null;
                                if ($category && $description) {
                                    return "{$category}: " . Str::limit($description, 50);
                                }

                                return null;
                            })
                    ])
                    ->addActionLabel('Tambah Timeline Kejadian')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        $datetime = $state['event_datetime'] ?? null;

                        if ($datetime) {
                            return "Kejadian pada " . date('d F Y, H:i', strtotime($datetime));
                        }

                        return null;
                    })
            ])
            ->collapsible();
    }

    public static function getFieldProblemAnalysis(): Section
    {
        return Section::make('🧠 Analisa Masalah (5 WHY)')
            ->description('Analisis akar masalah berdasarkan metode 5 WHY')
            ->schema([

                Repeater::make('problems')
                    ->relationship('problems')
                    ->label('Masalah (CMP / SDP)')
                    ->schema([

                        Select::make('problem_type')
                            ->label('Jenis Masalah')
                            ->options([
                                'CMP' => 'CMP (Clinical Management Problem)',
                                'SDP' => 'SDP (Service Delivery Problem)',
                            ])
                            ->required(),

                        Textarea::make('problem_description')
                            ->label('Deskripsi Masalah')
                            ->rows(3)
                            ->required(),

                        /*
                        |--------------------------------------------------------------------------
                        | 5 WHY ANALYSIS
                        |--------------------------------------------------------------------------
                        */

                        Repeater::make('whys')
                            ->relationship('whys')
                            ->label('Analisa 5 WHY')
                            ->schema([

                                TextInput::make('why_level')
                                    ->label('WHY ke')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5)
                                    ->default(1)
                                    ->required()
                                    ->extraInputAttributes(['min' => 1, 'max' => 5])
                                    ->prefix('🔹 WHY ')
                                    ->disabled(fn(callable $get) => filled($get('../../id')))
                                    ->helperText('Otomatis terisi berdasarkan urutan (1-5)'),

                                Textarea::make('problem_statement')
                                    ->label('Masalah')
                                    ->rows(2)
                                    ->required(),

                                Textarea::make('immediate_cause')
                                    ->label('Penyebab Langsung')
                                    ->rows(2),

                                Textarea::make('root_cause')
                                    ->label('Akar Masalah')
                                    ->rows(2),

                            ])
                            ->addActionLabel('➕ Tambah WHY (Max 5)')
                            ->reorderable()
                            ->collapsible()
                            ->maxItems(5)
                            ->minItems(1)
                            ->itemLabel(function (array $state): ?string {

                                $level = $state['why_level'] ?? 1;
                                $problem = $state['problem_statement'] ?? null;

                                if ($problem) {
                                    return "WHY {$level}: " . Str::limit($problem, 40);
                                }

                                return "WHY {$level}";
                            }),

                        /*
                        |--------------------------------------------------------------------------
                        | FAKTOR KONTRIBUTOR
                        |--------------------------------------------------------------------------
                        */

                        Repeater::make('contributors')
                            ->relationship('contributors')
                            ->label('Faktor Kontributor')
                            ->schema([

                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->searchable()
                                    ->preload()
                                    ->relationship('category', 'name', fn($query) => $query->orderBy('name'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(callable $set) => $set('component_id', null)),

                                Hidden::make('category')
                                    ->dehydrateStateUsing(function ($state, callable $get) {
                                        $categoryId = $get('category_id');
                                        return $categoryId ? ProblemContributorCategory::find($categoryId)?->code : null;
                                    }),

                                Select::make('component_id')
                                    ->label('Komponen')
                                    ->searchable()
                                    ->preload()
                                    ->options(function (Get $get) {
                                        $categoryId = $get('category_id');

                                        if (!$categoryId) {
                                            return [];
                                        }

                                        return ProblemContributorComponent::where('category_id', $categoryId)
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(callable $set) => $set('sub_component_id', null)),

                                Hidden::make('component')
                                    ->dehydrateStateUsing(function ($state, callable $get) {
                                        $componentId = $get('component_id');
                                        return $componentId ? ProblemContributorComponent::find($componentId)?->name : null;
                                    }),

                                Select::make('sub_component_id')
                                    ->label('Sub Komponen')
                                    ->searchable()
                                    ->preload()
                                    ->options(function (Get $get) {
                                        $componentId = $get('component_id');

                                        if (!$componentId) {
                                            return [];
                                        }

                                        return ProblemContributorSubComponent::where('component_id', $componentId)
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $subComponentId = $get('sub_component_id');

                                        if (!$subComponentId) {
                                            $set('description', null);
                                            return;
                                        }

                                        // Auto-populate description dengan semua deskripsi dari database
                                        $descriptions = ProblemContributorDescription::where('sub_component_id', $subComponentId)
                                            ->orderBy('id')
                                            ->pluck('description')
                                            ->toArray();

                                        if (!empty($descriptions)) {
                                            $autoFilled = implode("\n", array_map(fn($desc) => "• {$desc}", $descriptions));
                                            $set('description', $autoFilled);
                                        } else {
                                            $set('description', null);
                                        }
                                    }),

                                Hidden::make('sub_component')
                                    ->dehydrateStateUsing(function ($state, callable $get) {
                                        $subComponentId = $get('sub_component_id');
                                        return $subComponentId ? ProblemContributorSubComponent::find($subComponentId)?->name : null;
                                    }),

                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(10)
                                    ->hint(function (Get $get) {
                                        $subComponentId = $get('sub_component_id');

                                        if (!$subComponentId) {
                                            return null;
                                        }

                                        $count = ProblemContributorDescription::where('sub_component_id', $subComponentId)->count();
                                        return $count > 0 ? "💡 {$count} deskripsi tersedia (auto-filled)" : null;
                                    }),
                            ])
                            ->addActionLabel('Tambah Faktor')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {

                                $categoryId = $state['category_id'] ?? null;
                                $componentId = $state['component_id'] ?? null;
                                $subId = $state['sub_component_id'] ?? null;

                                $category = $categoryId ? ProblemContributorCategory::find($categoryId)?->name : null;
                                $component = $componentId ? ProblemContributorComponent::find($componentId)?->name : null;
                                $sub = $subId ? ProblemContributorSubComponent::find($subId)?->name : null;

                                if ($category && $component && $sub) {
                                    return "{$category} > {$component} > {$sub}";
                                }

                                if ($category && $component) {
                                    return "{$category} > {$component}";
                                }

                                return $category;
                            }),

                        /*
                        |--------------------------------------------------------------------------
                        | REKOMENDASI
                        |--------------------------------------------------------------------------
                        */

                        Repeater::make('recommendations')
                            ->relationship('recommendations')
                            ->label('Rekomendasi')
                            ->schema([

                                Textarea::make('recommendation_text')
                                    ->label('Rekomendasi')
                                    ->rows(2)
                                    ->required(),

                                Select::make('priority')
                                    ->label('Prioritas')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                    ]),
                            ])
                            ->addActionLabel('Tambah Rekomendasi')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {

                                $text = $state['recommendation_text'] ?? null;

                                if ($text) {
                                    return Str::limit($text, 50);
                                }

                                return null;
                            }),

                        /*
                        |--------------------------------------------------------------------------
                        | TINDAKAN
                        |--------------------------------------------------------------------------
                        */

                        Repeater::make('actions')
                            ->relationship('actions')
                            ->label('Tindakan')
                            ->schema([

                                Textarea::make('action_text')
                                    ->label('Tindakan')
                                    ->rows(2)
                                    ->required(),

                                TextInput::make('responsible_person')
                                    ->label('Penanggung Jawab'),

                                DatePicker::make('deadline')
                                    ->label('Deadline'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'ongoing' => 'Ongoing',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('pending'),
                            ])
                            ->addActionLabel('Tambah Tindakan')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {

                                $action = $state['action_text'] ?? null;

                                if ($action) {
                                    return Str::limit($action, 50);
                                }

                                return null;
                            }),

                    ])
                    ->addActionLabel('Tambah Masalah')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {

                        $type = $state['problem_type'] ?? null;
                        $desc = $state['problem_description'] ?? null;

                        if ($type && $desc) {
                            return "{$type}: " . Str::limit($desc, 50);
                        }

                        return $type;
                    }),
            ])
            ->collapsible();
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
}
