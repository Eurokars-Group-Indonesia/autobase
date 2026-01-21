# Installation Guide - Transaction Header Import

## Quick Installation Steps

### 1. Install Laravel Excel Package
```bash
composer require maatwebsite/excel
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Run Seeder (Optional - untuk permission)
```bash
php artisan db:seed --class=TransactionImportPermissionSeeder
```

### 4. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Verification

### Test Import Feature
1. Login sebagai user dengan permission `transactions.import`
2. Navigate ke `/transactions`
3. Click tombol "Import Excel"
4. Download template
5. Isi template dengan data test
6. Upload dan import

### Expected Result
- File berhasil diupload
- Data tersimpan ke database `tx_header`
- Redirect ke transaction list dengan success message

## Files Created/Modified

### New Files:
- `app/Imports/TransactionHeaderImport.php` - Import class
- `resources/views/transactions/import.blade.php` - Import form view
- `database/migrations/2026_01_21_104523_add_account_company_to_tx_header_table.php` - Add account_company field
- `database/seeders/TransactionImportPermissionSeeder.php` - Permission seeder
- `TRANSACTION_IMPORT_DOCUMENTATION.md` - Full documentation

### Modified Files:
- `app/Http/Controllers/TransactionHeaderController.php` - Added import methods
- `app/Models/TransactionHeader.php` - Added account_company to fillable
- `routes/web.php` - Added import routes
- `resources/views/transactions/index.blade.php` - Added import button

## Troubleshooting

### Package Not Found
```bash
composer require maatwebsite/excel
```

### Permission Denied
Run seeder atau manual insert permission:
```sql
INSERT INTO ms_permission (permission_name, permission_key, description, is_active, created_by, updated_by)
VALUES ('Import Transactions', 'transactions.import', 'Import transaction headers from Excel file', '1', 1, 1);
```

### Route Not Found
```bash
php artisan route:clear
php artisan config:clear
```

## Next Steps

Lihat `TRANSACTION_IMPORT_DOCUMENTATION.md` untuk:
- Detail penggunaan
- Format Excel template
- Error handling
- Testing guide
