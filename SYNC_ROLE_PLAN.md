# Plan dan Diskusi: Sync Role Aplikasi dengan IAM Client

**Tanggal:** 15 Februari 2026  
**Proyek:** IKP (Laravel + Filament + Spatie Permission)

## Latar Belakang

Pengguna ingin melakukan sinkronisasi role aplikasi dengan role yang ada di client (IAM). Sistem ini adalah penyedia SSO, dimana aplikasi client menggunakan role dari project ini. IAM tidak bisa otomatis membuat role aplikasi, sehingga perlu mekanisme sync manual atau terjadwal.

## Analisa Teknis

### Struktur Aplikasi Saat Ini
- **Framework:** Laravel 12
- **UI/Admin Panel:** Filament v4
- **Permission Management:** Spatie Laravel Permission + Filament Shield
- **Models:** User (dengan HasRoles), LaporanInsiden
- **Policies:** RolePolicy, LaporanInsidenPolicy

### Kebutuhan Sync
- **Sumber Data:** API dari sisi SSO (project ini)
- **Target:** Aplikasi client yang menggunakan SSO
- **Struktur Role:** Sama antara aplikasi dan client
- **Trigger:** Action di Filament Resource Application

## Plan Implementasi

### 1. Model Application
**Status:** Belum ada  
**Kebutuhan:**
- Fields: name, api_endpoint, api_key, status, created_at, updated_at
- Relationship: hasMany roles (jika diperlukan)

### 2. Migration
**File:** `create_applications_table.php`  
**Fields:**
- id (primary key)
- name (string)
- api_endpoint (string, nullable)
- api_key (string, encrypted)
- status (enum: active, inactive)
- timestamps

### 3. Filament Resource
**File:** `ApplicationResource.php`  
**Features:**
- CRUD untuk manage aplikasi client
- Action "Sync Roles" untuk trigger sync
- Form fields: name, api_endpoint, api_key, status

### 4. Service Class
**File:** `ApplicationSyncService.php`  
**Responsibilities:**
- Call API client untuk get roles
- Parse response dan map ke format aplikasi
- Create/update roles menggunakan Spatie Permission
- Handle errors dan logging

### 5. Job untuk Background Processing (Opsional)
**File:** `SyncApplicationRoles.php`  
**Purpose:** Jika sync memakan waktu lama, gunakan queue

### 6. API Endpoint (Opsional)
**Route:** `/api/applications/{id}/sync-roles`  
**Purpose:** Untuk trigger sync via API jika diperlukan

## Tantangan dan Solusi

### Tantangan
1. **Autentikasi API:** Bagaimana client memverifikasi request dari SSO?
2. **Error Handling:** Jika API client down atau response invalid
3. **Data Consistency:** Mencegah duplikasi atau konflik role
4. **Security:** API key storage dan transmission

### Solusi
1. **API Key:** Store encrypted, gunakan Bearer token
2. **Retry Mechanism:** Implement exponential backoff
3. **Validation:** Strict validation pada response API
4. **Logging:** Comprehensive logging untuk audit trail
5. **Rollback:** Ability to revert changes jika sync gagal

## Timeline Estimasi

1. **Week 1:** Create migration, model, dan basic Filament Resource
2. **Week 2:** Implement sync service dan action
3. **Week 3:** Testing, error handling, dan optimization
4. **Week 4:** Documentation dan deployment

## Next Steps

1. Konfirmasi struktur API response dari client
2. Tentukan format autentikasi API
3. Decide apakah perlu background job atau sync langsung
4. Buat mock API untuk testing

## Diskusi Tambahan

- **Frequency:** Manual via action, atau bisa ditambahkan scheduled job jika diperlukan
- **Permissions:** Hanya sync role, atau perlu sync permissions juga?
- **UI Feedback:** Progress bar atau notification saat sync berlangsung
- **Audit:** Logging semua perubahan role untuk compliance

---

**Status:** Plan disimpan, siap untuk implementasi setelah konfirmasi detail API.</content>
<parameter name="filePath">/home/juni/projects/ikp/SYNC_ROLE_PLAN.md