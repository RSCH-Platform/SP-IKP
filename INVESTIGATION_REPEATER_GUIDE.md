# Investigation Data Repeater - Complete Guide

## Overview
Repeater untuk mengumpulkan data investigasi dalam laporan insiden. Sistem ini mendukung tiga kategori investigasi (Interview, Review Dokumen, Observasi) dengan interface yang user-friendly.

## Fitur Utama

### 1. Multi-Tab Interface
Setiap kategori investigasi memiliki tab terpisah:
- **🎤 Interview** - Wawancara dengan narasumber
- **📄 Review Dokumen** - Telaah dokumen pendukung
- **👁️ Observasi** - Pengamatan langsung

Setiap tab menampilkan:
- **Badge Count**: Jumlah data yang sudah dikumpulkan
- **Collapsible Section**: Menyembunyikan data jika kosong
- **Repeater Fields**: Form untuk menambah/edit investigasi

### 2. Field-Field Repeater

#### Sumber / Narasumber (Text Input)
- **Label**: Sumber / Narasumber
- **Placeholder**: Nama orang atau dokumen yang dijadikan sumber
- **Contoh**: "Perawat Rina", "Dokumen Rekam Medis No. RM-12345"
- **Icon**: 👤 User

#### Lokasi / Ruangan (Text Input)
- **Label**: Lokasi / Ruangan
- **Placeholder**: Lokasi tempat investigasi dilakukan
- **Contoh**: "Ruang IGD", "Lantai 2 Bangsal A"
- **Icon**: 📍 Map Pin

#### Hasil Investigasi (Textarea)
- **Label**: Hasil Investigasi
- **Required**: Ya
- **Rows**: 6
- **Placeholder**: Jelaskan temuan, informasi, atau hasil yang diperoleh...
- **Use**: Deskripsikan findings secara detail

#### File Evidence (File Upload)
- **Label**: File Evidence / Bukti
- **Accepted Types**:
  - PDF (application/pdf)
  - Images: JPEG, PNG, GIF
  - Documents: DOC, DOCX
  - Spreadsheets: XLS, XLSX
- **Max Size**: 5 MB (5120 KB)
- **Storage**: private (tidak public)
- **Directory**: investigasi/

#### Status (Select)
- **Label**: Status
- **Options**:
  - 📝 Draft
  - ✅ Selesai
- **Default**: Draft
- **Use**: Menandai apakah investigasi sudah selesai

#### Tanggal Investigasi (DateTime Picker)
- **Label**: Tanggal Investigasi
- **Default**: Waktu sekarang (now())
- **Format**: d F Y, H:i (contoh: 12 Maret 2026, 14:30)
- **Required**: Ya
- **Seconds**: Tidak (hanya jam dan menit)

### 3. Auto-Populated Fields (Hidden)

#### kategori
- Automatically set berdasarkan tab yang dipilih
- Values: `interview`, `review_dokumen`, `observasi`
- Tidak perlu input manual

#### investigated_by (User ID)
- Automatically set ke user yang sedang login
- Captured saat data tersimpan
- Digunakan untuk audit trail

### 4. Data Behavior

#### Auto-Assignment
Saat membuat investigasi data, sistem otomatis:
1. Set `kategori` sesuai tab yang aktif
2. Set `investigated_by` ke user yang login
3. Set `investigated_at` ke waktu sekarang
4. Set `status` ke 'draft'
5. Set `created_by` ke user yang login

#### Relationship Filtering
- Repeater hanya menampilkan data untuk kategori yang dipilih
- Data diurutkan by `investigated_at` DESC, lalu `created_at` DESC
- Setiap kategori memiliki repeater terpisah namun menggunakan field name yang sama

### 5. User Experience Features

#### Item Labels
Setiap item di repeater menampilkan:
- **Jika ada sumber**: "📋 Investigasi: {sumber}" (contoh: "📋 Investigasi: Perawat Rina")
- **Jika kosong**: "📋 Data Investigasi Baru"

#### Helpful Text
Setiap field memiliki helper text untuk guidance:
- Sumber: "Nama orang atau dokumen yang dijadikan sumber"
- Lokasi: "Lokasi tempat investigasi dilakukan"
- Hasil: "Deskripsikan hasil investigasi secara detail"
- File: "Upload dokumen atau bukti pendukung"
- Tanggal: "Waktu investigasi dilakukan"

#### Grid Layout
- Sumber dan Lokasi: 2 kolom (lebih compact)
- Hasil Investigasi: Full width
- File Evidence: Full width
- Status dan Tanggal: 2 kolom

## Workflow Penggunaan

### 1. Access Condition
Repeater hanya tersedia ketika:
- User memiliki permission `Investigasi:LaporanInsiden`
- Laporan sudah disimpan (bukan create/new)
- Status laporan adalah investigasi atau sesuai kondisi yang diset

### 2. Adding Investigation Data

**Step 1**: Masuk ke tab kategori yang sesuai (Interview, Review Dokumen, atau Observasi)

**Step 2**: Klik "Add Item" atau button untuk menambah data

**Step 3**: Isi form fields:
- **Sumber**: Nama narasumber atau asal dokumen
- **Lokasi**: Tempat investigasi dilakukan (opsional)
- **Hasil**: Temuan investigasi secara detail (WAJIB)
- **File**: Upload dokumen pendukung jika ada
- **Status**: Pilih Draft atau Selesai
- **Tanggal**: Ubah jika berbeda dengan waktu sekarang

**Step 4**: Sistem otomatis set kategori dan investigator

