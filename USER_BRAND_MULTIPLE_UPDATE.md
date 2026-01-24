# Update User CRUD - Multiple Brands Support

## Perubahan yang Dilakukan

### 1. Database Schema
- **Tabel Baru**: `ms_user_brand` (many-to-many relationship)
- **Struktur**:
  - `user_brand_id` (PK)
  - `user_id` (FK ke ms_users)
  - `brand_id` (FK ke ms_brand)
  - `created_by`, `created_date`
  - `updated_by`, `updated_date`
  - `unique_id` (UUID)
  - `is_active` (enum '0','1')

### 2. Model
- **Model Baru**: `UserBrand.php`
- **Update Model User**:
  - Tambah relationship `brands()` (many-to-many)
  - Tambah relationship `userBrands()` (hasMany)
  - Hapus field `brand_id` dari fillable (tidak lagi digunakan)

### 3. Controller (UserController.php)
- **Method `index()`**: Update eager loading dari `brand` ke `brands`
- **Method `store()`**: 
  - Hapus `brand_id` dari data
  - Tambah logic untuk attach multiple brands
- **Method `edit()`**: 
  - Load relationship `brands`
  - Kirim `$userBrands` array ke view
- **Method `update()`**: 
  - Hapus `brand_id` dari data
  - Sync brands (detach lalu attach ulang)

### 4. Request Validation (UserRequest.php)
- Tambah validasi untuk `brands` array
- Tambah validasi untuk `dealer_id`
- Hapus validasi `brand_id` (tidak lagi digunakan)

### 5. Views
- **create.blade.php**: Ubah dropdown brand menjadi checkbox multiple brands
- **edit.blade.php**: Ubah dropdown brand menjadi checkbox multiple brands dengan pre-checked
- **index.blade.php**: Tampilkan multiple brands sebagai badges

## Cara Penggunaan

### Create User
1. Pilih brands dengan checkbox (bisa lebih dari 1)
2. Pilih dealer (optional)
3. Pilih roles dengan checkbox
4. Submit form

### Edit User
1. Brands yang sudah dipilih akan ter-centang otomatis
2. Bisa menambah/mengurangi brands
3. Update akan sync brands (hapus yang tidak dicentang, tambah yang baru)

### Display
- Di halaman index, brands ditampilkan sebagai badges berwarna info
- Jika user tidak punya brand, tampilkan "-"

## Migration
```bash
php artisan migrate
```

Migration file: `2026_01_24_081519_create_ms_user_brand_table.php`

## Catatan Penting
- Field `brand_id` di tabel `ms_users` masih ada tapi tidak digunakan lagi
- Jika ingin menghapus field `brand_id` dari `ms_users`, buat migration baru
- Relationship lama `brand()` di model User masih ada untuk backward compatibility
