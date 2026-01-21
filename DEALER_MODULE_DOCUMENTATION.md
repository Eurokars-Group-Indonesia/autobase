# Dealer Module Documentation

## Tanggal: 21 Januari 2026

## 📋 Ringkasan

Module Dealer telah berhasil dibuat dengan lengkap mengikuti struktur RBAC yang ada di project.

## 🗄️ Database Schema

### Tabel: ms_dealers

```sql
CREATE TABLE ms_dealers (
    dealer_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dealer_code VARCHAR(50) NOT NULL UNIQUE,
    dealer_name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE COMMENT 'UUIDV4, di gunakan untuk Get Data dari URL',
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_dealer_created_by (created_by),
    INDEX idx_dealer_updated_by (updated_by),
    INDEX idx_dealer_is_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES ms_users(user_id),
    FOREIGN KEY (updated_by) REFERENCES ms_users(user_id)
);
```

## 📁 File yang Dibuat

### 1. Migration
- `database/migrations/2026_01_21_101201_create_ms_dealers_table.php`

### 2. Model
- `app/Models/Dealer.php`
  - Primary key: `dealer_id`
  - Route key: `unique_id` (untuk URL)
  - Timestamps: `created_date`, `updated_date`
  - Auto-generate UUID saat create
  - Relationships: `creator()`, `updater()`

### 3. Controller
- `app/Http/Controllers/DealerController.php`
  - `index()` - List dealers dengan search dan pagination
  - `create()` - Form create dealer
  - `store()` - Save dealer baru
  - `edit()` - Form edit dealer
  - `update()` - Update dealer
  - `destroy()` - Soft delete (set is_active = '0')
  - Semua method dilindungi dengan permission check

### 4. Request Validation
- `app/Http/Requests/DealerRequest.php`
  - `dealer_code`: required, max:50, unique
  - `dealer_name`: required, max:150
  - `city`: nullable, max:100
  - `is_active`: hanya untuk update (0 atau 1)

### 5. Views
- `resources/views/dealers/index.blade.php` - List dealers
- `resources/views/dealers/create.blade.php` - Form create
- `resources/views/dealers/edit.blade.php` - Form edit
- Semua field memiliki `maxlength` sesuai schema
- Validasi error ditampilkan per field
- Status active/inactive hanya di form edit
- Search box di sebelah kanan

### 6. Routes
- `routes/web.php` - Ditambahkan routes untuk Dealer dengan middleware permission:
  - GET `/dealers` - dealers.view
  - GET `/dealers/create` - dealers.create
  - POST `/dealers` - dealers.create
  - GET `/dealers/{dealer}/edit` - dealers.edit
  - PUT/PATCH `/dealers/{dealer}` - dealers.edit
  - DELETE `/dealers/{dealer}` - dealers.delete

### 7. Seeder
- `database/seeders/DealerSeeder.php`
  - Create 4 permissions: dealers.view, dealers.create, dealers.edit, dealers.delete
  - Create menu: Dealers (icon: bi-shop, order: 60)
  - Attach permissions ke Admin role
  - Attach menu ke Admin role

## 🔐 Permissions

| Permission Code | Permission Name | Description |
|----------------|-----------------|-------------|
| dealers.view | View Dealers | Akses untuk melihat list dealers |
| dealers.create | Create Dealer | Akses untuk membuat dealer baru |
| dealers.edit | Edit Dealer | Akses untuk edit dealer |
| dealers.delete | Delete Dealer | Akses untuk delete dealer (soft delete) |

## 📱 Menu

| Menu Code | Menu Name | URL | Icon | Order |
|-----------|-----------|-----|------|-------|
| dealers | Dealers | /dealers | bi-shop | 60 |

## ✅ Fitur

1. **CRUD Lengkap**
   - Create, Read, Update, Delete (soft delete)
   - Semua operasi dilindungi dengan permission

2. **Search Functionality**
   - Search by dealer_code, dealer_name, city
   - Pagination 10 items per page
   - Search box di sebelah kanan

