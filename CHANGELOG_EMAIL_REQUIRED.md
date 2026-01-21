# Perubahan Field Email Menjadi Wajib (Required) & Penggunaan unique_id di Routes

## Tanggal: 21 Januari 2026

### Ringkasan Perubahan
1. Field email pada tabel `ms_users` telah diubah dari opsional (nullable) menjadi wajib diisi (required) dengan validasi email unik.
2. Semua form edit dan delete sekarang menggunakan `unique_id` sebagai route parameter, bukan primary key (user_id, role_id, dll).

### File yang Diubah

#### 1. Migration
- **File**: `database/migrations/0001_01_01_000000_create_users_table.php`
  - Mengubah `$table->string('email', 150)->nullable()->unique()` menjadi `$table->string('email', 150)->unique()`

- **File Baru**: `database/migrations/2026_01_21_084137_update_ms_users_email_required.php`
  - Migration untuk mengubah struktur tabel yang sudah ada
  - Mengubah kolom email dari nullable menjadi required

#### 2. Request Validation (Semua Module)
- **File**: `app/Http/Requests/UserRequest.php`
  - Mengubah validasi email dari `nullable` menjadi `required`
  - Menggunakan `Rule::unique()` untuk validasi dengan ignore berdasarkan `user_id`
  - Menambahkan custom error messages dalam Bahasa Indonesia

- **File**: `app/Http/Requests/RoleRequest.php`
  - Menggunakan `Rule::unique()` untuk validasi role_code dengan ignore berdasarkan `role_id`

- **File**: `app/Http/Requests/PermissionRequest.php`
  - Menggunakan `Rule::unique()` untuk validasi permission_code dengan ignore berdasarkan `permission_id`

- **File**: `app/Http/Requests/MenuRequest.php`
  - Menggunakan `Rule::unique()` untuk validasi menu_code dengan ignore berdasarkan `menu_id`

#### 3. Views - User Module
- **File**: `resources/views/users/create.blade.php`
  - Menambahkan tanda `<span class="text-danger">*</span>` pada label Email
  - Menambahkan atribut `required` pada input email

- **File**: `resources/views/users/edit.blade.php`
  - Menambahkan tanda `<span class="text-danger">*</span>` pada label Email
  - Menambahkan atribut `required` pada input email
  - **PERBAIKAN**: Mengubah form action dari `$user->user_id` menjadi `$user->unique_id`

#### 4. Views - Role Module
- **File**: `resources/views/roles/edit.blade.php`
  - **PERBAIKAN**: Mengubah form action dari `$role->role_id` menjadi `$role->unique_id`

- **File**: `resources/views/roles/index.blade.php`
  - Sudah menggunakan `$role->unique_id` untuk edit dan delete (tidak ada perubahan)

#### 5. Views - Permission Module
- **File**: `resources/views/permissions/edit.blade.php`
  - **PERBAIKAN**: Mengubah form action dari `$permission->permission_id` menjadi `$permission->unique_id`

- **File**: `resources/views/permissions/index.blade.php`
  - Sudah menggunakan `$permission->unique_id` untuk edit dan delete (tidak ada perubahan)

#### 6. Views - Menu Module
- **File**: `resources/views/menus/edit.blade.php`
  - **PERBAIKAN**: Mengubah form action dari `$menu->menu_id` menjadi `$menu->unique_id`

- **File**: `resources/views/menus/index.blade.php`
  - Sudah menggunakan `$menu->unique_id` untuk edit dan delete (tidak ada perubahan)

### Fitur yang Ditambahkan

#### 1. Validasi Email Wajib (User Module)
   - Email tidak boleh kosong saat create atau update user
   - Validasi dilakukan di level request (server-side) dan form (client-side)

#### 2. Pengecekan Email Duplikat (User Module)
   - Sistem akan mengecek apakah email sudah terdaftar
   - Saat create: email harus unik di seluruh tabel
   - Saat update: email harus unik kecuali untuk user yang sedang diedit
   - Pesan error yang jelas: "Email sudah terdaftar. Silakan gunakan email lain."

#### 3. Validasi Format Email (User Module)
   - Memastikan format email valid (menggunakan validasi Laravel)
   - Pesan error: "Format email tidak valid."

#### 4. Konsistensi Penggunaan unique_id (Semua Module)
   - Semua route edit dan delete sekarang menggunakan `unique_id` sebagai parameter
   - Model sudah memiliki `getRouteKeyName()` yang mengembalikan `unique_id`
   - Validasi unique di Request menggunakan `Rule::unique()->ignore()` dengan primary key yang benar

### Cara Menjalankan Migration

Jika database sudah di-migrate sebelumnya, jalankan migration baru:

```bash
php artisan migrate
```

Jika ingin rollback:

```bash
php artisan migrate:rollback
```

### Catatan Penting

⚠️ **Sebelum menjalankan migration**, pastikan:
1. Semua data user yang ada sudah memiliki email
2. Tidak ada email yang duplikat di database
3. Backup database terlebih dahulu

Jika ada data user tanpa email, isi terlebih dahulu sebelum menjalankan migration.

### Perubahan Teknis

#### Route Model Binding
Semua model (User, Role, Permission, Menu) sudah menggunakan `getRouteKeyName()` yang mengembalikan `unique_id`. Ini berarti:
- Route parameter otomatis menggunakan `unique_id` untuk mencari record
- URL lebih aman karena tidak expose primary key
- Contoh URL: `/users/550e8400-e29b-41d4-a716-446655440000/edit` (bukan `/users/1/edit`)

#### Validasi Unique
Semua Request validation sekarang menggunakan `Illuminate\Validation\Rule` untuk validasi unique:
```php
Rule::unique('table_name', 'column')->ignore($model->primary_key, 'primary_key_column')
```

Ini memastikan validasi unique tetap bekerja dengan benar meskipun route menggunakan `unique_id`.
