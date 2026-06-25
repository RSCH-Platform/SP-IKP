# Changelog

Semua perubahan yang signifikan pada proyek **SP-IKP** (Sistem Pelaporan Insiden Keselamatan Pasien) akan didokumentasikan di file ini.

Format changelog ini berdasarkan [Keep a Changelog](https://keepachangelog.com/id/1.0.0/), dan proyek ini mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
