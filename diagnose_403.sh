#!/bin/bash

# Diagnostic script untuk membantu menemukan kenapa user mendapatkan 403 setelah login.
# Jalankan dari root project: bash diagnose_403.sh

set -euo pipefail

echo "=== Cek user dengan NIP 0000.00000 ==="
php artisan tinker --execute="\$u = \App\Models\User::where('nip', '0000.00000')->first(); dump(\$u ? \$u->toArray() : 'User not found');"

echo -e "\n=== Role user ==="
php artisan tinker --execute="\$u = \App\Models\User::where('nip', '0000.00000')->first(); dump(\$u ? \$u->getRoleNames() : 'User not found');"

echo -e "\n=== Semua permission user (langsung & dari role) ==="
php artisan tinker --execute="\$u = \App\Models\User::where('nip', '0000.00000')->first(); dump(\$u ? \$u->getAllPermissions()->pluck('name') : 'User not found');"

echo -e "\n=== Semua permission role super_admin ==="
php artisan tinker --execute="\$r = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first(); dump(\$r ? \$r->permissions->pluck('name') : 'Role not found');"

echo -e "\n=== Cek user bisa akses ViewAny:LaporanInsiden ==="
php artisan tinker --execute="\$u = \App\Models\User::where('nip', '0000.00000')->first(); dump(\$u ? \$u->can('ViewAny:LaporanInsiden') : 'User not found');"
