# Transaction Import - Simplified Logic

## Tanggal: 22 Januari 2026

## 📝 Logic Baru (Simplified)

Import logic disederhanakan menjadi:
1. **Loop** setiap row
2. **Check** apakah record exists (WHERE wipno + invno + brand_id)
3. **Update** jika exists
4. **Insert** jika tidak exists
5. **header_id** dibiarkan NULL (auto increment)

## 🔧 Implementation

### Logic Flow

```
FOR EACH row in Excel:
    1. Validate required fields
    2. Parse data
    3. Query: WHERE wip_no = X AND invoice_no = Y AND brand_id = Z
    4. IF found:
         UPDATE existing record
       ELSE:
         INSERT new record (header_id = NULL, auto increment)
    5. Success count++
```

### Code

```php
// Check if record exists: wipno + invno + brand
$existing = TransactionHeader::where('wip_no', $row['wipno'])
    ->where('invoice_no', $invoiceNo)
    ->where('brand_id', $this->brandId)
    ->first();

// Prepare data (semua field termasuk wipno, invno, brand)
$data = [
    'wip_no' => $row['wipno'],
    'invoice_no' => $invoiceNo,
    'brand_id' => $this->brandId,
    // ... all other fields
    'is_active' => '1',
];

if ($existing) {
    // UPDATE: Record exists
    $data['updated_by'] = (string) Auth::id();
    $existing->update($data);
    Log::info("Row X UPDATED");
} else {
    // INSERT: Record not exists
    $data['created_by'] = (string) Auth::id();
    // header_id dibiarkan null (auto increment)
    $header = TransactionHeader::create($data);
    Log::info("Row X INSERTED");
}
```

## 📊 Behavior

### Scenario 1: New Record
**Data:** WIPNO=12345, InvNo=1001, Brand=1 (not exists)
**Action:** INSERT
**Result:** New record created with auto-generated header_id

### Scenario 2: Existing Record
**Data:** WIPNO=12345, InvNo=1001, Brand=1 (exists)
**Action:** UPDATE
**Result:** Existing record updated, header_id unchanged

### Scenario 3: Same WIPNO, Different InvNo
**Data:** 
- Row 2: WIPNO=12345, InvNo=1001, Brand=1
- Row 3: WIPNO=12345, InvNo=1002, Brand=1

**Action:** Both INSERT
**Result:** 2 different records created

### Scenario 4: Import Same File Twice
**Data:** Same Excel file imported 2x
**Action:** 
- First import: All INSERT
- Second import: All UPDATE

**Result:** No duplicate, all records updated

## 🎯 Key Points

### 1. Unique Key
- **WIPNO + InvNo + Brand** = Unique combination
- Jika ketiga field sama → UPDATE
- Jika salah satu berbeda → INSERT

### 2. header_id
- **Tidak di-set manual**
- **Dibiarkan NULL** saat INSERT
- **Database auto increment** akan generate
- **Tidak berubah** saat UPDATE

### 3. created_by vs updated_by
- **INSERT:** Set `created_by` = current user
- **UPDATE:** Set `updated_by` = current user
- **created_by tidak berubah** saat UPDATE

### 4. No Duplicate Error
- Logic ini **tidak akan ada duplicate error**
- Karena selalu check dulu sebelum insert
- Jika exists → UPDATE (bukan INSERT)

## 📝 Logging

### INSERT Log
```
Row 2 INSERTED
- header_id: 36
- wipno: 12345
- invno: 1001
```

### UPDATE Log
```
Row 3 UPDATED
- header_id: 36
- wipno: 12345
- invno: 1001
```

## ✅ Advantages

1. **Simple & Clear**
   - Easy to understand
   - Easy to debug
   - Easy to maintain

2. **No Duplicate Error**
   - Always check before insert
   - Update if exists
   - No PRIMARY KEY conflict

3. **Idempotent**
   - Import same file multiple times = same result
   - Safe to re-import
   - No data duplication

4. **Auto Increment**
   - header_id managed by database
   - No manual ID management
   - No ID conflict

## 🧪 Testing

### Test 1: First Import
```
Excel:
Row 2: WIPNO=12345, InvNo=1001, Brand=1, Customer="John"
Row 3: WIPNO=12346, InvNo=1002, Brand=1, Customer="Jane"

Result:
- 2 records INSERTED
- header_id: 36, 37
```

### Test 2: Second Import (Same File)
```
Excel:
Row 2: WIPNO=12345, InvNo=1001, Brand=1, Customer="John Doe"
Row 3: WIPNO=12346, InvNo=1002, Brand=1, Customer="Jane Doe"

Result:
- 2 records UPDATED
- header_id: 36, 37 (unchanged)
- Customer names updated
```

### Test 3: Partial Update
```
Excel:
Row 2: WIPNO=12345, InvNo=1001, Brand=1 (exists)
Row 3: WIPNO=12347, InvNo=1003, Brand=1 (new)

Result:
- Row 2: UPDATED (header_id: 36)
- Row 3: INSERTED (header_id: 38)
```

## 📌 Important Notes

1. **Tidak ada batch insert** - Row by row processing
2. **header_id auto increment** - Managed by database
3. **Unique key: wipno + invno + brand** - Check combination
4. **Idempotent import** - Safe to re-import
5. **Clear logging** - INSERT vs UPDATE

## 🚀 Deployment

```bash
php artisan cache:clear
php artisan view:clear
```

Logic sekarang lebih simple, clear, dan tidak akan ada duplicate error!
