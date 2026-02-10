# Filament Shield - Quick Reference

## Roles Hierarchy

```
┌─────────────────────────────────────────────────────┐
│                   super_admin                        │
│    ✅ Akses ke SEMUA fitur (23 permissions)         │
│                                                     │
│  Use: Untuk administrator utama sistem               │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                      admin                           │
│    ✅ Kelola Laporan Insiden + Manage Roles         │
│       16 permissions untuk LaporanInsiden            │
│       + Basic Role management (View, Create, Update)│
│                                                     │
│  Use: Untuk staff yang manage laporan & users       │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│                   panel_user                         │
│    ✅ Hanya bisa lihat daftar laporan              │
│       (ViewAny:LaporanInsiden - 1 permission)      │
│                                                     │
│  Use: Untuk user yang hanya perlu akses read       │
└─────────────────────────────────────────────────────┘
```

## Permission Structure

### Format: `[ACTION]:[RESOURCE]`
```
ViewAny:LaporanInsiden      → Lihat daftar
View:LaporanInsiden         → Lihat detail
Create:LaporanInsiden       → Buat baru
Update:LaporanInsiden       → Edit
Delete:LaporanInsiden       → Soft delete
Restore:LaporanInsiden      → Restore deleted
ForceDelete:LaporanInsiden  → Delete permanent
ForceDeleteAny:LaporanInsiden → Delete all permanent
RestoreAny:LaporanInsiden   → Restore all
Replicate:LaporanInsiden    → Duplicate
Reorder:LaporanInsiden      → Reorder list
```

## Color & Icons Used

| Elemen | Icon | Color |
|--------|------|-------|
| Shield/Roles | heroicon-o-shield-check | Primary |
| Navigation Group | - | Keamanan (Security) |
| Permission Tabs | - | Default |

## Database Tables Involved

```
permissions          // Store all permission names
roles               // Store all role names
role_has_permissions // M2M between roles & permissions
model_has_roles     // M2M between users & roles
model_has_permissions // M2M between users & permissions
```

## Key Configuration Files

### 1. `/config/filament-shield.php`
```php
'shield_resource' => [
    'slug' => 'shield/roles',
    'show_model_path' => false,
    'cluster' => null,
    'tabs' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => true,  // ✨ Enabled
    ],
]
```

### 2. `/app/Providers/AppServiceProvider.php`
```php
Gate::guessPolicyNamesUsing(function (string $modelClass) {
    return str_replace('Models', 'Policies', $modelClass) . 'Policy';
});
```

### 3. `/app/Providers/Filament/AdminPanelProvider.php`
```php
FilamentShieldPlugin::make()
    ->navigationLabel('Manajemen Peran & Izin')
    ->navigationIcon('heroicon-o-shield-check')
    ->navigationGroup('Keamanan')
    ->navigationSort(100)
    // ... optimized layout settings
```

## Quick Commands

### Create Permissions & Roles (Already Done!)
```bash
php artisan db:seed --class=ShieldSeeder
```

### View All Permissions
```bash
php artisan tinker
>>> Permission::pluck('name')
```

### View All Roles with Permissions
```bash
php artisan tinker
>>> Role::with('permissions')->get()->each(fn($r) => dump($r->name, $r->permissions->pluck('name')))
```

### Assign Role to User
```bash
php artisan tinker
>>> $user = User::find(1)
>>> $user->assignRole('admin')
```

### Check User Permissions
```bash
php artisan tinker
>>> $user = User::find(1)
>>> $user->permissions
>>> $user->getAllPermissions()
>>> $user->getCan('ViewAny:LaporanInsiden')
```

### Sync Permissions to Role
```bash
php artisan tinker
>>> $role = Role::where('name', 'admin')->first()
>>> $permissions = Permission::whereIn('name', ['View:LaporanInsiden', 'Update:LaporanInsiden'])->get()
>>> $role->syncPermissions($permissions)
```

## Troubleshooting Checklist

- [ ] User exists in database
- [ ] User memiliki role (check `model_has_roles` table)
- [ ] Role memiliki permission (check `role_has_permissions` table)
- [ ] Permission exist di `permissions` table
- [ ] Policy method return true/false correctly
- [ ] Authentication middleware aktif
- [ ] Clear cache: `php artisan cache:clear`

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Not Authorized" | Check permissions di database |
| Permission tidak muncul | Run `shield:generate --all --panel=admin` |
| User still can access | Check jika punya super_admin role |
| Wrong permission format | Gunakan `Action:Resource` (PascalCase) |
| Policy not working | Ensure policy registered di Gate |

## File Modifications Summary

```
✅ AppServiceProvider.php         - Gate::guessPolicyNamesUsing added
✅ AdminPanelProvider.php         - Shield plugin optimized
✅ config/filament-shield.php     - Custom permissions tab enabled
✅ app/Policies/RolePolicy.php    - NEW: Role authorization policy
✅ database/seeders/ShieldSeeder.php - NEW: Initialize roles & permissions
✅ LaporanInsidens/Pages/*.php    - HasPageShield trait added to all pages
```

## Next Steps

1. **Test dengan super_admin user** → Login dan cek semua fitur
2. **Create test users** dengan role berbeda → Test permission restrictions
3. **Monitor logs** → Check jika ada unauthorized access attempts
4. **Document custom permissions** → Jika ada permission non-standard
5. **Backup database** → Sebelum production deployment
6. **Setup audit logging** → Track siapa access apa dan kapan

---

**Status: ✅ FULLY OPTIMIZED & READY TO USE**
