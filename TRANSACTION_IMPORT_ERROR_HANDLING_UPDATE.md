# Transaction Import Error Handling - Update Documentation

## Tanggal: 22 Januari 2026

## 📋 Perubahan

Sistem import Transaction Header telah diperbaiki untuk menampilkan error detail per baris dengan informasi yang lebih lengkap.

## 🔧 Fitur Baru

### 1. Error Tracking Per Baris
- Setiap baris yang error akan dicatat dengan detail:
  - **Row Number**: Nomor baris yang error (termasuk header)
  - **Field**: Field/kolom yang bermasalah
  - **Value**: Nilai yang menyebabkan error
  - **Error Message**: Pesan error yang jelas

### 2. Validasi yang Ditambahkan

#### Required Fields:
- **WIPNO**: Wajib diisi, tidak boleh kosong
- **InvDate**: Wajib diisi, harus format tanggal yang valid
- **MAGICH** (Vehicle ID): Wajib diisi, harus angka
- **InvNo** (Invoice Number): Wajib diisi, harus angka
- **Mileage**: Wajib diisi, harus angka
- **DocType**: Wajib diisi, harus 'I' (Invoice) atau 'C' (Credit Note)
- **CurrCode**: Wajib diisi, maksimal 3 karakter

#### Optional Fields:
- ExchangeRate: Jika diisi harus angka
- GrossValue: Jika diisi harus angka
- NetValue: Jika diisi harus angka
- RegDate: Jika diisi harus format tanggal yang valid

### 3. Error Display

Error akan ditampilkan dalam format yang mudah dibaca:

```
Row 5
Field: MAGICH
Value: ABC123
Error: Vehicle ID (MAGICH) is required and must be a valid number
```

### 4. Success Counter

Setelah import, sistem akan menampilkan:
- Jumlah record yang berhasil diimport
- Jumlah record yang gagal
- Detail error untuk setiap baris yang gagal

### 5. Logging

Semua proses import akan di-log ke Laravel log file:
- Info: Row yang berhasil diimport
- Error: Row yang gagal dengan detail error dan stack trace

## 📁 File yang Diubah

### 1. app/Imports/TransactionHeaderImport.php
**Perubahan:**
- Tambah property `$errors` dan `$successCount`
- Tambah method `getErrors()` dan `getSuccessCount()`
- Tambah validasi detail di method `model()`
- Tambah try-catch untuk menangkap semua error
- Tambah logging untuk debugging
- Implement `WithBatchInserts` dan `WithChunkReading` untuk performa

**Validasi yang ditambahkan:**
```php
// Required field validation
if (empty($row['wipno'])) {
    $this->errors[] = [
        'row' => $this->currentRow,
        'field' => 'WIPNO',
        'value' => $row['wipno'] ?? 'empty',
        'error' => 'WIPNO is required and cannot be empty'
    ];
    return null;
}
```

### 2. app/Http/Controllers/TransactionHeaderController.php
**Perubahan:**
- Update method `import()` untuk mengambil custom errors
- Combine validation failures dengan custom errors
- Tampilkan success count
- Tambah logging untuk debugging

**Error handling:**
```php
// Get custom errors from import class
$customErrors = $import->getErrors();
$successCount = $import->getSuccessCount();

// Get validation failures
$failures = $import->failures();

// Combine all errors
$allErrors = [];
foreach ($customErrors as $error) {
    $allErrors[] = [
        'row' => $error['row'],
        'field' => $error['field'],
        'value' => $error['value'],
        'error' => $error['error']
    ];
}
```

### 3. resources/views/transactions/import.blade.php
**Perubahan:**
- Update tampilan error dengan format yang lebih jelas
- Tampilkan success count
- Perbaiki styling error message
- Tampilkan field, value, dan error message secara terpisah

**Error display:**
```blade
<div class="error-item">
    <div class="error-row-number">
        <i class="bi bi-arrow-right-circle-fill me-1"></i>Row {{ $error['row'] }}
    </div>
    <div class="error-message">
        <strong>Field:</strong> {{ $error['field'] }}<br>
        <strong>Value:</strong> <code>{{ $error['value'] }}</code><br>
        <strong>Error:</strong> {{ $error['error'] }}
    </div>
</div>
```

