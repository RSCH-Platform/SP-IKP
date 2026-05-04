# Form Edit Laporan Insiden - Optimasi Performa

## Ringkasan Masalah
Form edit laporan insiden mengalami hang/CPU tidak kuat saat action "Simpan Perubahan" dijalankan karena:
1. Multiple COUNT queries untuk badge di setiap tab
2. File uploads dengan preview yang memicu load file metadata
3. N+1 queries saat load relations
4. Semua section di-render saat form load (tidak lazy)

## Solusi yang Sudah Diimplementasi

### 1. ✅ DataCollectionSection - Caching & Lazy Load
**File:** `app/Filament/Resources/LaporanInsidens/Schemas/Sections/DataCollectionSection.php`

- Added `deferred()` untuk lazy load section
- Cache investigation counts dengan TTL 5 menit
- Disable file preview (`.previewable(false)`) untuk mengurangi metadata load
- Added `.minItems(0)` untuk collapsible repeaters

**Impact:** Mengurangi ~3 COUNT queries per render

### 2. ✅ TimelineGridSection - Lazy Load
**File:** `app/Filament/Resources/LaporanInsidens/Schemas/Sections/TimelineGridSection.php`

- Added `deferred()` untuk lazy load section

**Impact:** Section tidak di-render saat initial load

### 3. ✅ EditLaporanInsiden Page - Eager Loading & Cache
**File:** `app/Filament/Resources/LaporanInsidens/Pages/EditLaporanInsiden.php`

- Added `mutateFormDataBeforeFill()` untuk eager load `investigationData`
- Clear cache sebelum save di `mutateFormDataBeforeSave()`
- Added Cache import

**Impact:** Mengurangi N+1 queries, cache invalidation lebih smart

### 4. ✅ LaporanInsidenResource - Binding Query Optimization
**File:** `app/Filament/Resources/LaporanInsidens/LaporanInsidenResource.php`

- Added `.with('investigationData')` di `getRecordRouteBindingEloquentQuery()`

**Impact:** Reduce N+1 queries saat record binding

## Rekomendasi Tambahan untuk Performa Lebih Baik

### A. Konfigurasi Cache (config/cache.php)
```php
// Gunakan Redis atau Memcached untuk cache yang lebih cepat
'default' => env('CACHE_DRIVER', 'redis'),
```

### B. Database Indexes
Pastikan tabel `investigation_data` memiliki index:
```sql
CREATE INDEX idx_investigation_data_kategori ON investigation_data(kategori);
CREATE INDEX idx_investigation_data_laporan_insiden ON investigation_data(laporan_insiden_id);
CREATE INDEX idx_investigation_kategori_laporan ON investigation_data(laporan_insiden_id, kategori);
```

### C. PHP Configuration Optimization
Pastikan di `.env` atau server config:
```
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=60
```

### D. Query Logging untuk Debugging
Di development, enable query logging untuk identify N+1:
```php
// config/app.php atau di AppServiceProvider
if (config('app.debug')) {
    DB::listen(function ($query) {
        Log::debug($query->sql);
    });
}
```

### E. Form Reduction Strategy
Jika masih hang, pertimbangkan split form menjadi multiple pages:
- Pisahkan "Data Investigasi" menjadi modal/separate page
- Pisahkan "Timeline" ke tab terpisah dengan lazy load

### F. Monitoring
Gunakan tools seperti Barryvdh/laravel-debugbar untuk monitor:
```bash
composer require --dev barryvdh/laravel-debugbar
```

## Testing Checklist

- [ ] Buka form edit laporan dalam status "Investigasi"
- [ ] Klik tab "Review Dokumen" - harus load smooth (lazy)
- [ ] Klik tab "Observasi" - harus load smooth (lazy)
- [ ] Edit beberapa field
- [ ] Klik "Simpan Perubahan" - harus responsif (< 2 detik)
- [ ] Monitor CPU usage saat save - harus normal

## Jika Masih Mengalami Hang

1. **Check Database Indexes**
   ```bash
   php artisan tinker
   >>> DB::table('investigation_data')->getConnection()->getDoctrineSchemaManager()->listTableIndexes('investigation_data')
   ```

2. **Profile dengan Query Log**
   ```php
   DB::enableQueryLog();
   // run operation
   dd(DB::getQueryLog());
   ```

3. **Check Memory Usage**
   - Monitor dengan `memory_get_usage()` saat save
   - Jika > 100MB, ada memory leak

4. **Consider Pagination**
   - Jika investigation data > 100 items, add pagination ke repeater

## Files Modified
- `app/Filament/Resources/LaporanInsidens/Schemas/Sections/DataCollectionSection.php`
- `app/Filament/Resources/LaporanInsidens/Schemas/Sections/TimelineGridSection.php`
- `app/Filament/Resources/LaporanInsidens/Pages/EditLaporanInsiden.php`
- `app/Filament/Resources/LaporanInsidens/LaporanInsidenResource.php`

## Next Steps (Optional)
1. Migrasi ke pagination untuk large datasets
2. Implement debounce untuk auto-save
3. Setup job queue untuk background processing
4. Monitor dengan Application Performance Monitoring (APM)