**Step 5**: Save form untuk menyimpan

### 3. Editing Existing Data

**Step 1**: Buka form laporan insiden di halaman edit

**Step 2**: Scroll ke section "📊 Data Investigasi"

**Step 3**: Buka tab kategori yang sesuai

**Step 4**: Klik item yang ingin diedit (akan expand)

**Step 5**: Ubah value field yang perlu

**Step 6**: Save untuk menyimpan perubahan

### 4. Deleting Data

**Step 1**: Buka form dan scroll ke "📊 Data Investigasi"

**Step 2**: Buka tab kategori

**Step 3**: Klik icon delete (trash) pada item yang ingin dihapus

**Step 4**: Confirm deletion

**Step 5**: Save form untuk apply changes

### 5. Reordering Data

**Step 1**: Hover di atas item yang ingin dipindah

**Step 2**: Gunakan drag handle (biasanya icon dengan 6 dots)

**Step 3**: Drag ke posisi baru

**Step 4**: Save form

## Database Schema

### Tabel: investigation_data

```sql
CREATE TABLE investigation_data (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    laporan_insiden_id BIGINT NOT NULL,
    kategori ENUM('interview', 'review_dokumen', 'observasi'),
    sumber VARCHAR(255) NULLABLE,
    hasil TEXT NOT NULL,
    lokasi VARCHAR(255) NULLABLE,
    file_path VARCHAR(255) NULLABLE,
    investigated_by BIGINT NULLABLE,
    investigated_at DATETIME NULLABLE,
    status VARCHAR(255) DEFAULT 'draft',
    created_by BIGINT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (laporan_insiden_id) REFERENCES laporan_insidens(id) ON DELETE CASCADE,
    FOREIGN KEY (investigated_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX (laporan_insiden_id),
    INDEX (kategori)
);
```

## Model Layer

### InvestigationData Model

#### Methods
- `getKategoriOptions()`: Get available categories
- `getKategoriLabel()`: Get label untuk kategori
- `getStatusOptions()`: Get available statuses
- `getStatusLabel()`: Get label untuk status
- `markCompleted($userId)`: Mark as completed
- `isCompleted()`: Check if completed

#### Relationships
- `laporanInsiden()`: BelongsTo LaporanInsiden
- `investigator()`: BelongsTo User (investigated_by)
- `creator()`: BelongsTo User (created_by)

#### Auto-Setting in boot()
```php
static::creating(function ($model) {
    $model->created_by = $model->created_by ?? auth()->id();
    $model->status = $model->status ?? 'draft';
    $model->investigated_at = $model->investigated_at ?? now();
    $model->investigated_by = $model->investigated_by ?? auth()->id();
});
```

### LaporanInsiden Model

#### Relationship
```php
public function investigationData(): HasMany
{
    return $this->hasMany(InvestigationData::class);
}
```

## Form Configuration

### Location
`app/Filament/Resources/LaporanInsidens/Schemas/LaporanInsidenFormSchema.php`

### Method
`public static function getFieldDataCollection(): Section`

### Key Features
1. **Dynamic Tabs**: Menggunakan closure untuk generate tabs berdasarkan kategori
2. **Filtering**: `modifyQueryUsing()` untuk filter by kategori
3. **Relationship**: Menggunakan repeater dengan relationship `investigationData`
4. **State Management**: Proper `formatStateUsing()` dan `dehydrateStateUsing()` untuk hidden fields

## Troubleshooting

### Problem: Data tidak tersimpan
**Solution**: 
- Pastikan permission `Investigasi:LaporanInsiden` sudah granted
- Pastikan laporan sudah di-save terlebih dahulu
- Check laporan status sesuai kondisi di form config

### Problem: Kategori tidak tersimpan dengan benar
**Solution**:
- Hidden field `kategori` sudah configured dengan `formatStateUsing()` dan `dehydrateStateUsing()`
- Pastikan model boot method up-to-date

### Problem: investigated_by tidak tercapture
**Solution**:
- Hidden field `investigated_by` menggunakan Auth::id()
- Pastikan user sudah login dengan ID yang valid

### Problem: File tidak dapat diupload
**Solution**:
- Check file type - hanya PDF, Image, DOC, XLS yang diizinkan
- Check ukuran file - max 5MB
- Check storage disk permissions

### Problem: Repeater tidak muncul
**Solution**:
- User harus punya permission `Investigasi:LaporanInsiden`
- Laporan harus sudah disimpan (tidak di create page)
- Laporan status harus sesuai kondisi yang diset

## Performance Optimization

### Query Optimization
1. Relationship filtered by kategori dalam closure
2. Indexed pada `laporan_insiden_id` dan `kategori`
3. Ordered by `investigated_at` DESC untuk latest first

### UI Optimization
1. Tabs collapsed when empty (count === 0)
2. Badge counts only shown when > 0
3. Lazy loading sections

## Security Considerations

1. **Authorization**: Controlled via permission `Investigasi:LaporanInsiden`
2. **File Upload**: 
   - Stored as `private` (tidak public access)
   - File types whitelist diterapkan
   - Max size limited to 5MB
3. **User Tracking**: `investigated_by` dan `created_by` auto-captured
4. **Audit Trail**: All attempts recorded via model timestamps

## Future Enhancements

Possible improvements untuk repeater:
1. Add search/filter within category
2. Add bulk actions (delete multiple, change status)
3. Add export to PDF/Excel
4. Add notes/comments field
5. Add attachment count badge
6. Add investigation timeline view
