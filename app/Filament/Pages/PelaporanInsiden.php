<?php

namespace App\Filament\Pages;

use App\Models\LaporanInsiden;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PelaporanInsiden extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.pelaporan-insiden';

    protected static ?string $navigationLabel = 'Pelaporan Insiden';

    protected static ?string $title = 'Form Pelaporan Insiden Keselamatan Pasien';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'nama_pelapor' => Auth::user()->name,
            'tanggal_lapor' => now()->format('Y-m-d'),
            'tanggal_insiden' => now()->format('Y-m-d'),
            'status' => 'draft',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('📋 BAGIAN A: DATA PELAPOR')
                    ->description('Identitas dan informasi kontak pelapor insiden')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nama_pelapor')
                                    ->label('Nama Lengkap Pelapor')
                                    ->required()
                                    ->default(Auth::user()->name)
                                    ->prefixIcon('heroicon-m-user')
                                    ->placeholder('Masukkan nama lengkap'),

                                Forms\Components\TextInput::make('unit_kerja')
                                    ->label('Unit Kerja / Departemen')
                                    ->required()
                                    ->prefixIcon('heroicon-m-building-office')
                                    ->placeholder('Contoh: IGD, Rawat Inap, dll'),
                            ]),

                        Grid::make(2)
                            ->schema([
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
                    ->compact(),

                Section::make('📍 BAGIAN B: RINCIAN KEJADIAN INSIDEN')
                    ->description('Informasi lengkap tentang waktu dan tempat terjadinya insiden')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('jenis_insiden')
                                    ->label('Jenis Insiden')
                                    ->required()
                                    ->options([
                                        'KNC (Kejadian Nyaris Cedera)' => 'KNC (Kejadian Nyaris Cedera)',
                                        'KTD (Kejadian Tidak Diharapkan)' => 'KTD (Kejadian Tidak Diharapkan)',
                                        'KTC (Kejadian Tidak Cedera)' => 'KTC (Kejadian Tidak Cedera)',
                                        'Sentinel' => 'Sentinel',
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

                        Grid::make(2)
                            ->schema([
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
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-clock')
                                    ->seconds(false)
                                    ->helperText('Jam terjadinya insiden (format 24 jam)'),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->compact(),

                Section::make('👤 BAGIAN C: DATA PASIEN (Jika Terkait)')
                    ->description('Lengkapi informasi pasien jika insiden melibatkan pasien')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Placeholder::make('isian_pasien')
                            ->content('Isi bagian ini hanya jika insiden melibatkan pasien. Kosongkan jika tidak ada.')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
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
                                                '0-1 bulan' => '0-1 bulan',
                                                '>1 bulan - 1 tahun' => '>1 bulan - 1 tahun',
                                                '>1 tahun - 5 tahun' => '>1 tahun - 5 tahun',
                                                '>5 tahun - 15 tahun' => '>5 tahun - 15 tahun',
                                                '>15 tahun - 30 tahun' => '>15 tahun - 30 tahun',
                                                '>30 tahun - 65 tahun' => '>30 tahun - 65 tahun',
                                                '>65 tahun' => '>65 tahun',
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
                                                'Pribadi' => 'Pribadi',
                                                'BPJS' => 'BPJS',
                                                'Asuransi Swasta' => 'Asuransi Swasta',
                                                'Lainnya' => 'Lainnya',
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
                    ->collapsed()
                    ->persistCollapsed()
                    ->compact(),

                Section::make('📝 BAGIAN D: KRONOLOGI KEJADIAN')
                    ->description('Uraikan secara detail dan kronologis bagaimana insiden terjadi')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('kronologi')
                            ->label('Kronologi Lengkap Insiden')
                            ->required()
                            ->rows(8)
                            ->helperText('Jelaskan secara detail, runtut, dan kronologis bagaimana insiden terjadi dari awal hingga akhir. Tuliskan selengkap mungkin tanpa batasan karakter.')
                            ->placeholder('Contoh: Pada pukul 10.00 WIB, pasien sedang berada di ruang rawat inap ketika...')
                            ->columnSpanFull(),

                        Forms\Components\Radio::make('insiden_terjadi_pada')
                            ->label('Insiden Terjadi Pada')
                            ->required()
                            ->options([
                                'Pasien' => 'Pasien',
                                'Petugas' => 'Petugas / Staf',
                                'Pengunjung' => 'Pengunjung / Keluarga',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->default('Pasien')
                            ->inline()
                            ->inlineLabel(false)
                            ->descriptions([
                                'Pasien' => 'Insiden menimpa pasien yang sedang dirawat',
                                'Petugas' => 'Insiden menimpa petugas/staf rumah sakit',
                                'Pengunjung' => 'Insiden menimpa pengunjung atau keluarga pasien',
                                'Lainnya' => 'Selain ketiga kategori di atas',
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
                    ->compact(),

                Section::make('⚠️ BAGIAN E: KATEGORI DAN DAMPAK INSIDEN')
                    ->description('Klasifikasi jenis dan tingkat dampak insiden yang terjadi')
                    ->icon('heroicon-o-shield-exclamation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Forms\Components\Select::make('kategori_insiden')
                                //     ->label('Kategori Insiden')
                                //     ->required()
                                //     ->options([
                                //         'Medikasi' => '💊 Medikasi / Obat',
                                //         'Prosedur Klinik' => '🏥 Prosedur Klinik',
                                //         'Dokumentasi' => '📄 Dokumentasi',
                                //         'Infeksi Nosokomial' => '🦠 Infeksi Nosokomial',
                                //         'Jatuh' => '🤕 Jatuh',
                                //         'Komunikasi' => '💬 Komunikasi',
                                //         'Peralatan Medis' => '🔧 Peralatan Medis',
                                //         'Transfusi Darah' => '🩸 Transfusi Darah',
                                //         'Diet/Nutrisi' => '🍽️ Diet / Nutrisi',
                                //         'Lainnya' => '📌 Lainnya',
                                //     ])
                                //     ->native(false)
                                //     ->searchable()
                                //     ->prefixIcon('heroicon-m-tag')
                                //     ->helperText('Pilih kategori yang paling sesuai'),
                                Forms\Components\TextInput::make('kategori_insiden')
                                    ->label('Kategori Insiden')
                                    ->required()
                                    ->prefixIcon('heroicon-m-tag')
                                    ->helperText('Pilih kategori yang paling sesuai'),

                                Forms\Components\Select::make('dampak_insiden')
                                    ->label('Dampak Insiden')
                                    ->required()
                                    ->options([
                                        'Tidak ada cedera' => '✅ Tidak ada cedera',
                                        'Cedera ringan' => '🟡 Cedera ringan',
                                        'Cedera sedang' => '🟠 Cedera sedang',
                                        'Cedera berat' => '🔴 Cedera berat',
                                        'Meninggal' => '⚫ Meninggal',
                                    ])
                                    ->native(false)
                                    ->default('Tidak ada cedera')
                                    ->prefixIcon('heroicon-m-heart')
                                    ->helperText('Tingkat dampak yang dialami'),
                            ]),

                        Placeholder::make('info_grading')
                            ->content('ℹ️Catatan: Grading risiko akan diisi oleh Tim IKP/Validator setelah laporan disubmit.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->compact(),

                Section::make('🩹 BAGIAN F: TINDAKAN YANG DILAKUKAN')
                    ->description('Tindakan yang telah dilakukan setelah terjadinya insiden')
                    ->icon('heroicon-o-hand-raised')
                    ->schema([
                        Forms\Components\Textarea::make('tindakan_dilakukan')
                            ->label('Tindakan yang Telah Dilakukan Setelah Insiden')
                            ->required()
                            ->rows(6)
                            ->helperText('Jelaskan seluruh tindakan yang telah dilakukan setelah insiden terjadi (pertolongan pertama, notifikasi dokter, koordinasi tim, dll). Tuliskan selengkap mungkin tanpa batasan karakter.')
                            ->placeholder('Contoh:\n1. Segera memberikan pertolongan pertama kepada pasien\n2. Menghubungi dokter jaga untuk penanganan lanjutan\n3. Melaporkan kejadian kepada kepala ruangan\n4. Mendokumentasikan kondisi pasien\n5. ...')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('info_analisis')
                            ->content('ℹ️ Catatan: Analisis penyebab, rekomendasi, dan rencana tindakan pencegahan akan diisi oleh Tim IKP setelah investigasi mendalam.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->compact(),

                // Section::make('📎 CATATAN DAN LAMPIRAN')
                //     ->description('Informasi tambahan dan dokumen pendukung (jika ada)')
                //     ->icon('heroicon-o-paper-clip')
                //     ->schema([
                //         Forms\Components\Textarea::make('catatan_tambahan')
                //             ->label('Catatan Tambahan')
                //             ->rows(5)
                //             ->placeholder('Tuliskan informasi tambahan lainnya yang diperlukan untuk melengkapi laporan ini. Tidak ada batasan karakter.')
                //             ->helperText('(Opsional) Informasi tambahan yang belum tercakup di bagian sebelumnya')
                //             ->columnSpanFull(),

                //         Placeholder::make('info_lampiran')
                //             ->content('💡 **Tips:** Jika ada dokumen pendukung (foto, rekaman CCTV, dll), silakan lampirkan setelah laporan ini disubmit atau hubungi tim IKP.')
                //             ->columnSpanFull(),
                //     ])
                //     ->collapsible()
                //     ->collapsed()
                //     ->persistCollapsed()
                //     ->compact(),

                Forms\Components\Hidden::make('status')
                    ->default('draft'),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
            ])
            ->statePath('data');
    }

    public function simpanDraft(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = Auth::id();
        $data['status'] = 'draft';

        LaporanInsiden::create($data);

        Notification::make()
            ->title('Draft berhasil disimpan')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = Auth::id();
        $data['status'] = 'submitted';

        LaporanInsiden::create($data);

        Notification::make()
            ->title('Laporan berhasil disubmit')
            ->body('Laporan insiden Anda telah berhasil dikirim untuk direview.')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('simpanDraft')
                ->label('Simpan Draft')
                ->color('gray')
                ->action('simpanDraft'),

            \Filament\Actions\Action::make('submit')
                ->label('Submit Laporan')
                ->color('primary')
                ->action('submit'),
        ];
    }
}
