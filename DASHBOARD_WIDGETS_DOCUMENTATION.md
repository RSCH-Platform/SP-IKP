# Dashboard Chart Widgets - Dokumentasi Lengkap

## 📋 Ringkasan

Aplikasi IKP sekarang dilengkapi dengan **4 Widget Grafik Real-Time** untuk dashboard analytics insiden. Semua widget terintegrasi dengan **Filament Shield** untuk kontrol akses berbasis role.

---

## 📊 Widget yang Tersedia

### 1. **Incident Status Widget** (Donut Chart)
**File:** `app/Filament/Widgets/IncidentStatusWidget.php`

- **Tujuan:** Menampilkan distribusi status laporan insiden
- **Data Yang Ditampilkan:**
  - Draft (abu-abu)
  - Dilaporkan (biru)
  - Revisi (amber)
  - Diverifikasi (hijau)
  - Revisi Unit (orange)
  - Investigasi (ungu)
- **Permission:** `view_incident_status_widget`
- **Sort Order:** 1 (tampil pertama)

### 2. **Incident Category Widget** (Horizontal Bar Chart)
**File:** `app/Filament/Widgets/IncidentCategoryWidget.php`

- **Tujuan:** Menampilkan kategori insiden terbanyak (Top 8)
- **Data Yang Ditampilkan:**
  - Medikasi
  - Prosedur Klinik
  - Dokumentasi
  - Komunikasi
  - Peralatan Medis
  - Infeksi Nosokomial
  - Jatuh
  - Lainnya
- **Permission:** `view_incident_category_widget`
- **Sort Order:** 2

### 3. **Risk Grading Widget** (Bar Chart)
**File:** `app/Filament/Widgets/RiskGradingWidget.php`

- **Tujuan:** Menampilkan distribusi grading risiko
- **Data Yang Ditampilkan:**
  - Biru (Tidak signifikan) - #0ea5e9
  - Hijau (Minor) - #10b981
  - Kuning (Moderat) - #eab308
  - Merah (Mayor) - #ef4444
  - Hitam (Katastropik) - #1f2937
- **Permission:** `view_risk_grading_widget`
- **Sort Order:** 3
- **Warna Semantik:** Blue → Green → Yellow → Red → Black

### 4. **Incident Trend Widget** (Area Chart)
**File:** `app/Filament/Widgets/IncidentTrendWidget.php`

- **Tujuan:** Menampilkan tren insiden 12 bulan terakhir
- **Data Yang Ditampilkan:**
  - Bulan dan jumlah insiden
  - Pola perubahan trend
  - Data historis untuk analytics
- **Permission:** `view_incident_trend_widget`
- **Sort Order:** 4
- **Format:** Smooth curve dengan gradient fill

---

## 🔐 Permission & Filament Shield Integration

### Struktur Permission:
```
Widget Permission Naming Convention:
view_{widget_name}
```

### Permission List:
| Widget | Permission Name | Status |
|--------|-----------------|--------|
| Incident Status | `view_incident_status_widget` | Active |
| Incident Category | `view_incident_category_widget` | Active |
| Risk Grading | `view_risk_grading_widget` | Active |
| Incident Trend | `view_incident_trend_widget` | Active |

### Trait Used:
- `HasWidgetShield` - Otomatis menangani authorization widget dengan Filament Shield

---

## ⚙️ Setup & Installation

### Step 1: Run Seeder untuk Register Permissions
```bash
# Opsi 1: Menggunakan Command Artisan
php artisan app:register-dashboard-widget-permissions

# Opsi 2: Menggunakan Seeder (jika ingin included di db:seed)
php artisan db:seed DashboardChartWidgetsSeeder
```

### Step 2: Configure di Filament Shield UI
1. Login sebagai Super Admin ke Filament Panel
2. Navigate ke: **Manajemen Peran & Izin** (Shield)
3. Edit role yang ingin diberi akses (misal: `panel_user`)
4. Pada tab **Widgets**, check permissions untuk 4 widgets:
   - ✓ view_incident_status_widget
   - ✓ view_incident_category_widget
   - ✓ view_risk_grading_widget
   - ✓ view_incident_trend_widget
5. Simpan perubahan

### Step 3: Verify di Dashboard
1. Login dengan user yang punya permission
2. Navigate ke Dashboard (`/ikp-application`)
3. Lihat 4 widget chart ditampilkan dengan data yang sesuai

---

## 🗂️ File Structure

```
app/
├── Filament/
│   ├── Pages/
│   │   └── Dashboard.php                          [Custom Dashboard Page]
│   └── Widgets/
│       ├── IncidentStatusWidget.php              [Widget 1: Status]
│       ├── IncidentCategoryWidget.php            [Widget 2: Category]
│       ├── RiskGradingWidget.php                 [Widget 3: Risk]
│       └── IncidentTrendWidget.php               [Widget 4: Trend]
│
├── Services/
│   └── DashboardChartService.php                 [Data Service untuk semua widget]
│
└── Console/
    └── Commands/
        └── RegisterDashboardWidgetPermissions.php [Command untuk register permissions]

database/
└── seeders/
    └── DashboardChartWidgetsSeeder.php           [Seeder untuk permissions]

resources/
└── views/
    └── filament/
        └── widgets/
            ├── incident-status-widget.blade.php         [View: Donut Chart]
            ├── incident-category-widget.blade.php       [View: Bar Chart]
            ├── risk-grading-widget.blade.php            [View: Bar Chart]
            └── incident-trend-widget.blade.php          [View: Area Chart]
```

