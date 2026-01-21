# Schema Update Documentation

## Tanggal: 21 Januari 2026

## 📋 Ringkasan Perubahan

Update struktur database sesuai dengan schema yang telah ditentukan, dengan fokus pada:
1. Field `created_by` menjadi NOT NULL di semua tabel
2. Menambahkan indexes yang diperlukan untuk performa
3. Mengubah tipe data `created_date` dari `timestamp` menjadi `datetime` dengan default CURRENT_TIMESTAMP
4. Mengubah `menu_order` dari `integer` menjadi `unsigned integer`

## 🗄️ Perubahan Per Tabel

### 1. ms_permissions

**Perubahan:**
- ✅ `created_by` → NOT NULL (sebelumnya nullable)
- ✅ `created_date` → datetime dengan default CURRENT_TIMESTAMP (sebelumnya timestamp)
- ✅ Tambah index: `created_by`, `updated_by`, `is_active`
- ✅ Tambah foreign key: `created_by`, `updated_by` → `ms_users.user_id`

**Schema Final:**
```sql
CREATE TABLE ms_permissions (
    permission_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    permission_code VARCHAR(100) NOT NULL UNIQUE,
    permission_name VARCHAR(150) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE,
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES ms_users(user_id),
    FOREIGN KEY (updated_by) REFERENCES ms_users(user_id)
);
```

### 2. ms_role

**Perubahan:**
- ✅ `created_by` → NOT NULL (sebelumnya nullable)
- ✅ `created_date` → datetime dengan default CURRENT_TIMESTAMP (sebelumnya datetime nullable)
- ✅ Tambah index: `created_by`, `updated_by`, `is_active`
- ✅ Hapus index: `role_code` (sudah ada unique constraint)

**Schema Final:**
```sql
CREATE TABLE ms_role (
    role_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_code VARCHAR(10) NOT NULL UNIQUE,
    role_name VARCHAR(50) NOT NULL,
    role_description VARCHAR(200) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE,
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES ms_users(user_id),
    FOREIGN KEY (updated_by) REFERENCES ms_users(user_id)
);
```

### 3. ms_role_permissions

**Perubahan:**
- ✅ `created_by` → NOT NULL (sebelumnya nullable)
- ✅ `created_date` → datetime dengan default CURRENT_TIMESTAMP (sebelumnya timestamp)
- ✅ Tambah index: `created_by`, `updated_by`, `is_active`
- ✅ Primary key composite: `(role_id, permission_id)`

**Schema Final:**
```sql
CREATE TABLE ms_role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE,
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    PRIMARY KEY (role_id, permission_id),
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (role_id) REFERENCES ms_role(role_id),
    FOREIGN KEY (permission_id) REFERENCES ms_permissions(permission_id)
);
```

### 4. ms_user_roles

**Perubahan:**
- ✅ `created_by` → NOT NULL (sebelumnya nullable)
- ✅ `created_date` → datetime dengan default CURRENT_TIMESTAMP (sebelumnya timestamp)
- ✅ Tambah index: `is_active`
- ✅ Index existing: `user_id`, `role_id`

**Schema Final:**
```sql
CREATE TABLE ms_user_roles (
    user_role_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_date TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE,
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (user_id) REFERENCES ms_users(user_id),
    FOREIGN KEY (role_id) REFERENCES ms_role(role_id)
);
```

### 5. ms_menus

**Perubahan:**
- ✅ `created_by` → NOT NULL (sebelumnya nullable)
- ✅ `created_date` → datetime dengan default CURRENT_TIMESTAMP (sebelumnya timestamp)
- ✅ `menu_order` → UNSIGNED INTEGER (sebelumnya integer)
- ✅ Tambah index: `created_by`, `updated_by`, `is_active`
- ✅ Index existing: `parent_id`, `menu_order`
- ✅ Tambah foreign key: `created_by`, `updated_by` → `ms_users.user_id`

**Schema Final:**
```sql
CREATE TABLE ms_menus (
    menu_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    menu_code VARCHAR(50) NOT NULL UNIQUE,
    menu_name VARCHAR(100) NOT NULL,
    menu_url VARCHAR(255) NULL,
    menu_icon VARCHAR(50) NULL,
    parent_id BIGINT UNSIGNED NULL,
    menu_order INT UNSIGNED NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE,
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    INDEX idx_parent_id (parent_id),
    INDEX idx_menu_order (menu_order),
    INDEX idx_is_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES ms_users(user_id),
    FOREIGN KEY (updated_by) REFERENCES ms_users(user_id)
);
```

### 6. ms_role_menus

**Perubahan:**
- ✅ `created_by` → NOT NULL (sebelumnya nullable)
- ✅ `created_date` → datetime dengan default CURRENT_TIMESTAMP (sebelumnya timestamp)
- ✅ Tambah index: `menu_id`, `role_id`, `is_active`
- ✅ Unique constraint: `(role_id, menu_id)`

