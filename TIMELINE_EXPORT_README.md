# Timeline Export to Excel

## Overview
Fitur export timeline insiden ke file Excel telah ditambahkan ke Filament Resource. Fitur ini memungkinkan pengguna untuk mengunduh data timeline dalam format Excel dengan format grid yang sama seperti yang ditampilkan di UI.

## Fitur
- **Export Format**: Grid dengan Waktu di kolom pertama dan Kategori di kolom-kolom berikutnya
- **Grouped by Date**: Data dikelompokkan berdasarkan tanggal kejadian
- **Styled Header**: Header dengan warna latar belakang gelap untuk kejelasan
- **Unique Categories**: Hanya menampilkan kategori yang benar-benar memiliki data
- **Automatic Sorting**: Data diurutkan berdasarkan waktu kejadian

## Implementasi

### 1. Export Class: `App\Exports\TimelineGridExport`
File: `/app/Exports/TimelineGridExport.php`

Class ini menangani pembuatan file Excel dengan menggunakan OpenSpout (built-in dengan Filament).

```php
$export = new TimelineGridExport($laporanInsiden);
return $export->download();
```

**Fitur:**
- Membuat file Excel dengan sheet "Timeline"
- Menambahkan title row dengan informasi laporan
- Mengelompokkan events berdasarkan tanggal
- Menambahkan header untuk setiap tanggal
- Menampilkan waktu, kategori, dan deskripsi dalam format grid
- Set column width untuk readability

### 2. Action di ViewLaporanInsiden
File: `/app/Filament/Resources/LaporanInsidens/Pages/ViewLaporanInsiden.php`

Menambahkan action button "Export Timeline ke Excel" di header page.

**Visibility:**
- Action hanya tampil jika laporan memiliki timeline events
- Button berwarna info (biru)
- Icon: arrow-down-tray

### 3. Dependencies
- **OpenSpout**: Sudah included dengan Filament 4.0
- **File System**: Menggunakan `storage/temp/` untuk menyimpan file sementara

## Struktur File Excel

### Format Output:
```
TIMELINE INSIDEN - Laporan #123 (1 June 2026)

01 June 2026
| Waktu  | Kategori A (KAT-A) | Kategori B (KAT-B) |
|--------|-------------------|-------------------|
| 08:00  | Deskripsi A       | Deskripsi B       |
| 09:30  | Deskripsi A2      | [Kosong]          |

02 June 2026
| Waktu  | Kategori A (KAT-A) | Kategori B (KAT-B) |
|--------|-------------------|-------------------|
| 10:00  | Deskripsi A3      | Deskripsi B2      |
```

### Header Styling:
- Title Row: Background biru gelap (79, 129, 189), text putih bold
- Column Header: Background biru tua (31, 78, 121), text putih bold, centered
- Data Cells: Border on all sides
- Column Width: 25 untuk Waktu, 35 untuk kategori

## Cara Penggunaan

1. **Buka Laporan**: Navigasi ke halaman View Laporan Insiden
2. **Klik Button Export**: Cari tombol "Export Timeline ke Excel" di bagian atas halaman
3. **Download File**: File akan otomatis diunduh dengan nama format: `timeline-laporan-{id}-{timestamp}.xlsx`

## File Path
- Export Class: `app/Exports/TimelineGridExport.php`
- Modified File: `app/Filament/Resources/LaporanInsidens/Pages/ViewLaporanInsiden.php`

## Temp Directory
File Excel sementara disimpan di: `storage/temp/`
File akan otomatis dihapus setelah download selesai.

## Notes
- Hanya kategori yang memiliki entry yang akan ditampilkan
- Jika tidak ada timeline events, action export tidak akan tampil
- Format waktu: HH:MM (24-hour format)
- Format tanggal: Terjemahan ke Bahasa Indonesia (e.g., "01 Juni 2026")
