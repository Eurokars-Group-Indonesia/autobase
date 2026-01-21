# Perubahan Field Email Menjadi Wajib (Required)

## Tanggal: 21 Januari 2026

### Ringkasan Perubahan
Field email pada tabel `ms_users` telah diubah dari opsional (nullable) menjadi wajib diisi (required) dengan validasi email unik.

### File yang Diubah

#### 1. Migration
- **File**: `database/migrations/0001_01_01_000000_create_users_table.php`
  - Mengubah `$table->string('email', 150)->nullable()->unique()` menjadi `$table->string('email', 150)->unique()`

- **File Baru**: `database/migrations/2026_01_21_084137_update_ms_users_email_required.php`
  - Migration untuk mengubah struktur tabel yang sudah ada
  - Mengubah kolom email dari nullable menjadi required

#### 2. Request Validation
- **File**: `app/Http/Requests/UserRequest.php`
  - Mengubah validasi email dari `nullable` menjadi `required`
  - Menambahkan custom error messages dalam Bahasa Indonesia:
    - `email.required`: "Email wajib diisi."
    - `email.email`: "Format email tidak valid."
    - `email.unique`: "Email sudah terdaftar. Silakan gunakan email lain."
    - `email.max`: "Email maksimal 150 karakter."

#### 3. View - Create User
- **File**: `resources/views/users/create.blade.php`
  - Menambahkan tanda `<span class="text-danger">*</span>` pada label Email
  - Menambahkan atribut `required` pada input email

#### 4. View - Edit User
- **File**: `resources/views/users/edit.blade.php`
  - Menambahkan tanda `<span class="text-danger">*</span>` pada label Email
  - Menambahkan atribut `required` pada input email

### Fitur yang Ditambahkan

1. **Validasi Email Wajib**
   - Email tidak boleh kosong saat create atau update user
   - Validasi dilakukan di level request (server-side) dan form (client-side)

2. **Pengecekan Email Duplikat**
   - Sistem akan mengecek apakah email sudah terdaftar
   - Saat create: email harus unik di seluruh tabel
   - Saat update: email harus unik kecuali untuk user yang sedang diedit
   - Pesan error yang jelas: "Email sudah terdaftar. Silakan gunakan email lain."

3. **Validasi Format Email**
   - Memastikan format email valid (menggunakan validasi Laravel)
   - Pesan error: "Format email tidak valid."

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