**Schema Final:**
```sql
CREATE TABLE ms_role_menus (
    role_menu_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    menu_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE,
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_menu_id (menu_id),
    INDEX idx_role_id (role_id),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_role_menu (role_id, menu_id),
    
    FOREIGN KEY (role_id) REFERENCES ms_role(role_id),
    FOREIGN KEY (menu_id) REFERENCES ms_menus(menu_id)
);
```

## 📁 File yang Diubah

### Migrations
1. ✅ `database/migrations/2024_01_01_000001_create_rbac_tables.php` - Updated untuk fresh install
2. ✅ `database/migrations/2024_01_01_000002_create_ms_menus_table.php` - Updated untuk fresh install
3. ✅ `database/migrations/2026_01_21_093822_update_rbac_tables_structure.php` - Migration baru untuk existing database

### Seeders
1. ✅ `database/seeders/RBACSeeder.php` - Updated untuk menambahkan `created_by` di semua insert

### Models
Tidak ada perubahan di model karena:
- Model sudah menggunakan `$fillable` yang include `created_by`
- Timestamps sudah di-handle dengan `const CREATED_AT` dan `UPDATED_AT`
- Boot method sudah handle `unique_id` generation

## 🚀 Cara Menjalankan Migration

### Untuk Database Baru (Fresh Install)
```bash
php artisan migrate:fresh --seed
```

### Untuk Database yang Sudah Ada
```bash
# Jalankan migration update
php artisan migrate

# Jika ada error, rollback dulu
php artisan migrate:rollback
php artisan migrate
```

## ⚠️ Catatan Penting

### 1. created_by NOT NULL
Semua tabel sekarang require `created_by` saat insert. Pastikan:
- Controller selalu set `created_by` saat create
- Seeder create user dulu sebelum create data lain
- Factory (jika ada) juga set `created_by`

### 2. Chicken-Egg Problem
Karena `created_by` NOT NULL dan reference ke `ms_users`, urutan seeding penting:
1. Create user dulu (admin)
2. Baru create permissions, roles, menus dengan `created_by` = admin user_id

### 3. Foreign Key Constraints
Semua foreign key menggunakan `RESTRICT`:
- `onUpdate('restrict')` - Tidak bisa update primary key jika ada reference
- `onDelete('restrict')` - Tidak bisa delete jika ada reference

### 4. Indexes untuk Performance
Indexes ditambahkan pada kolom yang sering di-query:
- `created_by`, `updated_by` - Untuk audit trail queries
- `is_active` - Untuk filter active records
- `parent_id`, `menu_order` - Untuk menu hierarchy

## 🧪 Testing Checklist

### Test Migration
- [ ] Fresh install: `php artisan migrate:fresh --seed`
- [ ] Verify semua tabel ter-create dengan benar
- [ ] Verify indexes ter-create
- [ ] Verify foreign keys ter-create
- [ ] Login dengan admin/password berhasil

### Test Existing Database
- [ ] Backup database dulu
- [ ] Run migration: `php artisan migrate`
- [ ] Verify tidak ada error
- [ ] Test CRUD operations di semua module
- [ ] Verify created_by ter-isi dengan benar

### Test Data Integrity
- [ ] Create user baru → `created_by` harus ter-isi
- [ ] Create role baru → `created_by` harus ter-isi
- [ ] Create permission baru → `created_by` harus ter-isi
- [ ] Create menu baru → `created_by` harus ter-isi
- [ ] Attach permission ke role → `created_by` harus ter-isi di pivot

## 📊 Perbandingan Schema

### Sebelum
```sql
created_by BIGINT UNSIGNED NULL
created_date TIMESTAMP NULL
menu_order INTEGER DEFAULT 0
-- Indexes minimal
```

### Sesudah
```sql
created_by BIGINT UNSIGNED NOT NULL
created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP
menu_order INT UNSIGNED DEFAULT 0
-- Indexes lengkap untuk performa
```

## 💡 Keuntungan Perubahan

1. **Data Integrity** - `created_by` NOT NULL memastikan audit trail lengkap
2. **Performance** - Indexes pada kolom yang sering di-query
3. **Consistency** - Semua tabel menggunakan `datetime` untuk timestamps
4. **Type Safety** - `menu_order` unsigned mencegah nilai negatif
5. **Foreign Keys** - Memastikan referential integrity

## 🔄 Rollback Plan

Jika ada masalah, rollback dengan:
```bash
php artisan migrate:rollback
```

Atau restore dari backup database.

## ✅ Kesimpulan

Schema sudah diupdate sesuai dengan spesifikasi:
- ✅ Semua field `created_by` menjadi NOT NULL
- ✅ Indexes ditambahkan untuk performa
- ✅ Foreign keys lengkap
- ✅ Tipe data konsisten
- ✅ Seeder updated untuk handle `created_by`
- ✅ Migration untuk fresh install dan existing database
