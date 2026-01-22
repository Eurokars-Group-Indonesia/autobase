# Transaction Import - Row Number Fix

## Tanggal: 22 Januari 2026

## 🐛 Issue

Error menampilkan "Row: Unknown" padahal seharusnya menampilkan nomor baris Excel yang spesifik.

**Screenshot Error:**
```
Row: Unknown
Field: Unknown
Value: 9
Error: A record with this value already exists...
```

**Expected:**
```
Excel Row 3
Field: WIPNO
Value: 12345
Error: A record with this value already exists...
```

## 🔍 Root Cause

SQL error (seperti duplicate entry) tidak memberikan row number dalam error message. Kita perlu menggunakan `currentRow` dari import class.

## 🔧 Solution

### 1. Make currentRow Public

**File:** `app/Imports/TransactionHeaderImport.php`

**Before:**
```php
protected $currentRow = 1;
```

**After:**
```php
public $currentRow = 1; // Public agar bisa diakses dari controller
```

### 2. Pass Import Object to Error Parser

**File:** `app/Http/Controllers/TransactionHeaderController.php`

**Before:**
```php
$errorDetails = $this->parseSqlErrorDetailed($e);
```

**After:**
```php
$errorDetails = $this->parseSqlErrorDetailed($e, $import);
```

### 3. Use currentRow in Error Parser

**File:** `app/Http/Controllers/TransactionHeaderController.php`

**Before:**
```php
private function parseSqlErrorDetailed($exception)
{
    // ...
    $errors[] = [
        'row' => 'Unknown',  // ❌ Always Unknown
        'field' => $field,
        'value' => $value,
        'error' => $message
    ];
}
```

**After:**
```php
private function parseSqlErrorDetailed($exception, $import = null)
{
    // Get current row from import if available
    $currentRow = $import ? $import->currentRow : 'Unknown';
    
    // ...
    $errors[] = [
        'row' => $currentRow,  // ✅ Use actual row number
        'field' => $field,
        'value' => $value,
        'error' => $message
    ];
}
```

## 📊 Before vs After

### Before
```
Import Errors (1 error)

Row: Unknown
Field: Unknown
Value: 9
Error: A record with this value already exists...
```

### After
```
Import Errors (1 error)

Excel Row 3
Field: Unique ID
Value: 9
Error: A record with this value already exists...
```

## 🎯 How It Works

1. **Import Process:**
   - Row 1: Header (skipped)
   - Row 2: Data 1 → `currentRow = 2`
   - Row 3: Data 2 → `currentRow = 3` ← Error terjadi di sini
   - Row 4: Data 3 → `currentRow = 4`

2. **Error Tracking:**
   - Saat error terjadi di Row 3
   - `$import->currentRow` = 3
   - Error message: "Excel Row 3"

3. **SQL Error (No Row Number):**
   - SQL error seperti duplicate entry tidak punya row number
   - Kita ambil dari `$import->currentRow`
   - Jadi tetap tahu error di row berapa

## 📝 Error Types & Row Number

| Error Type | Row Number Source | Example |
|------------|------------------|---------|
| Validation Error | `$this->currentRow` | Excel Row 3 |
| SQL Error (with row) | SQL message + 1 | Excel Row 5 |
| SQL Error (no row) | `$import->currentRow` | Excel Row 3 |
| Duplicate Entry | `$import->currentRow` | Excel Row 7 |
| Generic Error | `$import->currentRow` | Excel Row 10 |

## ✅ Testing

### Test 1: Validation Error
**Data:** Row 3 - WIPNO empty
**Expected:** "Excel Row 3"
**Source:** `$this->currentRow` in import

### Test 2: SQL Error with Row
**Data:** Row 5 - Invalid integer
**Expected:** "Excel Row 5"
**Source:** SQL error message + 1

### Test 3: Duplicate Entry
**Data:** Row 7 - Duplicate unique_id
**Expected:** "Excel Row 7"
**Source:** `$import->currentRow`

### Test 4: Generic Error
**Data:** Row 10 - Unknown error
**Expected:** "Excel Row 10"
**Source:** `$import->currentRow`

## 🔍 Debugging

Jika row number masih "Unknown":

1. **Check Import Object:**
```php
\Log::info('Current Row', ['row' => $import->currentRow]);
```

2. **Check Error Parser:**
```php
\Log::info('Error Details', [
    'has_import' => $import !== null,
    'current_row' => $import ? $import->currentRow : 'null'
]);
```

3. **Check Error Array:**
```php
\Log::info('Error Array', ['errors' => $errorDetails]);
```

## 📌 Important Notes

1. **currentRow must be public** agar bisa diakses dari controller
2. **Pass $import object** ke error parser
3. **Fallback to 'Unknown'** jika import object tidak tersedia
4. **SQL row number + 1** karena row 1 adalah header Excel

## ✅ Verification

- [x] currentRow changed to public
- [x] Import object passed to error parser
- [x] Error parser uses currentRow
- [x] All error types show row number
- [x] Fallback to 'Unknown' if no row available
- [x] Cache cleared
- [x] Tested with real data

## 🚀 Deployment

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

Sekarang semua error akan menampilkan nomor baris Excel yang benar!
