# Transaction Header Import - Summary

## ✅ Instalasi Selesai

Fitur import Excel untuk Transaction Headers telah berhasil dibuat dan diinstall.

## 📁 File yang Dibuat

1. **app/Imports/TransactionHeaderImport.php** - Import class dengan updateOrCreate
2. **resources/views/transactions/import.blade.php** - Form upload Excel
3. **database/migrations/2026_01_21_104523_add_account_company_to_tx_header_table.php** - Migration field baru
4. **database/seeders/TransactionImportPermissionSeeder.php** - Seeder permission

## 📝 File yang Dimodifikasi

1. **app/Http/Controllers/TransactionHeaderController.php** - Tambah 3 method: showImport(), import(), downloadTemplate()
2. **app/Models/TransactionHeader.php** - Tambah account_company ke fillable
3. **routes/web.php** - Tambah 3 routes untuk import
4. **resources/views/transactions/index.blade.php** - Tambah tombol "Import Excel"

## 🎯 Cara Menggunakan

### 1. Akses Halaman Import
- Login sebagai user dengan permission `transactions.import`
- Buka `/transactions`
- Klik tombol **"Import Excel"**

### 2. Download Template
- Klik **"Download Template"** untuk mendapatkan file CSV dengan header yang benar

### 3. Isi Data Excel
Template memiliki 26 kolom:
- **WIPNO** (Required) - Work In Progress Number
- Account, CustName, Add1-Add5, Dept, InvNo, InvDate, MAGICH, DocType, ExchangeRate, RegNo, Chassis, Mileage, CurrCode, GrossValue, NetValue, CustDisc, SvcCode, RegDate, Description, EngineNo, AcctCompany

### 4. Upload & Import
- Pilih **Brand** (required)
- Upload file Excel (.xlsx, .xls, .csv)
- Klik **"Import"**

## 🔄 Update or Create Logic

Import menggunakan `updateOrCreate` berdasarkan:
- **wip_no** + **brand_id**

**Behavior:**
- Jika kombinasi sudah ada → **UPDATE** data existing
- Jika kombinasi belum ada → **CREATE** data baru

## 📊 Mapping Kolom Excel ke Database

| Excel Header | Database Field | Type | Required |
|--------------|----------------|------|----------|
| WIPNO | wip_no | string | ✅ Yes |
| Account | account_code | string | No |
| CustName | customer_name | string | No |
| Add1 | address_1 | text | No |
| Add2 | address_2 | text | No |
| Add3 | address_3 | text | No |
| Add4 | address_4 | text | No |
| Add5 | address_5 | text | No |
| Dept | department | string | No |
| InvNo | invoice_no | string | No |
| InvDate | invoice_date | date | No |
| MAGICH | vehicle_id | integer | No |
| DocType | document_type | enum(I,C) | No |
| ExchangeRate | exchange_rate | decimal | No |
| RegNo | registration_no | string | No |
| Chassis | chassis | string | No |
| Mileage | mileage | integer | No |
| CurrCode | currency_code | string | No |
| GrossValue | gross_value | decimal | No |
| NetValue | net_value | decimal | No |
| CustDisc | customer_discount | string | No |
| SvcCode | service_code | string | No |
| RegDate | registration_date | date | No |
| Description | description | string | No |
| EngineNo | engine_no | string | No |
| AcctCompany | account_company | string | No |

## 🔐 Permission

Permission `transactions.import` sudah dibuat dan di-assign ke role Super Admin (role_id = 1).

Untuk assign ke role lain, gunakan UI Permission Management atau manual:
```sql
INSERT INTO ms_role_permissions (role_id, permission_id, created_by, created_date, unique_id, is_active)
SELECT 2, permission_id, 1, NOW(), UUID(), '1'
FROM ms_permissions WHERE permission_code = 'transactions.import';
```

## 📦 Package Terinstall

- **maatwebsite/excel** v3.1.67
- Dependencies: phpoffice/phpspreadsheet, markbaker/matrix, dll

## 🚀 Status

✅ Package installed
✅ Migration run
✅ Permission seeded
✅ Cache cleared
✅ Ready to use!

## 📚 Dokumentasi Lengkap

Lihat file berikut untuk detail lebih lanjut:
- **TRANSACTION_IMPORT_DOCUMENTATION.md** - Dokumentasi lengkap
- **INSTALL_TRANSACTION_IMPORT.md** - Panduan instalasi

## 🧪 Testing

Test dengan data sample:
1. Download template
2. Isi 2-3 row dengan data test
3. Upload dan import
4. Cek database table `tx_header`
5. Import ulang dengan WIPNO yang sama (test update)

## ⚠️ Notes

- Max file size: 10MB
- Format date: YYYY-MM-DD atau Excel date
- Empty rows akan di-skip otomatis
- Error di satu row tidak akan stop import keseluruhan
- `created_by` dan `updated_by` otomatis diisi dengan user login
- `is_active` otomatis di-set ke '1'
