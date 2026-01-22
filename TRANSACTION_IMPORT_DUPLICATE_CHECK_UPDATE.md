# Transaction Import - Update Duplicate Check

## Tanggal: 22 Januari 2026

## 🔄 Perubahan

Update logic duplicate check dari **WIPNO + Brand** menjadi **WIPNO + InvNo + Brand**

## 📝 Alasan

Kombinasi WIPNO + Brand saja tidak cukup unik karena:
- Satu WIPNO bisa memiliki multiple invoice
- Perlu tambahan Invoice Number untuk uniqueness
- Kombinasi WIPNO + InvNo + Brand lebih akurat

## 🔧 Implementation

### 1. Update Duplicate Check Logic

**File:** `app/Imports/TransactionHeaderImport.php`

**Before:**
```php
// Check if record exists
$existing = TransactionHeader::where('wip_no', $row['wipno'])
    ->where('brand_id', $this->brandId)
    ->first();
```

**After:**
```php
// Check if record exists (wipno + invno + brand)
$existing = TransactionHeader::where('wip_no', $row['wipno'])
    ->where('invoice_no', $invoiceNo)
    ->where('brand_id', $this->brandId)
    ->first();
```

### 2. Update Error Message

**File:** `app/Http/Controllers/TransactionHeaderController.php`

**Before:**
```php
if (strpos($key, 'invoice_no') !== false) {
    $field = 'WIPNO + Inv No + Brand';
}

$errors[] = [
    'field' => $field,
    'error' => "A record with this value already exists..."
];
```

**After:**
```php
if (strpos($key, 'invoice_no') !== false || strpos($key, 'wip_no') !== false) {
    $field = 'WIPNO + InvNo + Brand';
    $errorMsg = "A record with this WIPNO, Invoice Number, and Brand combination already exists. The record should be updated instead of creating a new one.";
}

$errors[] = [
    'field' => $field,
    'error' => $errorMsg
];
```

## 📊 Behavior

### Scenario 1: Same WIPNO, Same InvNo, Same Brand
**Action:** UPDATE existing record
**Result:** No error, record updated

**Example:**
```
Row 2: WIPNO=12345, InvNo=1001, Brand=1 → Created
Row 5: WIPNO=12345, InvNo=1001, Brand=1 → Updated (same record)
```

### Scenario 2: Same WIPNO, Different InvNo, Same Brand
**Action:** CREATE new record
**Result:** No error, new record created

**Example:**
```
Row 2: WIPNO=12345, InvNo=1001, Brand=1 → Created
Row 5: WIPNO=12345, InvNo=1002, Brand=1 → Created (different invoice)
```

### Scenario 3: Same WIPNO, Same InvNo, Different Brand
**Action:** CREATE new record
**Result:** No error, new record created

**Example:**
```
Row 2: WIPNO=12345, InvNo=1001, Brand=1 → Created
Row 5: WIPNO=12345, InvNo=1001, Brand=2 → Created (different brand)
```

## 🎯 Testing

### Test 1: Update Existing Record
**Data:**
```
Import 1:
Row 2: WIPNO=12345, InvNo=1001, Brand=1, Customer="John"

Import 2:
Row 2: WIPNO=12345, InvNo=1001, Brand=1, Customer="John Doe"
```

**Expected:**
- Import 1: 1 record created
- Import 2: 1 record updated (Customer changed to "John Doe")
- Total records: 1

### Test 2: Create Multiple Invoices for Same WIPNO
**Data:**
```
Row 2: WIPNO=12345, InvNo=1001, Brand=1
Row 3: WIPNO=12345, InvNo=1002, Brand=1
Row 4: WIPNO=12345, InvNo=1003, Brand=1
```

**Expected:**
- 3 records created
- All with same WIPNO but different InvNo

### Test 3: Same WIPNO Across Brands
**Data:**
```
Row 2: WIPNO=12345, InvNo=1001, Brand=1
Row 3: WIPNO=12345, InvNo=1001, Brand=2
```

**Expected:**
- 2 records created
- Same WIPNO and InvNo but different Brand

## 📌 Important Notes

1. **Unique Key:** WIPNO + InvNo + Brand
2. **Update Behavior:** If all 3 match → Update
3. **Create Behavior:** If any of 3 different → Create new
4. **No Error:** Duplicate check tidak menghasilkan error, hanya update

## 🔍 Database Consideration

Jika ingin enforce uniqueness di database level, tambahkan unique constraint:

```sql
ALTER TABLE tx_header 
ADD UNIQUE KEY unique_wip_inv_brand (wip_no, invoice_no, brand_id);
```

**Note:** Ini optional, karena logic sudah handle di application level.

## ✅ Verification

- [x] Duplicate check updated to WIPNO + InvNo + Brand
- [x] Error message updated
- [x] Field name updated to "WIPNO + InvNo + Brand"
- [x] Testing scenarios documented
- [x] Cache cleared

## 🚀 Deployment

```bash
php artisan cache:clear
php artisan view:clear
```

Sekarang import akan menggunakan kombinasi WIPNO + InvNo + Brand untuk duplicate check!
