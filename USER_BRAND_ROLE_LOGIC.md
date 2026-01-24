# User Brand & Role Logic - Cartesian Product

## Perubahan yang Dilakukan

### 1. Struktur Database
- Kolom `brand_id` di tabel `ms_user_roles` sudah ada (tidak perlu migration baru)
- User sekarang bisa memiliki multiple brands melalui kombinasi dengan roles
- Dealer tetap disimpan di tabel `ms_users` (tidak berubah)

### 2. Logic Perkalian Kartesian (Cartesian Product)

Ketika user memilih:
- **1 Role + 1 Brand** = 1 record di `ms_user_roles`
- **2 Roles + 1 Brand** = 2 records di `ms_user_roles`
- **2 Roles + 2 Brands** = 4 records di `ms_user_roles`
- **3 Roles + 2 Brands** = 6 records di `ms_user_roles`

Contoh:
```
Roles: [Admin, Manager]
Brands: [Toyota, Honda]

Data yang terinsert ke ms_user_roles:
1. user_id=1, role_id=1 (Admin), brand_id=1 (Toyota)
2. user_id=1, role_id=1 (Admin), brand_id=2 (Honda)
3. user_id=1, role_id=2 (Manager), brand_id=1 (Toyota)
4. user_id=1, role_id=2 (Manager), brand_id=2 (Honda)
```

### 3. File yang Diubah

#### Model
- `app/Models/User.php` - Tambah relasi `userRoles()` dan update `withPivot` untuk include `brand_id`
- `app/Models/UserRole.php` - Model baru untuk tabel `ms_user_roles`

#### Controller
- `app/Http/Controllers/UserController.php`
  - Method `store()`: Logic perkalian kartesian roles x brands
  - Method `update()`: Hapus semua user_roles lama, insert ulang dengan logic kartesian
  - Method `edit()`: Load userRoles dengan brand untuk tampilkan checkbox yang sudah dipilih

#### Request
- `app/Http/Requests/UserRequest.php` - Tambah validasi untuk `brands` array

#### View
- `resources/views/users/create.blade.php` - Ubah dropdown brand menjadi checkbox
- `resources/views/users/edit.blade.php` - Ubah dropdown brand menjadi checkbox, tampilkan brands yang sudah dipilih

### 4. Cara Penggunaan

1. Buka form Create/Edit User
2. Pilih satu atau lebih **Roles** (checkbox)
3. Pilih satu atau lebih **Brands** (checkbox)
4. Pilih **Dealer** (dropdown - tetap single select)
5. Save - sistem akan otomatis membuat kombinasi semua roles x brands

### 5. Catatan Penting

- Field `brand_id` di tabel `ms_users` tidak lagi digunakan (bisa dihapus nanti jika diperlukan)
- Setiap kombinasi role-brand akan memiliki `unique_id` sendiri
- Saat update user, semua kombinasi role-brand lama akan dihapus dan dibuat ulang
