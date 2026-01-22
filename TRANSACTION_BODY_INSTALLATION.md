# Transaction Body Module - Installation Guide

## 📦 File yang Dibuat

### 1. Migration
- `database/migrations/2026_01_22_100000_create_tx_body_table.php`

### 2. Model
- `app/Models/TransactionBody.php`

### 3. Controller
- `app/Http/Controllers/TransactionBodyController.php`

### 4. View
- `resources/views/transaction-body/index.blade.php`

### 5. Seeder (Optional)
- `database/seeders/TransactionBodySeeder.php`

### 6. Routes
- Updated: `routes/web.php`

### 7. Documentation
- `TRANSACTION_BODY_MODULE_DOCUMENTATION.md`
- `TRANSACTION_BODY_INSTALLATION.md` (file ini)

## 🚀 Langkah Instalasi

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. (Optional) Jalankan Seeder untuk Testing
```bash
php artisan db:seed --class=TransactionBodySeeder
```

### 3. Setup Permission
Tambahkan permission baru ke database:

```sql
INSERT INTO ms_permission (permission_name, permission_description, created_by, unique_id, is_active) 
VALUES ('transaction-body.view', 'View Transaction Body', 1, UUID(), '1');
```

### 4. Assign Permission ke Role
Assign permission `transaction-body.view` ke role yang sesuai melalui UI atau database:

```sql
-- Contoh: Assign ke role dengan role_id = 1
INSERT INTO ms_role_permission (role_id, permission_id, created_by, unique_id, is_active)
SELECT 1, permission_id, 1, UUID(), '1'
FROM ms_permission
WHERE permission_name = 'transaction-body.view';
```

### 5. Tambahkan Menu (Optional)
Jika ingin menambahkan menu di sidebar, tambahkan ke tabel `ms_menu`:

```sql
INSERT INTO ms_menu (menu_name, menu_url, menu_icon, menu_order, parent_id, created_by, unique_id, is_active)
VALUES ('Transaction Body', '/transaction-body', 'bi-list-ul', 50, NULL, 1, UUID(), '1');
```

Kemudian assign menu ke permission:

```sql
INSERT INTO ms_menu_permission (menu_id, permission_id, created_by, unique_id, is_active)
SELECT 
    (SELECT menu_id FROM ms_menu WHERE menu_url = '/transaction-body'),
    (SELECT permission_id FROM ms_permission WHERE permission_name = 'transaction-body.view'),
    1, UUID(), '1';
```

### 6. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🔍 Testing

### 1. Akses Module
Buka browser dan akses: `http://your-domain/transaction-body`

### 2. Test Search
- Coba search dengan Part No, Invoice No, WIP No, atau Description
- Test date range filter
- Test pagination (10, 25, 50, 100 per page)

### 3. Verify Cache
- Lakukan search yang sama 2x, yang kedua harusnya lebih cepat (dari cache)
- Cache akan expire setelah 1 jam

## 📋 Checklist

- [ ] Migration berhasil dijalankan
- [ ] Tabel `tx_body` sudah ada di database
- [ ] Permission `transaction-body.view` sudah ditambahkan
- [ ] Permission sudah di-assign ke role yang sesuai
- [ ] User bisa akses `/transaction-body`
- [ ] Search berfungsi dengan baik
- [ ] Date picker berfungsi dengan baik
- [ ] Pagination berfungsi dengan baik
- [ ] Cache berfungsi dengan baik

## 🔧 Troubleshooting

### Error: Permission denied
**Solusi**: Pastikan user sudah memiliki permission `transaction-body.view`

### Error: Table not found
**Solusi**: Jalankan migration dengan `php artisan migrate`

### Error: View not found
**Solusi**: 
1. Pastikan folder `resources/views/transaction-body/` sudah ada
2. Jalankan `php artisan view:clear`

### Date picker tidak muncul
**Solusi**: Pastikan koneksi internet aktif (Flatpickr menggunakan CDN)

### Cache tidak berfungsi
**Solusi**: 
1. Cek konfigurasi cache di `.env` (CACHE_DRIVER)
2. Pastikan folder `storage/framework/cache` writable
3. Jalankan `php artisan cache:clear`

## 📝 Notes

1. Module ini hanya memiliki fitur READ (view data)
2. Tidak ada Create, Update, atau Delete
3. Cache otomatis expire setelah 1 jam
4. Search menggunakan LIKE pattern untuk optimasi index
5. Date filter menggunakan field `date_decard`

## 🎯 Next Steps

Setelah instalasi selesai, Anda bisa:
1. Menambahkan data transaction body melalui import atau manual insert
2. Customize tampilan sesuai kebutuhan
3. Menambahkan fitur export jika diperlukan
4. Menambahkan relasi dengan Transaction Header
5. Menambahkan detail view untuk setiap record
