# Transaction Header Import Documentation

## Overview
Fitur import Excel untuk Transaction Headers memungkinkan user untuk mengupload data transaksi dalam format Excel (.xlsx, .xls, .csv) dan secara otomatis menyimpannya ke database menggunakan metode `updateOrCreate`.

## Installation

### 1. Install Laravel Excel Package
```bash
composer require maatwebsite/excel
```

### 2. Publish Configuration (Optional)
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

## Files Created

### 1. Import Class
**File:** `app/Imports/TransactionHeaderImport.php`

Class ini menghandle proses import dengan fitur:
- `ToModel`: Mengkonversi setiap row menjadi model
- `WithHeadingRow`: Menggunakan row pertama sebagai header
- `WithValidation`: Validasi data sebelum import
- `SkipsEmptyRows`: Skip row yang kosong
- `SkipsOnFailure`: Melanjutkan import meskipun ada error

**Key Features:**
- Menggunakan `updateOrCreate` berdasarkan `wip_no` dan `brand_id`
- Parse date otomatis (Excel serial number dan string date)
- Handle null values
- Auto-set `created_by`, `updated_by`, dan `is_active`

### 2. Controller Methods
**File:** `app/Http/Controllers/TransactionHeaderController.php`

**Methods:**
- `showImport()`: Menampilkan form upload
- `import()`: Proses import file Excel
- `downloadTemplate()`: Download template CSV

### 3. View
**File:** `resources/views/transactions/import.blade.php`

Form upload dengan:
- Pilihan Brand (required)
- Upload file Excel
- Download template
- Instruksi dan daftar kolom

### 4. Routes
**File:** `routes/web.php`

```php
Route::middleware('permission:transactions.import')->group(function () {
    Route::get('/transactions/import', [TransactionHeaderController::class, 'showImport'])
        ->name('transactions.import');
    Route::post('/transactions/import', [TransactionHeaderController::class, 'import'])
        ->name('transactions.import.process');
    Route::get('/transactions/import/template', [TransactionHeaderController::class, 'downloadTemplate'])
        ->name('transactions.import.template');
});
```

## Excel Template

### Column Headers (Case Insensitive)
1. **WIPNO** → wip_no (Required)
2. **Account** → account_code
3. **CustName** → customer_name
4. **Add1** → address_1
5. **Add2** → address_2
6. **Add3** → address_3
7. **Add4** → address_4
8. **Add5** → address_5
9. **Dept** → department
10. **InvNo** → invoice_no
11. **InvDate** → invoice_date
12. **MAGICH** → vehicle_id
13. **DocType** → document_type
14. **ExchangeRate** → exchange_rate
15. **RegNo** → registration_no
16. **Chassis** → chassis
17. **Mileage** → mileage
18. **CurrCode** → currency_code
19. **GrossValue** → gross_value
20. **NetValue** → net_value
21. **CustDisc** → customer_discount
22. **SvcCode** → service_code
23. **RegDate** → registration_date
24. **Description** → description
25. **EngineNo** → engine_no
26. **AcctCompany** → account_company

### Example Data
```csv
WIPNO,Account,CustName,Add1,Add2,Add3,Add4,Add5,Dept,InvNo,InvDate,MAGICH,DocType,ExchangeRate,RegNo,Chassis,Mileage,CurrCode,GrossValue,NetValue,CustDisc,SvcCode,RegDate,Description,EngineNo,AcctCompany
WIP001,ACC001,John Doe,Jl. Sudirman No. 1,Jakarta,Indonesia,,,SALES,INV001,2026-01-15,VEH001,I,1.00,B1234XYZ,CH123456,50000,IDR,10000000,9500000,500000,SVC001,2020-01-01,Service Description,ENG123,COMP001
```

## Usage

### 1. Access Import Page
- Navigate to Transaction Headers list
- Click "Import Excel" button (requires `transactions.import` permission)

### 2. Download Template
- Click "Download Template" button
- Template akan berisi header kolom yang benar

