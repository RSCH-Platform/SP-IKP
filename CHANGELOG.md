# Changelog

Semua perubahan yang signifikan pada proyek **SP-IKP** (Sistem Pelaporan Insiden Keselamatan Pasien) akan didokumentasikan di file ini.

Format changelog ini berdasarkan [Keep a Changelog](https://keepachangelog.com/id/1.0.0/), dan proyek ini mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2026-10-06

### Fixed / Performance
- **Pencegahan Error Maximum Execution Time pada Edit Laporan**:
  - Mengubah rendering tab pratinjau (`preview-laporan-insiden-content` & `preview-investigasi-laporan-insiden-content`) di `edit-laporan-insiden.blade.php` menjadi *on-demand / conditional* berbasis Livewire state (`$activeTab`), mencegah server me-render ratusan query N+1 (problem, 5 Whys, action, media URL) saat form edit sedang aktif.
  - Menambahkan konfigurasi *fail-fast* HTTP timeout pada driver S3 dan MinIO di `config/filesystems.php` (`connect_timeout = 2s`, `timeout = 5s`) untuk mencegah request menggantung hingga batas PHP execution time saat storage remote mengalami kendala.
  - Mengisolasi penyimpanan form utama pada `EditLaporanInsiden`: menyimpan langsung field tabel `laporan_insidens` dan kalkulasi risk assessment tanpa memicu rekonsiliasi relationship bawaan Filament yang berat.
  - Mengimplementasikan **Dirty Tracking** dan **Granular Model Save** pada `EditLaporanInsiden`: mendeteksi secara akurat field yang benar-benar berubah (`isAttributeChanged`), melewati query `UPDATE` jika tidak ada perubahan, serta mencatat log perubahan per-field.
  - Menambahkan **Milestone Stopwatch & Diagnostics Logging** (`[IKP Save][M1-M5]`): mengukur durasi setiap fase simpan (ekstraksi state form, deteksi field kotor, eksekusi SQL update, kalkulasi grading risiko, dan pengiriman notifikasi) beserta konsumsi memori untuk kemudahan deteksi bottleneck.
  - Mengganti dump stack trace vendor yang panjang pada penanganan exception simpan dengan log diagnostik ringkas dan terfokus (`logSaveException`): menyajikan penjelasan manusiawi (koneksi timeout, database null constraint, dsb.), lokasi baris kode aplikasi (`app/`), dan notifikasi UI yang informatif tanpa membanjiri file log.
  - **Memperbaiki Infinite Recursion pada Authorization Policy & Scoping Resource**: Mengganti pemanggilan rekursif `$authUser->can(...)` di dalam `LaporanInsidenPolicy` dan `LaporanInsidenResource` menjadi pengecekan langsung `checkPermissionTo(...)`, menuntaskan *bug* "Maximum call stack size reached. Infinite recursion" yang menyebabkan PHP hang hingga batas timeout 120 detik saat evaluasi izin `ForceEdit` dan route binding.
  - Mengeliminasi duplikasi *mount* komponen `TimelineGridManager` di dalam form wizard saat tahap investigasi aktif.

## [1.1.1] - 2026-07-03

### Added / Features
- **Sistem Pembaruan Aplikasi (Updater)**: Penambahan arsitektur sistem *updater* terpusat (folder `app/Updates/`) beserta perintah artisan `AppUpdateCommand` untuk menstandardisasi pengelolaan dan eksekusi rilis fitur baru.
- **Dokumentasi Sistem**: Menambahkan dokumen khusus `analisis_css_dan_performa_ikp.md` yang merangkum panduan arsitektur CSS dan rekam jejak analisis performa.

### Fixed / Patched
- **Bug Filter Analitik Unit Kerja**: Memperbaiki isu pada *widget* `ManagerUnitKerjaAnalytics` di mana data tidak muncul (bernilai `null`) karena kegagalan *strict type checking* pada variabel periode dari *Livewire*.
- **Kompatibilitas Lintas Database**: Memperbaiki *bug SQL* pada fungsi filter bulan yang menggunakan sintaks baku `whereRaw('MONTH...')` menjadi implementasi bawaan Eloquent (`whereMonth`), sehingga sistem aman digunakan pada *database* apa pun (MySQL, PostgreSQL, maupun SQLite).
- **Fatal Error pada Widget**: Menghapus sisa *debug code* `dd()` pada tabel unit performa yang sempat menyebabkan berhentinya proses *render* komponen di *dashboard*.

### Changed / Improved
- **Optimalisasi UI/UX Tabel Risiko**: Merombak tampilan tabel `priority-risk.blade.php` dengan menyembunyikan kolom yang kurang relevan, menyeragamkan jenis *font* dengan `tabular-nums`, membenahi *badge* warna grading, serta menambahkan baris kalkulasi "Total" dan "Jumlah Insiden" di bagian terbawah.
- **Desain UI Komponen (Filament)**: Memperkecil jarak *padding* tombol (*button*) bawaan Filament secara global via *CSS overrides* (`theme.css`) untuk tampilan layar yang lebih lega, serta melakukan penyesuaian pada CSS komponen *wizard*.
- **Refaktorisasi Sistem Pelaporan**: Melakukan pembersihan (*refactoring*) pada *Controllers* `InvestigasiLaporanInsidenViewController` & `LaporanInsidenViewController`.
- **Pembaruan Skrip Migrasi**: Penyesuaian perintah migrasi otomatis untuk rekaman data lawas (`MigrateLegacyLaporanInsidenData`).
- **Dependensi Composer**: Pembaruan rutin untuk paket-paket dependen di `composer.json`.

## [1.1.0] - 2026-07-01

### Added / Features
- **Database Schema Normalization**: Pemisahan data investigasi ke dalam tabel baru `investigations` untuk menormalkan struktur database (menghapus kolom terkait investigasi dari `laporan_insidens`).
- **State Transitions**: Penambahan tabel `laporan_insiden_transitions` untuk melacak status dan alur kerja (workflow steps) laporan insiden dengan lebih handal.
- **Data Migration Command**: Penambahan command artisan `app:migrate-legacy-laporan-insiden-data` untuk migrasi data laporan lama secara aman ke skema database baru yang sudah dipisahkan.
- **Peningkatan Arsitektur**: Implementasi folder/layer `Actions` dan `Jobs` untuk pemisahan logika yang lebih bersih serta mendukung pemrosesan *background/asinkron*.
- **Testing Suite Baru**: Pembuatan struktur test untuk Filament, Actions, Models, beserta `SuperAdminAccessTest` guna menunjang arsitektur Pest.
- **Development Tooling**: Penambahan perintah `wipe-db` dan `safe-migrate` pada bagian scripts `composer.json` untuk mempermudah operasional database reset selama proses development.

### Changed
- **Pembaruan Filament Resources & Form**:
  - Pembaruan pada `LaporanInsidenResource` termasuk memisahkan komponen form (seperti `DataCollectionSection` dan `PelaporSection`) agar modular dan lebih mudah dipelihara.
  - Penyempurnaan berbagai widget panel termasuk `InvestigationStatsWidget`, `DraftReportsWidget`, dan `ManagerUnitKerjaAnalytics`.
- **Konsolidasi Dokumentasi**: Menggabungkan file dokumentasi usang (seperti penghapusan `CHANGE_LOG.md` dan `TIMELINE_EXPORT_README.md`) untuk disatukan dalam standar `CHANGELOG.md` ini.

### Performance (Optimized)
- **Optimasi N+1 Queries**: Menerapkan *eager loading* untuk relasi berlapis di `LaporanInsidenResource` dan refaktorisasi `HasWorkflowSteps` dengan menghapus iterasi `User::find`, sehingga mengatasi *bottleneck* performa saat halaman preview dimuat.
- **Optimasi Index (Sargable Query)**: Perbaikan query pembuatan nomor laporan pada `generateNomorLaporan` yang kini menggunakan `whereBetween` untuk mengaktifkan index pada database dan mencegah operasi *full table scan*.
- **Pencegahan Bottleneck Transaksi**: Menonaktifkan sementara pengiriman notifikasi ke semua pengguna (global user notifications) di dalam siklus edit (`EditLaporanInsiden`) untuk mencegah proses lambat yang memblokir (*blocking*) operasional simpan.

## [1.0.0]

### Added / Features
- Tambahan fungsionalitas export untuk catatan timeline (kronologi).
- Tambahan tabel pada widget tren laporan insiden di dashboard.
- Konfigurasi sinkronisasi user dengan IAM (mendukung sinkronisasi/penghapusan user yang sudah tidak ada di IAM).
- Integrasi *AWS S3 / MinIO* menggunakan paket `league/flysystem-aws-s3-v3`.
- Penambahan pengaturan `MEDIA_DISK` di `.env.example` untuk penyimpanan media publik.
- Dukungan *command* pengecekan koneksi MinIO dengan validasi *network request* yang lebih baik.
- Peningkatan keamanan data dengan *read-only mode* penuh untuk laporan yang sudah selesai (*Completed Reports*).
- Pembaruan tampilan `Investigated Reports Table` (mengelompokkan *problem statements* dan memperbarui layout tabel).
- Pembaruan widget `DraftReportsInvestigatedWidget` (penambahan deskripsi dan peningkatan *styling* tabel).
- Pembaruan pada *summary stats view* dan cara pengambilan jumlah insiden di dashboard untuk performa yang lebih baik.
- Pembuatan file LICENSE dan pendokumentasian lisensi pihak ketiga.

### Fixed
- Perbaikan penamaan paket, tipe lisensi, dan pembaruan versi `auth-bridge-client` di dalam `composer.json`.
- Perbaikan sistem penanganan konten di `support.js` untuk mengoptimalkan kloning elemen dan *rendering*.

### Refactored / Optimized
- Refaktorisasi logika pengunggahan dokumen menjadi metode *reusable* untuk mempermudah *maintenance*.
- Optimasi *layer checking* pada perintah pengujian koneksi MinIO.
- Pembaruan konfigurasi *filesystem disk* yang dapat menyesuaikan diri secara otomatis berdasarkan *environment*.
