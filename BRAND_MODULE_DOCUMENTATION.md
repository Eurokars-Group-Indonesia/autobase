# Brand Module Documentation

## Tanggal: 21 Januari 2026

## 📋 Ringkasan

Module Brand telah berhasil dibuat dengan lengkap mengikuti struktur RBAC yang ada di project.

## 🗄️ Database Schema

### Tabel: ms_brand

```sql
CREATE TABLE ms_brand (
    brand_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    brand_code VARCHAR(50) NOT NULL UNIQUE,
    brand_name VARCHAR(100) NOT NULL,
    brand_group VARCHAR(100) NULL,
    country_origin VARCHAR(100) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE COMMENT 'UUIDV4, di gunakan untuk Get Data dari URL',
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_brand_created_by (created_by),
    INDEX idx_brand_updated_by (updated_by),
    INDEX idx_brand_is_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES ms_users(user_id),
    FOREIGN KEY (updated_by) REFERENCES ms_users(user_id)
);
```

## 📁 File yang Dibuat

### 1. Migration
- `database/migrations/2026_01_21_094850_create_ms_brand_table.php`

### 2. Model
- `app/Models/Brand.php`
  - Primary key: `brand_id`
  - Route key: `unique_id` (untuk URL)
  - Timestamps: `created_date`, `updated_date`
  - Auto-generate UUID saat create
  - Relationships: `creator()`, `updater()`

### 3. Controller
- `app/Http/Controllers/BrandController.php`
  - `index()` - List brands dengan search dan pagination
  - `create()` - Form create brand
  - `store()` - Save brand baru
  - `edit()` - Form edit brand
  - `update()` - Update brand
  - `destroy()` - Soft delete (set is_active = '0')
  - Semua method dilindungi dengan permission check

### 4. Request Validation
- `app/Http/Requests/BrandRequest.php`
  - `brand_code`: required, max:50, unique
  - `brand_name`: required, max:100
  - `brand_group`: nullable, max:100
  - `country_origin`: nullable, max:100
  - `is_active`: hanya untuk update (0 atau 1)

### 5. Views
- `resources/views/brands/index.blade.php` - List brands
- `resources/views/brands/create.blade.php` - Form create
- `resources/views/brands/edit.blade.php` - Form edit
- Semua field memiliki `maxlength` sesuai schema
- Validasi error ditampilkan per field
- Status active/inactive hanya di form edit

### 6. Routes
- `routes/web.php` - Ditambahkan routes untuk Brand dengan middleware permission:
  - GET `/brands` - brands.view
  - GET `/brands/create` - brands.create
  - POST `/brands` - brands.create
  - GET `/brands/{brand}/edit` - brands.edit
  - PUT/PATCH `/brands/{brand}` - brands.edit
  - DELETE `/brands/{brand}` - brands.delete

### 7. Seeder
- `database/seeders/BrandSeeder.php`
  - Create 4 permissions: brands.view, brands.create, brands.edit, brands.delete
  - Create menu: Brands (icon: bi-tag, order: 50)
  - Attach permissions ke Admin role
  - Attach menu ke Admin role

## 🔐 Permissions

| Permission Code | Permission Name | Description |
|----------------|-----------------|-------------|
| brands.view | View Brands | Akses untuk melihat list brands |
| brands.create | Create Brand | Akses untuk membuat brand baru |
| brands.edit | Edit Brand | Akses untuk edit brand |
| brands.delete | Delete Brand | Akses untuk delete brand (soft delete) |

## 📱 Menu

| Menu Code | Menu Name | URL | Icon | Order |
|-----------|-----------|-----|------|-------|
| brands | Brands | /brands | bi-tag | 50 |

## ✅ Fitur

1. **CRUD Lengkap**
   - Create, Read, Update, Delete (soft delete)
   - Semua operasi dilindungi dengan permission

2. **Search Functionality**
   - Search by brand_code, brand_name, brand_group, country_origin
   - Pagination 10 items per page

3. **Validation**
   - HTML maxlength di semua input field
   - Backend validation di BrandRequest
   - Unique validation untuk brand_code
   - Required fields ditandai dengan asterisk merah

4. **Audit Trail**
   - `created_by` - User yang create (NOT NULL)
   - `created_date` - Tanggal create (auto)
   - `updated_by` - User yang update
   - `updated_date` - Tanggal update (auto)

5. **UUID untuk URL**
   - Menggunakan `unique_id` (UUID) di URL untuk keamanan
   - Primary key `brand_id` tidak exposed di URL

6. **Soft Delete**
   - Delete hanya set `is_active = '0'`
   - Data tidak benar-benar dihapus dari database

7. **Status Management**
   - Default status: Active ('1')
   - Tidak ada pilihan status di form create (auto active)
   - Status bisa diubah di form edit

## 🚀 Cara Menggunakan

### 1. Jalankan Migration (Sudah Dijalankan)
```bash
php artisan migrate
```

### 2. Jalankan Seeder (Sudah Dijalankan)
```bash
php artisan db:seed --class=BrandSeeder
```

### 3. Akses Module
- Login sebagai admin
- Menu "Brands" akan muncul di sidebar
- URL: http://your-domain/brands

## 🔍 Testing Checklist

- [x] Migration berhasil dijalankan
- [x] Seeder berhasil dijalankan
- [x] Permissions ter-create
- [x] Menu ter-create
- [x] Permissions dan menu ter-attach ke Admin role
- [ ] Test create brand baru
- [ ] Test edit brand
- [ ] Test delete brand
- [ ] Test search functionality
- [ ] Test validation (unique brand_code)
- [ ] Test permission (login sebagai user non-admin)

## 📝 Catatan

1. **Brand Code** harus unique
2. **Brand Name** adalah field required
3. **Brand Group** dan **Country Origin** adalah optional
4. Status default adalah Active, tidak perlu dipilih saat create
5. Semua field memiliki maxlength sesuai schema database
6. URL menggunakan UUID untuk keamanan (bukan ID)

## 🎯 Sesuai dengan Requirement

✅ Model - Brand.php
✅ Migration - create_ms_brand_table.php
✅ Controller - BrandController.php
✅ Request - BrandRequest.php
✅ Views - index.blade.php, create.blade.php, edit.blade.php
✅ Routes - web.php (dengan middleware permission)
✅ Permissions - brands.view, brands.create, brands.edit, brands.delete
✅ Menu - Brands menu dengan icon bi-tag
✅ HTML maxlength di semua field
✅ Validasi di Request
✅ Tidak ada pilihan status di form create (default active)
✅ Status bisa diubah di form edit
✅ Schema sesuai dengan requirement
