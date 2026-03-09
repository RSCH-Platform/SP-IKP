# Optimasi Workflow dan Sinkronisasi Status

## Ringkasan
Dokumen ini menjelaskan optimasi yang telah dilakukan untuk memastikan workflow status dan timestamp/user ID tersinkronisasi dengan benar pada tabel `laporan_insidens`.

## Masalah yang Ditemukan
1. **Status tidak sinkron dengan timestamp**: Ketika status berubah menjadi "dilaporkan", field `reported_at` dan `reported_by` tidak otomatis terisi
2. **Seeder tidak konsisten**: Seeder membuat record dengan status tertentu tanpa mengisi workflow fields yang sesuai
3. **Manual update bypass workflow**: Update langsung ke database tidak memicu sinkronisasi otomatis

## Solusi yang Diimplementasikan

### 1. Model Observer Auto-Sync
**File**: `app/Models/LaporanInsiden.php`

Ditambahkan logic di method `boot()` untuk auto-sync timestamps dan user IDs:

```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->nomor_laporan)) {
            $model->nomor_laporan = self::generateNomorLaporan();
        }
        
        // Auto-sync saat membuat record
        self::syncStatusTimestamps($model);
    });

    static::updating(function ($model) {
        // Auto-sync saat status berubah
        if ($model->isDirty('status')) {
            self::syncStatusTimestamps($model);
        }
    });
}
```

### 2. Kolom `reported_by` 
**File**: `database/migrations/2026_03_09_233736_add_reported_by_to_laporan_insidens.php`

Ditambahkan kolom baru untuk mencatat siapa yang melaporkan insiden:

```php
$table->foreignId('reported_by')->nullable()
    ->after('status')
    ->constrained('users')
    ->nullOnDelete();
```

### 3. Relationship Baru
**File**: `app/Models/LaporanInsiden.php`

```php
public function reporter(): BelongsTo
{
    return $this->belongsTo(User::class, 'reported_by');
}
```

### 4. Metode `syncStatusTimestamps()`
Logic sinkronisasi berdasarkan status:

| Status | Fields yang Di-sync |
|--------|-------------------|
| `draft` | - |
| `dilaporkan` | `reported_by`, `reported_at` |
| `diverifikasi` | `verified_by`, `verified_at`, `reported_by`, `reported_at` (defensive) |
| `revisi` / `revisi_unit` | `rejected_by`, `rejected_at` |
| `investigasi` | Semua field di atas (defensive) |

**Defensive coding**: Jika status sudah lanjut (misal: investigasi) tapi reported_at belum terisi, akan diisi otomatis.

### 5. Update Workflow Methods
Semua workflow methods di model sudah diupdate:

- `submitLaporan()`: Set `reported_by` dan `reported_at`
- `verifikasiLaporan()`: Set `verified_by`, `verified_at`, plus defensive untuk reported fields
- `mulaiInvestigasi()`: Defensive untuk semua workflow fields

### 6. Seeder Optimization
**File**: `database/seeders/LaporanInsidenSeeder.php`

Setiap record sekarang dibuat dengan workflow fields yang benar sesuai statusnya:

```php
// Contoh: Status dilaporkan
'status' => 'dilaporkan',
'reported_by' => $reporter->id,
'reported_at' => now()->subDays(2),

// Contoh: Status investigasi
'status' => 'investigasi',
'reported_by' => $reporter->id,
'reported_at' => now()->subDays(1),
'verified_by' => $reporter->id,
'verified_at' => now()->subDays(1)->addHours(2),
```

## Workflow Status Diagram

```
draft → dilaporkan → diverifikasi → investigasi
         ↓              ↓
       revisi      revisi_unit
```

### Field Requirements per Status:

1. **draft**: Tidak ada requirement khusus
2. **dilaporkan**: 
   - `reported_by` ✓
   - `reported_at` ✓
3. **diverifikasi**:
   - `reported_by` ✓
   - `reported_at` ✓
   - `verified_by` ✓
   - `verified_at` ✓
   - `grading_risiko` ✓ (required sebelum investigasi)
4. **investigasi**:
   - Semua field di atas ✓