### 3. Fill Excel Data
- Isi data sesuai dengan kolom yang tersedia
- WIPNO adalah kolom wajib
- Format tanggal: YYYY-MM-DD atau Excel date

### 4. Upload File
- Pilih Brand
- Upload file Excel (.xlsx, .xls, .csv)
- Max file size: 10MB
- Click "Import"

### 5. Result
- **Success**: Redirect ke transaction list dengan pesan sukses
- **Warning**: Import selesai tapi ada error di beberapa row
- **Error**: Import gagal total

## Update or Create Logic

Import menggunakan `updateOrCreate` dengan key:
- `wip_no`
- `brand_id`

**Behavior:**
- Jika kombinasi `wip_no` + `brand_id` sudah ada → **UPDATE**
- Jika kombinasi `wip_no` + `brand_id` belum ada → **CREATE**

## Date Handling

Import class dapat handle berbagai format date:
1. **Excel Serial Number**: Otomatis dikonversi ke Carbon date
2. **String Date**: Parse menggunakan Carbon (support berbagai format)
3. **Empty/Null**: Set sebagai null

## Validation

### File Validation
- Required
- Format: .xlsx, .xls, .csv
- Max size: 10MB

### Data Validation
- `wipno`: Required
- `invno`: Nullable
- `invdate`: Nullable

## Error Handling

### Import Failures
Jika ada row yang gagal:
- Import tetap dilanjutkan untuk row lainnya
- Error message ditampilkan dengan nomor row
- Format: "Row X: error message"

### Exception Handling
Jika terjadi exception:
- Import dihentikan
- Error message ditampilkan
- User diarahkan kembali ke form import

## Permissions

Tambahkan permission baru:
```sql
INSERT INTO ms_permission (permission_name, permission_key, description, is_active, created_by, updated_by)
VALUES ('Import Transactions', 'transactions.import', 'Import transaction headers from Excel', '1', 1, 1);
```

Assign ke role yang sesuai melalui UI atau database.

## Testing

### Test Import
1. Download template
2. Isi dengan data test:
   - Row 1: Data valid lengkap
   - Row 2: Data minimal (hanya WIPNO)
   - Row 3: Data dengan tanggal Excel serial
   - Row 4: Data dengan WIPNO yang sudah ada (test update)

### Expected Results
- Row 1-3: Created successfully
- Row 4: Updated existing record
- Total: 3 records in database (1 updated, 2 new)

## Troubleshooting

### Issue: "Class 'Maatwebsite\Excel\Facades\Excel' not found"
**Solution:** Install Laravel Excel package
```bash
composer require maatwebsite/excel
```

### Issue: "Date not parsing correctly"
**Solution:** Check date format in Excel. Use YYYY-MM-DD or Excel date format.

### Issue: "Permission denied"
**Solution:** Ensure user has `transactions.import` permission.

### Issue: "Brand not found"
**Solution:** Ensure Brand ID exists in ms_brand table and is active.

## Notes

1. Import process menggunakan `updateOrCreate`, jadi data existing akan di-update
2. Semua field kecuali WIPNO adalah optional
3. Brand harus dipilih sebelum upload
4. File size maksimal 10MB
5. Import akan skip empty rows otomatis
6. Date format flexible (Excel serial atau string)
7. Numeric values akan otomatis di-cast sesuai tipe data
8. `created_by` dan `updated_by` otomatis diisi dengan user yang login
9. `is_active` otomatis di-set ke '1'

## Future Enhancements

1. **Batch Processing**: Untuk file besar, gunakan queue
2. **Progress Bar**: Real-time progress indicator
3. **Preview**: Preview data sebelum import
4. **Export**: Export existing data ke Excel
5. **Validation Rules**: Tambah validasi lebih detail
6. **Duplicate Check**: Warning untuk duplicate WIPNO
7. **Import History**: Log semua import activity
8. **Rollback**: Kemampuan untuk rollback import
