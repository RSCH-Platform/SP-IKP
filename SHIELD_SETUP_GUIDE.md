# Filament Shield - Setup & Optimization Guide

## Overview
Filament Shield adalah plugin yang paling powerful untuk mengelola **Roles**, **Permissions**, dan **Policies** di Filament Panel. Dokumentasi ini menjelaskan setup dan optimasi yang telah dilakukan untuk aplikasi IKP.

## ✅ Apa yang Sudah Dikonfigurasi

### 1. **Installation & Configuration**
- ✅ Plugin sudah diinstall via Composer: `bezhansalleh/filament-shield`
- ✅ Config file dipublikasi di: `/config/filament-shield.php`
- ✅ User model sudah menambahkan `HasRoles` trait (Spatie Permission)

### 2. **Plugin Registration**
- ✅ FilamentShieldPlugin terdaftar di `AdminPanelProvider`
- ✅ Navigation dikonfigurasi dengan label Indonesia: "Manajemen Peran & Izin"
- ✅ Ditempatkan di group "Keamanan" dengan sort order 100
- ✅ Layout dioptimalkan untuk responsive (grid columns: 1-2-3)

### 3. **Authorization Setup**
- ✅ AppServiceProvider menambahkan Gate policy guesser untuk auto-resolution
- ✅ `LaporanInsidenPolicy` sudah dikonfigurasi dengan semua method Shield
- ✅ `RolePolicy` dibuat untuk mengamankan role management
- ✅ Semua resource pages ditambahkan `HasPageShield` trait

### 4. **Roles & Permissions**
ShieldSeeder telah membuat struktur role dengan permissions:

| Role | Permissions | Deskripsi |
|------|------------|-----------|
| **super_admin** | 23 (semua) | Akses penuh ke seluruh sistem |
| **admin** | 16 | Kelola laporan + manage roles dasar |
| **panel_user** | 1 | Hanya bisa lihat daftar laporan |

#### Permission Format (PascalCase dengan separator `:`)
```
ViewAny:LaporanInsiden    // Lihat daftar
View:LaporanInsiden       // Lihat detail
Create:LaporanInsiden     // Buat baru
Update:LaporanInsiden     // Edit
Delete:LaporanInsiden     // Hapus soft
Restore:LaporanInsiden    // Restore
ForceDelete:LaporanInsiden    // Hapus permanen
ForceDeleteAny:LaporanInsiden // Hapus semua permanen
RestoreAny:LaporanInsiden     // Restore semua
Replicate:LaporanInsiden      // Duplicate record
Reorder:LaporanInsiden        // Reorder records

// Role Management
ViewAny:Role, View:Role, Create:Role, Update:Role, Delete:Role, etc.
```

### 5. **Resource Pages Configuration**
Setiap halaman resource sudah menambahkan trait `HasPageShield`:
- `ListLaporanInsidens` → Enforce `ViewAny:LaporanInsiden`
- `CreateLaporanInsiden` → Enforce `Create:LaporanInsiden`
- `ViewLaporanInsiden` → Enforce `View:LaporanInsiden`
- `EditLaporanInsiden` → Enforce `Update:LaporanInsiden`

### 6. **Shield UI Features**
Di Role Resource admin panel (`/admin/shield/roles`):
- ✅ Tabs: Pages, Widgets, Resources, Custom Permissions
- ✅ Checkbox-based permission assignment
- ✅ Global search enabled
- ✅ Bulk actions available

## 📁 File Structure

```
app/
  ├── Policies/
  │   ├── LaporanInsidenPolicy.php    (✅ Configured)
  │   └── RolePolicy.php              (✨ Baru: Manage role authorization)
  ├── Providers/
  │   ├── AppServiceProvider.php       (✅ Gate policy guesser)
  │   └── Filament/
  │       └── AdminPanelProvider.php   (✅ Shield plugin optimized)
  └── Filament/
      └── Resources/
          └── LaporanInsidens/
              └── Pages/
                  ├── ListLaporanInsidens.php      (✅ + HasPageShield)
                  ├── CreateLaporanInsiden.php     (✅ + HasPageShield)
                  ├── EditLaporanInsiden.php       (✅ + HasPageShield)
                  └── ViewLaporanInsiden.php       (✅ + HasPageShield)
config/
  └── filament-shield.php              (✅ Optimized)
database/
  └── seeders/
      └── ShieldSeeder.php             (✨ Baru: Initialize roles & permissions)
```

## 🔧 Cara Menggunakan

### 1. **Melihat & Mengelola Roles**
```
URL: /admin/shield/roles
```
- Lihat semua roles yang ada
- Buat role baru
- Edit permissions untuk setiap role
- Delete role (jika tidak digunakan)

### 2. **Melihat Roles User**
Edit user di resources, lihat field `roles` untuk assign roles.

### 3. **Check User Permissions di Code**
```php
// Check permission
if ($user->can('Create:LaporanInsiden')) {
    // User dapat membuat laporan
}

// Check role
if ($user->hasRole('admin')) {
    // User adalah admin
}

// Multiple checks
if ($user->can('Update:LaporanInsiden') || $user->hasRole('super_admin')) {
    // Allow editing
}

// In routes
Route::get('/laporan', function () {
    // Authenticated users only
})->middleware('auth');

// With policy
Route::post('/laporan/{laporan}/update', [LaporanController::class, 'update'])
    ->middleware('can:update,laporan');
```