3. **Validation**
   - HTML maxlength di semua input field
   - Backend validation di DealerRequest
   - Unique validation untuk dealer_code
   - Required fields ditandai dengan asterisk merah

4. **Audit Trail**
   - `created_by` - User yang create (NOT NULL)
   - `created_date` - Tanggal create (auto)
   - `updated_by` - User yang update
   - `updated_date` - Tanggal update (auto)

5. **UUID untuk URL**
   - Menggunakan `unique_id` (UUID) di URL untuk keamanan
   - Primary key `dealer_id` tidak exposed di URL

6. **Soft Delete**
   - Delete hanya set `is_active = '0'`
   - Data tidak benar-benar dihapus dari database

7. **Status Management**
   - Default status: Active ('1')
   - Tidak ada pilihan status di form create (auto active)
   - Status bisa diubah di form edit

8. **No N+1 Query Problem**
   - Tidak ada relasi yang digunakan di view index
   - Tidak perlu eager loading

## 🚀 Cara Menggunakan

### 1. Jalankan Migration (Sudah Dijalankan)
```bash
php artisan migrate
```

### 2. Jalankan Seeder (Sudah Dijalankan)
```bash
php artisan db:seed --class=DealerSeeder
```

### 3. Akses Module
- Login sebagai admin
- Menu "Dealers" akan muncul di sidebar
- URL: http://your-domain/dealers

## 🔍 Testing Checklist

- [x] Migration berhasil dijalankan
- [x] Seeder berhasil dijalankan
- [x] Permissions ter-create
- [x] Menu ter-create
- [x] Permissions dan menu ter-attach ke Admin role
- [ ] Test create dealer baru
- [ ] Test edit dealer
- [ ] Test delete dealer
- [ ] Test search functionality
- [ ] Test validation (unique dealer_code)
- [ ] Test permission (login sebagai user non-admin)

## 📝 Catatan

1. **Dealer Code** harus unique
2. **Dealer Name** adalah field required
3. **City** adalah optional
4. Status default adalah Active, tidak perlu dipilih saat create
5. Semua field memiliki maxlength sesuai schema database
6. URL menggunakan UUID untuk keamanan (bukan ID)
7. Search box diletakkan di sebelah kanan table

## 🎯 Sesuai dengan Requirement

✅ Model - Dealer.php
✅ Migration - create_ms_dealers_table.php
✅ Controller - DealerController.php
✅ Request - DealerRequest.php
✅ Views - index.blade.php, create.blade.php, edit.blade.php
✅ Routes - web.php (dengan middleware permission)
✅ Permissions - dealers.view, dealers.create, dealers.edit, dealers.delete
✅ Menu - Dealers menu dengan icon bi-shop
✅ HTML maxlength di semua field
✅ Validasi di Request
✅ Tidak ada pilihan status di form create (default active)
✅ Status bisa diubah di form edit
✅ Schema sesuai dengan requirement
✅ Search box di sebelah kanan
✅ Tidak ada relasi = tidak perlu eager loading

## 📊 Perbandingan dengan Module Brand

| Aspek | Brand | Dealer |
|-------|-------|--------|
| Fields | 4 (code, name, group, country) | 3 (code, name, city) |
| Icon | bi-tag | bi-shop |
| Menu Order | 50 | 60 |
| Relasi | Tidak ada | Tidak ada |
| Eager Loading | Tidak perlu | Tidak perlu |

## ✅ Kesimpulan

Module Dealer telah berhasil dibuat lengkap dengan:
- ✅ CRUD lengkap dengan permission checks
- ✅ Search functionality di sebelah kanan
- ✅ Validasi HTML dan backend
- ✅ Soft delete
- ✅ UUID untuk URL security
- ✅ Audit trail lengkap
- ✅ Permissions dan menu terintegrasi dengan RBAC
- ✅ Tidak ada N+1 query problem (tidak ada relasi di view)