5. **revisi / revisi_unit**:
   - `rejected_by` ✓
   - `rejected_at` ✓
   - `rejection_reason` ✓

## Cara Penggunaan

### Membuat Laporan Baru
```php
// Otomatis: Model observer akan set reported_by jika statusnya 'dilaporkan'
$laporan = LaporanInsiden::create([
    'status' => 'dilaporkan',
    // ... field lainnya
]);
// reported_by dan reported_at akan otomatis terisi!
```

### Update Status
```php
// Option 1: Gunakan workflow methods (RECOMMENDED)
$laporan->submitLaporan(); // Set status + reported fields
$laporan->verifikasiLaporan(auth()->id()); // Set status + verified fields

// Option 2: Manual update (auto-sync tetap jalan)
$laporan->update(['status' => 'dilaporkan']);
// reported_by dan reported_at akan otomatis terisi!
```

### Query dengan Relationship
```php
// Eager load reporter
$laporans = LaporanInsiden::with('reporter', 'verifier')->get();

foreach ($laporans as $laporan) {
    echo $laporan->reporter->name; // Nama pelapor
    echo $laporan->reported_at; // Waktu dilaporkan
}
```

## Testing

### Test Manual
1. Buat laporan baru dengan status "dilaporkan"
2. Cek bahwa `reported_by` dan `reported_at` terisi otomatis
3. Update status ke "diverifikasi"
4. Cek bahwa `verified_by` dan `verified_at` terisi

### Test di Tinker
```php
php artisan tinker

// Buat laporan baru
$laporan = LaporanInsiden::create([
    'status' => 'dilaporkan',
    'user_id' => 1,
    'unit_kerja_id' => 1,
    'nama_pelapor' => 'Test',
    // ... field wajib lainnya
]);

// Cek auto-sync
dd($laporan->reported_at, $laporan->reported_by);
```

## Migration

Untuk apply perubahan ke database yang sudah ada:

```bash
# Tambah kolom reported_by
php artisan migrate

# Jika ingin reset ulang database (HATI-HATI: akan hapus semua data)
php artisan migrate:fresh --seed
```

## Breaking Changes

⚠️ **TIDAK ADA** - Semua perubahan backward compatible:
- Kolom `reported_by` nullable
- Auto-sync hanya berjalan jika field masih kosong
- Existing records tidak akan terpengaruh kecuali di-update statusnya

## Checklist Implementasi

- [x] Tambahkan kolom `reported_by` di migration
- [x] Tambahkan relationship `reporter()` di model
- [x] Implementasi `syncStatusTimestamps()` di model observer
- [x] Update workflow methods (`submitLaporan`, dll)
- [x] Update seeder dengan workflow fields yang benar
- [x] Run migration
- [x] Test manual update status
- [ ] Update Filament form/infolist untuk tampilkan `reporter`
- [ ] Update policy jika perlu (akses berdasarkan reporter)

## Next Steps (Opsional)

1. **Display Reporter di Filament**: Tambahkan field di infolist untuk menampilkan nama reporter
2. **Filter by Reporter**: Tambahkan filter di table untuk cari berdasarkan reporter
3. **Notification**: Kirim notifikasi ke reporter saat status berubah
4. **Audit Log**: Log semua perubahan status untuk audit trail

## File yang Dimodifikasi

1. `app/Models/LaporanInsiden.php` - Model dengan observer dan workflow methods
2. `database/migrations/2026_03_09_233736_add_reported_by_to_laporan_insidens.php` - Migration baru
3. `database/seeders/LaporanInsidenSeeder.php` - Seeder dengan workflow fields

## Verifikasi

Untuk memverifikasi bahwa auto-sync berfungsi:

```sql
-- Cek record yang statusnya dilaporkan tapi reported_at NULL
SELECT id, status, reported_at, reported_by 
FROM laporan_insidens 
WHERE status = 'dilaporkan' AND reported_at IS NULL;

-- Seharusnya tidak ada hasil (atau akan terisi saat di-update)
```

---

**Dokumentasi dibuat**: 10 Maret 2026  
**Versi**: 1.0  
**Author**: IKP System Optimization
