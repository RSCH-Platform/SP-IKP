# 🚀 QUICK REFERENCE: Dashboard Widgets

## ✨ Apa yang Baru
4 Dashboard Chart Widgets dengan Filament Shield permissions:
1. **Status Laporan** - Donut chart distribusi status
2. **Kategori Insiden** - Bar chart kategori terbanyak
3. **Grading Risiko** - Bar chart klasifikasi risiko
4. **Trend Insiden** - Area chart trend 12 bulan

---

## 📍 Aksesnya Dimana?

**URL:** `http://localhost:8200/ikp-application` (atau URL domain Anda)

**Requirement:**
- Sudah login
- User punya permission (`panel_user` role atau lebih tinggi)

---

## ⚙️ Setup (Sudah Selesai!)

Permission commands yang sudah di-run:
```bash
✅ php artisan app:register-dashboard-widget-permissions
✅ php artisan cache:clear
✅ php artisan config:clear
```

Jika perlu re-run:
```bash
# Normal setup
php artisan app:register-dashboard-widget-permissions

# Reset (hapus & buat ulang)
php artisan app:register-dashboard-widget-permissions --reset

# Atau pakai seeder
php artisan db:seed DashboardChartWidgetsSeeder
```

---

## 🎯 Key Features

### 📊 Charts
| Chart | Data | Color |
|-------|------|-------|
| Donut | Status workflow | Semantic (gray/blue/amber/green/orange/purple) |
| Bar (Horizontal) | Top 8 kategori | Multi-color |
| Bar (Vertical) | Risk levels | Semantic (blue→green→yellow→red→black) |
| Area | Monthly trend | Blue gradient |

### 🔄 Auto-Update
Data auto-refresh whenever you:
- Create new incident report
- Update incident status
- Delete report
- Restore deleted report

### 💾 Caching
- Cache duration: **1 hour**
- Manual clear: Call `DashboardChartService::clearCache()`

---

## 🔐 Permission Management (Super Admin)

### View Permission List
1. Login as Super Admin
2. Go to **Manajemen Peran & Izin**
3. Click Edit on any role
4. Tab **Widgets** - See all 4 widget permissions

### Allow/Deny Widgets
1. In widget permissions list
2. Check ✓ to allow, uncheck ✗ to deny
3. Click Save

---

## 📂 File Locations

```
Key Files:
├── app/Filament/Pages/Dashboard.php              [Main page]
├── app/Filament/Widgets/                         [4 Widget classes]
│   ├── IncidentStatusWidget.php
│   ├── IncidentCategoryWidget.php
│   ├── RiskGradingWidget.php
│   └── IncidentTrendWidget.php
├── app/Services/DashboardChartService.php        [Data layer]
├── resources/views/filament/widgets/             [Chart views]
│   ├── incident-status-widget.blade.php
│   ├── incident-category-widget.blade.php
│   ├── risk-grading-widget.blade.php
│   └── incident-trend-widget.blade.php
└── DASHBOARD_WIDGETS_DOCUMENTATION.md            [Full docs]
```

---

## 🐛 Troubleshooting

### Widgets tidak muncul?
```bash
php artisan cache:clear
php artisan app:register-dashboard-widget-permissions
```

### Data tidak update?
```bash
# Force refresh cache
php artisan tinker
>>> \App\Services\DashboardChartService::clearCache()
```

### Chart kosong?
1. Check database ada data `laporan_insidens`
2. Check browser console (F12) for JavaScript errors
3. Verify ApexCharts CDN accessible

---

## 🎨 Want to Customize?

### Change Colors
Edit: `app/Services/DashboardChartService.php`
```php
protected function formatStatusData($data)
{
    $statusColors = [
        'draft' => '#your-color',  // ← Change this
        // ...
    ];
}
```

### Change Cache Duration
Edit: `app/Services/DashboardChartService.php`
```php
protected const CACHE_TTL = 7200;  // 2 hours (instead of 3600 = 1 hour)
```

### Change Widget Order
Edit each widget class: `protected static ?int $sort = X;`
```php
class IncidentStatusWidget extends Widget
{
    protected static ?int $sort = 1;  // ← Lower = appears first
}
```

---

## 💡 Quick Commands

```bash
# Test data generation
php artisan tinker
>>> \App\Models\LaporanInsiden::count()

# Manual cache clear
php artisan tinker
>>> \App\Services\DashboardChartService::clearCache()
>>> exit

# Check permissions
php artisan tinker
>>> \Spatie\Permission\Models\Permission::where('name', 'like', '%widget%')->get()

# Reset all widget permissions
php artisan app:register-dashboard-widget-permissions --reset
```

---

## 📞 Documentation Files

- **DASHBOARD_WIDGETS_IMPLEMENTATION_STATUS.md** - Full implementation details & checklist
- **DASHBOARD_WIDGETS_DOCUMENTATION.md** - Complete technical documentation

---

**Status:** ✅ Ready to Use!  
**Last Updated:** April 15, 2026
