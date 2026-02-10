# Filament Shield - Developer Quick Start

## 🚀 Getting Started (For New Developers)

### 1. First Time Setup

```bash
# Clone repository
git clone <your-repo>
cd ikp

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed --class=ShieldSeeder

# Clear cache
php artisan cache:clear
php artisan config:cache
```

### 2. Login & Test

1. Start dev server: `php artisan serve`
2. Go to: `http://localhost:8000/admin`
3. Login with:
   - Email: `admin@example.com`
   - Password: `password`

### 3. Access Role Management

Visit: `http://localhost:8000/admin/shield/roles`

---

## 📖 How to Work with Permissions

### Understanding Permissions

```
Format: [Action]:[Resource]
Example: ViewAny:LaporanInsiden

Actions: ViewAny, View, Create, Update, Delete, Restore, ForceDelete, etc.
```

### Check User Permissions

```php
// In your controller or service
$user = auth()->user();

// Check single permission
if ($user->can('ViewAny:LaporanInsiden')) {
    // Show list
}

// Check role
if ($user->hasRole('admin')) {
    // Admin only
}

// Check multiple
if ($user->can('Create:LaporanInsiden') || $user->hasRole('super_admin')) {
    // Can create
}

// Get all permissions
$permissions = $user->getAllPermissions();
$roles = $user->getRoleNames();
```

### In Blade Templates

```blade
<!-- Show if user has permission -->
@can('Create:LaporanInsiden')
    <a href="{{ route('filament.admin.resources.laporan-insidens.create') }}">
        Buat Laporan Baru
    </a>
@endcan

<!-- Show if user has role -->
@role('admin')
    <div>Admin only content</div>
@endrole
```

### In Routes

```php
// Require specific permission
Route::post('/laporan', [LaporanController::class, 'store'])
    ->middleware('can:Create:LaporanInsiden');

// Require any permission
Route::middleware('can:ViewAny:LaporanInsiden')->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index']);
});
```

### In Policies

```php
// All policies inherit Shield permissions
class LaporanInsidenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:LaporanInsiden');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:LaporanInsiden');
    }
    
    // ... etc
}
```

---

## 🔧 Adding a New Resource

### Step 1: Create Resource & Policy

```bash
php artisan make:filament-resource YourResource
php artisan make:policy YourResourcePolicy -m YourModel
```

### Step 2: Update Policy

```php
<?php
namespace App\Policies;

class YourResourcePolicy
{
    public function viewAny(User $user): bool {
        return $user->can('ViewAny:YourResource');
    }
    
    public function view(User $user, YourModel $model): bool {
        return $user->can('View:YourResource');
    }
    
    // ... add all 11 methods
}
```

### Step 3: Update Seeder

```php
// database/seeders/ShieldSeeder.php

$permissions = [
    // ... existing
    'ViewAny:YourResource',
    'View:YourResource',
    'Create:YourResource',
    'Update:YourResource',
    'Delete:YourResource',
    'Restore:YourResource',
    'ForceDelete:YourResource',
    'ForceDeleteAny:YourResource',
    'RestoreAny:YourResource',
    'Replicate:YourResource',
    'Reorder:YourResource',
];

// Add to appropriate roles
$adminRole->givePermissionTo([
    'ViewAny:YourResource',
    'View:YourResource',
    'Create:YourResource',
    'Update:YourResource',
    'Delete:YourResource',
]);
```

### Step 4: Seed Database

```bash
php artisan db:seed --class=ShieldSeeder
```

### Step 5: Test

```php
// In tinker
$user = User::find(1);
$user->can('ViewAny:YourResource'); // Should be true for admin
```

---

## 🧪 Testing Permissions

### Manual Testing

```bash
# Open tinker
php artisan tinker

# Create test user
$user = User::factory()->create()

# Assign role
$user->assignRole('panel_user')

# Check permission
$user->can('ViewAny:LaporanInsiden')

# Should return: false initially (panel_user only has ViewAny:LaporanInsiden)

# Test with admin role
$user->syncRoles('admin')
$user->can('Create:LaporanInsiden') # true
```

### Unit Tests

