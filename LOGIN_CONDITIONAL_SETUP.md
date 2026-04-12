# IKP Login Conditional Authentication Documentation

## Overview
Sistem login IKP sekarang mendukung dua mode authentication:
- **SSO/IAM Mode**: Menggunakan IAM (Identity & Access Management) untuk SSO login
- **Filament Mode**: Menggunakan Filament login page default dengan NIP/Password

## Konfigurasi

### Environment Variables (`.env`)
```env
# Enable/Disable SSO
USE_SSO=true              # Set ke true untuk SSO, false untuk Filament default
IAM_ENABLED=true          # Enable IAM features

# IAM Routes Configuration  
IAM_LOGIN_ROUTE=/login    # Route untuk IAM login (default: /sso/login)
IAM_CALLBACK_ROUTE=/callback  # Route untuk IAM callback
```

## Bagaimana Cara Kerjanya

### 1. **Root Route (`/`)**
- Jika `USE_SSO=true` dan `IAM_ENABLED=true` → Redirect ke `/login` (IAM SSO)
- Jika tidak → Redirect ke `/admin` (Filament admin panel)

### 2. **Admin Panel Access (`/admin`)**
- ConditionalAuthenticate middleware mengecek authentication status
- Jika user belum authenticated:
  - **SSO Enabled**: Redirect ke `/login` (IAM SSO login page)
  - **SSO Disabled**: Redirect ke Filament login page

### 3. **Authentication Flow**

#### SSO Mode (USE_SSO=true, IAM_ENABLED=true):
```
User → / 
  ↓ 
Redirect to /login (IAM SSO)
  ↓
IAM Server (SSO Login)
  ↓
Redirect to /callback (IAM Callback)
  ↓
Create/Update User in IKP
  ↓
Set Session/Token
  ↓
Redirect to /admin (Admin Panel)
```

#### Filament Mode (USE_SSO=false or IAM_ENABLED=false):
```
User → /admin
  ↓
Filament Login Page
  ↓
Enter NIP/Password
  ↓
Authenticate
  ↓
Redirect to /admin (Admin Panel)
```

## Files yang Dimodifikasi

### 1. **app/Http/Middleware/ConditionalAuthenticate.php** (BARU)
Custom middleware yang conditional login behavior:
- Check jika user sudah authenticated
- Jika tidak, check SSO status
- Redirect ke IAM login atau Filament login

### 2. **app/Providers/Filament/AdminPanelProvider.php**
- Replace `Filament\Http\Middleware\Authenticate` dengan `ConditionalAuthenticate`
- Tidak register Filament login page jika SSO enabled

### 3. **routes/web.php**
- Tambah root route yang conditional redirect

## Testing

### Test SSO Mode (SSO Enabled)
```bash
# Set di .env
USE_SSO=true
IAM_ENABLED=true
IAM_LOGIN_ROUTE=/login

# Akses aplikasi
curl http://127.0.0.1:8200/
# Harusnya redirect ke /login (IAM SSO)
```

### Test Filament Mode (SSO Disabled)
```bash
# Set di .env
USE_SSO=false
IAM_ENABLED=false

# Akses aplikasi
curl http://127.0.0.1:8200/
# Harusnya redirect ke /admin

# Akses /admin tanpa login
curl http://127.0.0.1:8200/admin
# Harusnya redirect ke Filament login page
```

## Routes

| Route | Nama | Deskripsi |
|-------|------|-----------|
| `/` | root | Conditional redirect ke IAM login atau Filament admin |
| `/login` | iam.sso.login | IAM SSO Login (dari package laravel-iam-client) |
| `/callback` | iam.sso.callback | IAM SSO Callback (dari package laravel-iam-client) |
| `/admin` | admin | Filament Admin Panel |
| `/admin/login` | filament.auth.login | Filament Login Page (hanya jika SSO disabled) |

## Logout

- Jika SSO: `/iam/logout` (IAM initiated logout)
- Jika Filament: Logout link di Filament account widget

## Security Notes

1. **Token Verification**: 
   - `IAM_VERIFY_EACH_REQUEST=true` - Verify JWT setiap request
   - `IAM_VERIFY_REMOTE_EACH_REQUEST=true` - Verify dengan IAM server

2. **Session Sync**:
   - `IAM_SYNC_SESSION_LIFETIME=true` - Sync session lifetime dengan token expiry
   - `IAM_SESSION_LIFETIME_BUFFER=2` - Buffer 2 menit untuk edge cases

3. **Backchannel**:
   - `IAM_BACKCHANNEL_VERIFY=false` - Temporarily disabled untuk testing
   - Aktifkan untuk production

## Troubleshooting

### Issue: Login tidak redirect ke IAM
**Solution**: 
- Check `.env` apakah `USE_SSO=true` dan `IAM_ENABLED=true`
- Check apakah middleware `ConditionalAuthenticate` terdaftar di AdminPanelProvider

### Issue: Filament login page muncul saat SSO enabled
**Solution**:
- Check apakah `$panel->login()` di-skip di AdminPanelProvider
- Check `if (!$ssoEnabled)` logic

### Issue: Token verification gagal
**Solution**:
- Check `IAM_JWT_SECRET` di `.env` match dengan IAM server
- Check `IAM_BACKCHANNEL_VERIFY` setting

## Next Steps

1. Test IAM login flow end-to-end
2. Test Filament login mode
3. Test logout functionality
4. Test token refresh mechanism
5. Test role sync dari IAM ke IKP