## 🎯 Cara Menggunakan

### 1. Import File Excel
1. Pilih Brand
2. Upload file Excel
3. Klik Import

### 2. Jika Ada Error
Sistem akan menampilkan:
- Alert warning dengan jumlah success dan error
- List detail error per baris:
  - Nomor baris
  - Field yang bermasalah
  - Nilai yang menyebabkan error
  - Pesan error yang jelas

### 3. Perbaiki Data
Berdasarkan error yang ditampilkan:
1. Buka file Excel
2. Cari baris yang error (sesuai nomor baris)
3. Perbaiki field yang bermasalah
4. Upload ulang file

### 4. Cek Log (Optional)
Untuk debugging lebih detail, cek file log:
```
storage/logs/laravel.log
```

Log akan berisi:
- Info: "Processing row X" dengan data row
- Info: "Row X imported successfully" dengan header_id
- Error: "Error importing row X" dengan error message dan stack trace

## 📊 Contoh Error Messages

### 1. WIPNO Required
```
Row 5
Field: WIPNO
Value: empty
Error: WIPNO is required and cannot be empty
```

### 2. Invalid Date
```
Row 10
Field: InvDate
Value: 2026-13-45
Error: Invoice Date is required and must be a valid date
```

### 3. Invalid Number
```
Row 15
Field: MAGICH
Value: ABC123
Error: Vehicle ID (MAGICH) is required and must be a valid number
```

### 4. Invalid Document Type
```
Row 20
Field: DocType
Value: X
Error: Document Type must be either I (Invoice) or C (Credit Note)
```

### 5. Invalid Currency Code
```
Row 25
Field: CurrCode
Value: USDD
Error: Currency Code is required and must be 3 characters or less
```

## 🔍 Debugging

### Jika Import Sukses Tapi Data Tidak Masuk

1. **Cek Log File**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Cek Database**
   ```sql
   SELECT * FROM tx_header WHERE wip_no = 'YOUR_WIPNO' AND brand_id = YOUR_BRAND_ID;
   ```

3. **Cek Error Array**
   - Error akan ditampilkan di halaman import
   - Cek apakah ada error yang tidak tertangkap

4. **Cek Validation**
   - Pastikan semua required field terisi
   - Pastikan format data sesuai

### Jika Ada Error SQL

Error SQL akan di-parse menjadi user-friendly message:
- Incorrect integer value → "Invalid data type"
- Data too long → "Data too long for field"
- Duplicate entry → "Duplicate entry"

## 📝 Notes

1. **UpdateOrCreate**: Import menggunakan `updateOrCreate` berdasarkan WIPNO dan Brand ID
   - Jika record sudah ada → akan di-update
   - Jika record belum ada → akan di-create

2. **Batch Processing**: Import menggunakan batch (100 records per batch) untuk performa

3. **Cache**: Cache akan di-clear otomatis setelah import (sukses atau error)

4. **Default Values**:
   - `account_company`: 'DEFAULT' jika kosong
   - `customer_discount`: '0' jika kosong
   - `gross_value`: 0 jika kosong
   - `net_value`: 0 jika kosong

## 🚀 Testing

### Test Case 1: Valid Data
- Upload file dengan data valid
- Expected: Semua data berhasil diimport

### Test Case 2: Missing Required Field
- Upload file dengan WIPNO kosong
- Expected: Error "WIPNO is required"

### Test Case 3: Invalid Number
- Upload file dengan MAGICH = "ABC"
- Expected: Error "must be a valid number"

### Test Case 4: Invalid Date
- Upload file dengan InvDate = "2026-13-45"
- Expected: Error "must be a valid date"

### Test Case 5: Mixed Valid and Invalid
- Upload file dengan beberapa row valid dan beberapa invalid
- Expected: Valid rows diimport, invalid rows ditampilkan error

## ✅ Checklist

- [x] Error tracking per baris
- [x] Validasi required fields
- [x] Error display yang jelas
- [x] Success counter
- [x] Logging untuk debugging
- [x] Batch processing untuk performa
- [x] Cache clearing setelah import
- [x] User-friendly error messages
- [x] SQL error parsing
- [x] Documentation