```php
// tests/Feature/AuthorizationTest.php

class AuthorizationTest extends TestCase
{
    public function test_super_admin_can_access_all()
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        
        $this->assertTrue($user->can('Create:LaporanInsiden'));
        $this->assertTrue($user->can('Delete:LaporanInsiden'));
    }
    
    public function test_panel_user_can_only_view()
    {
        $user = User::factory()->create();
        $user->assignRole('panel_user');
        
        $this->assertTrue($user->can('ViewAny:LaporanInsiden'));
        $this->assertFalse($user->can('Create:LaporanInsiden'));
    }
}
```

---

## 🛠️ Troubleshooting

### User Can't Access Resource

```php
// Debug checklist
$user = User::find(1);

// 1. Check roles
dd($user->getRoleNames()); // Should see role name

// 2. Check role permissions
$role = $user->roles->first();
dd($role->permissions); // Should see permissions

// 3. Check direct user permissions
dd($user->direct_permissions); // Check direct perms

// 4. Check can() method
dd($user->can('ViewAny:LaporanInsiden')); // Should be true

// 5. Check if policy exists
dd(\Illuminate\Support\Facades\Gate::getPolicyFor(LaporanInsiden::class));
```

### Permission Not In Database

```php
// Check if permission exists
Spatie\Permission\Models\Permission::where('name', 'ViewAny:LaporanInsiden')->first();

// If not, add it manually
Spatie\Permission\Models\Permission::create([
    'name' => 'ViewAny:LaporanInsiden',
    'guard_name' => 'web'
]);

// Or re-seed
php artisan db:seed --class=ShieldSeeder
```

### Clear Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue if running
php artisan queue:restart
```

---

## 📚 Useful Commands

```bash
# View all permissions
php artisan tinker
>>> Spatie\Permission\Models\Permission::all()

# View all roles
>>> Spatie\Permission\Models\Role::all()

# View user's roles
>>> User::find(1)->getRoleNames()

# View user's permissions
>>> User::find(1)->getAllPermissions()->pluck('name')

# Assign role to user
>>> User::find(1)->assignRole('admin')

# Revoke role from user
>>> User::find(1)->removeRole('admin')

# Give permission directly to user
>>> User::find(1)->givePermissionTo('ViewAny:LaporanInsiden')

# Check user can
>>> User::find(1)->can('Create:LaporanInsiden')
```

---

## 🎯 Common Patterns

### Only Super Admin Can Delete

```php
// In LaporanInsidenPolicy
public function delete(User $user, LaporanInsiden $model): bool
{
    return $user->hasRole('super_admin');
}

// Or
public function delete(User $user, LaporanInsiden $model): bool
{
    return $user->can('Delete:LaporanInsiden') && $user->hasRole('super_admin');
}
```

### Owner Can Edit Own Record

```php
public function update(User $user, LaporanInsiden $model): bool
{
    return $user->can('Update:LaporanInsiden') 
        || $model->user_id === $user->id;
}
```

### Soft Delete Protection

```php
public function forceDelete(User $user, LaporanInsiden $model): bool
{
    return $user->hasRole('super_admin') && $user->can('ForceDelete:LaporanInsiden');
}

public function restore(User $user, LaporanInsiden $model): bool
{
    return $user->can('Restore:LaporanInsiden');
}
```

---

## 📖 Reference

| File | Purpose |
|------|---------|
| `config/filament-shield.php` | Shield configuration |
| `app/Policies/*.php` | Resource policies |
| `database/seeders/ShieldSeeder.php` | Initialize roles & permissions |
| `app/Models/User.php` | User model with HasRoles |
| `SHIELD_SETUP_GUIDE.md` | Full documentation |
| `SHIELD_QUICK_REFERENCE.md` | Quick reference |

---

## 🚨 Important Notes

1. **Always backup database** before seeding production
2. **Test permissions** before deploying to users
3. **Never assign super_admin** lightly - only trusted admins
4. **Add validation** in rules before permissions for UX
5. **Document custom permissions** in code or wiki
6. **Monitor audit logs** for permission issues

---

**Last Updated**: February 10, 2026  
**For Questions**: Check SHIELD_SETUP_GUIDE.md or SHIELD_QUICK_REFERENCE.md