---

## 💾 Data Service: DashboardChartService

### Lokasi: `app/Services/DashboardChartService.php`

### Methods Available:
```php
// 1. Get Status Distribution
$service->getStatusDistribution();
// Returns: ['labels', 'series', 'colors']

// 2. Get Category Ranking (Top 8)
$service->getCategoryRanking();
// Returns: ['labels', 'series']

// 3. Get Risk Grading Distribution
$service->getRiskGradingDistribution();
// Returns: ['labels', 'series', 'colors']

// 4. Get Monthly Trend (12 months)
$service->getMonthlyTrend();
// Returns: ['months', 'series']

// 5. Clear All Cache
DashboardChartService::clearCache();
// Clears 1-hour cache untuk refresh data
```

### Caching Strategy:
- **TTL:** 1 hour (3600 seconds)
- **Cache Keys:**
  - `chart:status-distribution`
  - `chart:category-ranking`
  - `chart:risk-grading`
  - `chart:monthly-trend`
- **Manual Refresh:** `DashboardChartService::clearCache()` (call saat ada perubahan data)

---

## 🎨 Chart Technology

- **Library:** ApexCharts v3.45.0 (via CDN)
- **Rendering:** Real-time di Browser (JavaScript)
- **Download:** Support download chart as PNG/SVG
- **Dark Mode:** Full support Dark Mode Filament

### Chart Types:
| Widget | Type | Details |
|--------|------|---------|
| Status | Donut | 65% inner size, center label |
| Category | Horizontal Bar | Distributed colors, 60% width |
| Risk | Bar | Semantic colors, 70% width |
| Trend | Area | Smooth curve, gradient fill |

---

## 🚀 Advanced Configuration

### Customize Sort Order Widget
Edit di masing-masing Widget class:
```php
protected static ?int $sort = 1;  // 1 untuk pertama, 2 untuk kedua, dst
```

### Customize Cache TTL
Edit di `DashboardChartService.php`:
```php
protected const CACHE_TTL = 3600;  // 1 hour, ubah sesuai kebutuhan
```

### Customize Widget Colors
Edit method `formatXxxData()` di `DashboardChartService.php`:
```php
$statusColors = [
    'draft' => '#custom-color',
    // ...
];
```

---

## 📈 Query Optimization

Semua queries sudah optimized untuk performa:

### 1. Status Distribution
```sql
SELECT status, COUNT(*) as total FROM laporan_insidens GROUP BY status;
```

### 2. Category Ranking
```sql
SELECT kategori_insiden, COUNT(*) as total 
FROM laporan_insidens 
WHERE kategori_insiden IS NOT NULL
GROUP BY kategori_insiden 
ORDER BY total DESC 
LIMIT 8;
```

### 3. Risk Grading
```sql
SELECT grading_risiko, COUNT(*) as total 
FROM laporan_insidens 
WHERE grading_risiko IS NOT NULL
GROUP BY grading_risiko;
```

### 4. Monthly Trend
```sql
SELECT DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total
FROM laporan_insidens 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(created_at, "%Y-%m")
ORDER BY month;
```

---

## 🐛 Troubleshooting

### Widget tidak muncul di Dashboard
**Solusi:**
1. Ensure `HasWidgetShield` trait ada di widget class
2. Run command register permissions: `php artisan app:register-dashboard-widget-permissions`
3. Clear cache: `php artisan cache:clear`
4. Check user role punya permission view widget yang bersangkutan

### Data tidak update
**Solusi:**
1. Clear chart cache: `DashboardChartService::clearCache()`
2. Or wait 1 hour untuk auto-refresh cache
3. Check query database manual jika diperlukan

### Chart tidak render
**Solusi:**
1. Check browser console untuk JavaScript error
2. Ensure CDN ApexCharts accessible: https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js
3. Check view file ada di: `resources/views/filament/widgets/`

---

## 🔄 Event Hooks & Observers

Untuk auto-refresh cache saat ada perubahan insiden, Anda bisa tambahkan di `LaporanInsiden` Observer:

```php
// app/Observers/LaporanInsidenObserver.php
use App\Services\DashboardChartService;

public function created(LaporanInsiden $model)
{
    DashboardChartService::clearCache();
}

public function updated(LaporanInsiden $model)
{
    DashboardChartService::clearCache();
}
```

---

## 📞 Support & Questions

Untuk pertanyaan atau issue:
1. Check file dokumentasi ini terlebih dahulu
2. Lihat komentar di dalam code masing-masing file
3. Test di development environment sebelum production

---

**Last Updated:** April 15, 2026
**Status:** Production Ready ✅