### 4. **Direct User Assignment (Contoh di UserSeeder)**
```php
use Spatie\Permission\Models\Role;

$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
]);

$admin->assignRole('admin');
// atau
$admin->syncRoles(['admin', 'super_admin']);
```

## 🛡️ Policy Enforcement

### Automatic via Filament Resource
```php
// LaporanInsidenResource dengan policy otomatis
// Filament akan check policy methods secara otomatis:
// - viewAny() untuk list action
// - view() untuk view action
// - create() untuk create button
// - update() untuk edit button
// - delete() untuk delete action
```

### Manual in Controllers
```php
// Check permission sebelum action
$this->authorize('update', $laporan);

// Atau use middleware
Route::put('/laporan/{laporan}', [LaporanController::class, 'update'])
    ->middleware('can:update,laporan');
```

## 📊 Permission Levels

### ViewAny (view daftar)
```php
// Permissions needed: ViewAny:LaporanInsiden
// Shown in: List page, navigation menu
```

### View (lihat detail)
```php
// Permissions needed: View:LaporanInsiden
// Shown in: Detail/View page, inline viewers
```

### Create (membuat)
```php
// Permissions needed: Create:LaporanInsiden
// Shown in: Create button, form access
```

### Update (edit)
```php
// Permissions needed: Update:LaporanInsiden
// Shown in: Edit button, edit form access
```

### Delete (soft delete)
```php
// Permissions needed: Delete:LaporanInsiden
// Shown in: Delete button (soft delete)
```

### Restore (restore soft-deleted)
```php
// Permissions needed: Restore:LaporanInsiden
// Shown in: Restore button pada deleted records
```

### ForceDelete (hapus permanent)
```php
// Permissions needed: ForceDelete:LaporanInsiden
// Shown in: Force Delete button (permanent delete)
```

## 🔄 Workflow untuk Menambah Resource Baru

Kalau Anda membuat resource baru (misalnya `UserResource`), ikuti langkah ini:

### 1. Create Resource dan Policy
```bash
php artisan make:filament-resource User
php artisan make:policy UserPolicy -m User
```

### 2. Update Policy dengan Shield Format
```php
<?php
namespace App\Policies;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->can('ViewAny:User');
    }
    // ... dst
}
```

### 3. Add HasPageShield ke Pages
```php
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ListUsers extends ListRecords {
    use HasPageShield;
    // ...
}
```

### 4. Update ShieldSeeder
```php
$permissions = [
    'ViewAny:User',
    'View:User',
    'Create:User',
    // ... dst
];
```

### 5. Run Seeder
```bash
php artisan db:seed --class=ShieldSeeder
```

## 🚀 Advanced Features

### Custom Permissions
Untuk permissions yang tidak tied to resources (misalnya "Approve:Report"):

```php
// config/filament-shield.php
'custom_permissions' => [
    'Approve:Report' => 'Approve Report',
    'Export:Report' => 'Export Report',
];
```

### Permission Localization
```bash
php artisan shield:translation id --panel=admin
```

Ini membuat file `lang/id/filament-shield.php` untuk translate permission labels.

### Policy Path Customization
Jika Anda ingin policies disimpan di folder berbeda:

```php
// config/filament-shield.php
'policies' => [
    'path' => app_path('Policies/Custom'),
    // ...
]
```

## 🧪 Testing

### Test Permission
```php
public function test_user_can_view_laporan()
{
    $user = User::factory()->create();
    $user->givePermissionTo('ViewAny:LaporanInsiden');
    
    $laporan = LaporanInsiden::factory()->create();
    
    $this->actingAs($user)
        ->get("/admin/laporan-insidens/{$laporan->id}")
        ->assertSuccessful();
}
```

### Test Role
```php
public function test_admin_can_manage_roles()
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    // Admin should have permission to manage roles
    $this->assertTrue($admin->can('ViewAny:Role'));
}
```

## 📝 Tips & Best Practices

1. **Always use Role + Policies** - Jangan hanya rely on policy methods tanpa roles
2. **Keep Super Admin Limited** - Hindari assign super_admin ke terlalu banyak user
3. **Use Meaningful Names** - Naming permissions jelas dan konsisten
4. **Test Permissions** - Selalu test permission workflows di development
5. **Document Custom Permissions** - Dokumentasikan permission custom atau non-standard
6. **Regular Audit** - Regularly check roles dan permissions assignments
7. **Use Seeding** - Gunakan seeder untuk initialize roles, jangan manual

## 🐛 Troubleshooting

### Permission Tidak Muncul di Shield UI
```bash
# Regenerate permissions
php artisan shield:generate --all --panel=admin

# Or check if they're in database
php artisan tinker
>>> Permission::all()
```

### User Tidak Bisa Access Resource
1. Check di database: apakah user punya role?
2. Check role: apakah role punya permission untuk resource?
3. Check policy: apakah policy method return true?

### "Not Authorized" Error
```php
// Debug
dd(auth()->user()->permissions);  // Check permissions
dd(auth()->user()->roles);         // Check roles
dd(Gate::allows('ViewAny:LaporanInsiden'));  // Check gate
```

## 📚 Resources

- [Filament Shield Documentation](https://filamentphp.com/plugins/bezhansalleh-shield)
- [Spatie Permission Package](https://github.com/spatie/laravel-permission)
- [Filament Panel Authorization](https://filamentphp.com/docs/3.x/panels/authentication)

---

**Setup selesai ✅**  
Sekarang Filament Panel Anda sudah fully protected dengan role-based access control!
