# Transaction Import - Fix Column Count Error

## Tanggal: 22 Januari 2026

## 🐛 Issue

**Error Message:**
```
Database error: SQLSTATE[21S01]: Insert value list does not match column list: 1136 Column count doesn't match value count at row 2
```

## 🔍 Root Cause Analysis

### Problem 1: Model Incrementing Setting
**Issue:** Model `TransactionHeader` memiliki setting `public $incrementing = false;` tapi di database `header_id` adalah `AUTO_INCREMENT`.

**Impact:** 
- Laravel mencoba insert `header_id` secara manual
- Menyebabkan column count mismatch
- Insert gagal

**Database Structure:**
```sql
`header_id` int unsigned NOT NULL AUTO_INCREMENT
```

**Model Setting (WRONG):**
```php
public $incrementing = false;  // ❌ SALAH
```

### Problem 2: Created_by Type Mismatch
**Issue:** Field `created_by` dan `updated_by` di database adalah `char(36)` (UUID) tapi import menggunakan `Auth::id()` yang return integer.

**Database Structure:**
```sql
`created_by` char(36) NOT NULL
`updated_by` char(36) DEFAULT NULL
```

**Import Code (WRONG):**
```php
'created_by' => Auth::id(),  // ❌ Return integer
'updated_by' => Auth::id(),  // ❌ Return integer
```

## 🔧 Solution

### Fix 1: Update Model Incrementing
**File:** `app/Models/TransactionHeader.php`

**Before:**
```php
public $incrementing = false;  // ❌
```

**After:**
```php
public $incrementing = true;   // ✅
```

**Explanation:**
- Karena `header_id` adalah AUTO_INCREMENT di database
- Laravel harus tahu bahwa primary key auto increment
- Jangan include `header_id` di fillable array

### Fix 2: Remove header_id from Fillable
**File:** `app/Models/TransactionHeader.php`

**Before:**
```php
protected $fillable = [
    'header_id',  // ❌ Tidak perlu karena auto increment
    'brand_id',
    // ...
];
```

**After:**
```php
protected $fillable = [
    'brand_id',  // ✅ header_id dihapus
    'invoice_no',
    // ...
];
```

### Fix 3: Cast Auth::id() to String
**File:** `app/Imports/TransactionHeaderImport.php`

**Before:**
```php
'created_by' => Auth::id(),  // ❌ Return integer
'updated_by' => Auth::id(),  // ❌ Return integer
```

**After:**
```php
'created_by' => (string) Auth::id(),  // ✅ Cast to string
'updated_by' => (string) Auth::id(),  // ✅ Cast to string
```

**Explanation:**
- Database field adalah `char(36)` untuk UUID
- Auth::id() return integer (user ID)
- Cast ke string agar compatible

### Fix 4: Remove Default Value for account_company
**File:** `app/Imports/TransactionHeaderImport.php`

**Before:**
```php
'account_company' => $row['acctcompany'] ?? 'DEFAULT',  // ❌
```

**After:**
```php
'account_company' => $row['acctcompany'] ?? null,  // ✅
```

**Explanation:**
- Field di database adalah nullable
- Biarkan null jika tidak ada value

## 📝 Complete Changes

### 1. app/Models/TransactionHeader.php
```php
class TransactionHeader extends Model
{
    protected $table = 'tx_header';
    protected $primaryKey = 'header_id';
    public $incrementing = true;  // ✅ Changed from false to true
    protected $keyType = 'integer';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_date';

    protected $fillable = [
        // 'header_id',  // ✅ REMOVED - auto increment
        'brand_id',
        'invoice_no',
        'wip_no',
        // ... rest of fields
    ];
}
```

### 2. app/Imports/TransactionHeaderImport.php
```php
$header = TransactionHeader::updateOrCreate(
    [
        'wip_no' => $row['wipno'],
        'brand_id' => $this->brandId,
    ],
    [
        // ... other fields
        'account_company' => $row['acctcompany'] ?? null,  // ✅ Changed from 'DEFAULT'
        'created_by' => (string) Auth::id(),  // ✅ Cast to string
        'updated_by' => (string) Auth::id(),  // ✅ Cast to string
        'is_active' => '1',
    ]
);
```

## 🧪 Testing

### Test 1: Check Model Settings
```bash
php artisan tinker --execute="echo 'Incrementing: ' . (new \App\Models\TransactionHeader())->incrementing;"
```
**Expected Output:** `Incrementing: 1`

### Test 2: Test Insert
```php
$header = TransactionHeader::create([
    'brand_id' => 1,
    'invoice_no' => 12345,
    'wip_no' => 54321,
    'invoice_date' => '2026-01-22',
    'vehicle_id' => 100,
    'document_type' => 'I',
    'mileage' => 10000,
    'currency_code' => 'IDR',
    'customer_discount' => '0',
    'created_by' => '1',
    'is_active' => '1',
]);
```
**Expected:** Record created successfully with auto-generated header_id

### Test 3: Import Excel
Upload file Excel dengan data valid
**Expected:** Import berhasil tanpa column count error

## 📊 Database vs Model Comparison

| Field | Database Type | Model Setting | Import Value |
|-------|--------------|---------------|--------------|
| header_id | INT UNSIGNED AUTO_INCREMENT | incrementing = true | Auto generated |
| created_by | CHAR(36) | - | (string) Auth::id() |
| updated_by | CHAR(36) | - | (string) Auth::id() |
| account_company | VARCHAR(50) NULL | - | null if empty |

## ⚠️ Important Notes

1. **Auto Increment Fields:**
   - Jangan include di fillable array
   - Set `$incrementing = true` di model
   - Laravel akan handle auto increment

2. **UUID vs Integer:**
   - Jika field di database adalah CHAR(36) untuk UUID
   - Tapi menggunakan integer user ID
   - Harus cast ke string: `(string) Auth::id()`

3. **Nullable Fields:**
   - Jika field nullable di database
   - Gunakan `null` bukan string 'DEFAULT'
   - Biarkan database handle default value

4. **UpdateOrCreate:**
   - Hanya include fields yang perlu di-update
   - Primary key auto increment tidak perlu di-set
   - Laravel akan handle insert vs update

## ✅ Verification Checklist

- [x] Model incrementing set to true
- [x] header_id removed from fillable
- [x] created_by cast to string
- [x] updated_by cast to string
- [x] account_company default changed to null
- [x] Cache cleared
- [x] Model tested
- [x] Import tested

## 🚀 Deployment

1. Clear all cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

2. Test import with real data

3. Verify no column count errors

4. Check imported data in database
