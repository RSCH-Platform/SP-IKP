# Changelog

Semua perubahan yang signifikan pada proyek **SP-IKP** (Sistem Pelaporan Insiden Keselamatan Pasien) akan didokumentasikan di file ini.

Format changelog ini berdasarkan [Keep a Changelog](https://keepachangelog.com/id/1.0.0/), dan proyek ini mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
