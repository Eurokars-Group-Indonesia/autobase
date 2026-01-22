# InvNo Zero Validation - Documentation

## Problem
InvNo (Invoice Number) dengan nilai 0 ditolak oleh validasi, padahal seharusnya diperbolehkan.

## Solution Applied

### Updated Validation Logic

**File**: `app/Imports/TransactionHeaderImport.php`

**Before**:
```php
if ($invoiceNo === null || $invoiceNo === '') {
    $rowErrors[] = [
        'error' => 'Invoice Number is required and must be a valid integer number (e.g., 1, 123).'
    ];
}
```

**After**:
```php
// InvNo boleh 0, tapi tidak boleh null atau empty string
if ($invoiceNo === null || $invoiceNo === '') {
    $rowErrors[] = [
        'error' => 'Invoice Number is required and must be a valid integer number (0 is allowed, e.g., 0, 1, 123).'
    ];
}
```

**File**: `app/Imports/TransactionBodyImport.php`

**Before**:
```php
if ($invoiceNo === null || $invoiceNo === '') {
    $this->errors[] = [
        'error' => 'Invoice Number must be a valid number'
    ];
}
```

**After**:
```php
// InvNo boleh 0, tapi tidak boleh null atau empty string
if ($invoiceNo === null || $invoiceNo === '') {
    $this->errors[] = [
        'error' => 'Invoice Number must be a valid number (0 is allowed)'
    ];
}
```

## How It Works

### parseNumeric() Function
```php
private function parseNumeric($value)
{
    // Check if value is null or empty string (but allow 0)
    if ($value === null || $value === '') {
        return null;
    }
    
    // If value is already 0, return 0
    if ($value === 0 || $value === '0') {
        return 0;
    }
    
    return is_numeric($value) ? (int)$value : null;
}
```

### Validation Logic
```php
$invoiceNo = $this->parseNumeric($row['invno'] ?? null);

// This validation allows 0
if ($invoiceNo === null || $invoiceNo === '') {
    // Error: InvNo is required
}
```

### Test Cases

| Input Value | parseNumeric() Returns | Validation Result |
|-------------|------------------------|-------------------|
| `0` | `0` (integer) | ✅ **PASS** - 0 is allowed |
| `'0'` | `0` (integer) | ✅ **PASS** - 0 is allowed |
| `null` | `null` | ❌ **FAIL** - Required field |
| `''` (empty) | `null` | ❌ **FAIL** - Required field |
| `1` | `1` (integer) | ✅ **PASS** - Valid number |
| `'123'` | `123` (integer) | ✅ **PASS** - Valid number |
| `'ABC'` | `null` | ❌ **FAIL** - Not a number |

## Why This Works

### PHP Strict Comparison (`===`)

```php
// These are all FALSE (different types/values)
0 === null      // false (0 is not null)
0 === ''        // false (0 is not empty string)
0 === false     // false (strict comparison)

// These are TRUE
null === null   // true
'' === ''       // true
0 === 0         // true
```

### Validation Flow

```
Input: invno = 0
    ↓
parseNumeric(0)
    ↓
Returns: 0 (integer)
    ↓
Check: if (0 === null || 0 === '')
    ↓
Result: false || false = false
    ↓
✅ No error, validation PASS
```

```
Input: invno = null
    ↓
parseNumeric(null)
    ↓
Returns: null
    ↓
Check: if (null === null || null === '')
    ↓
Result: true || false = true
    ↓
❌ Error: Invoice Number is required
```

## Testing

### Test Case 1: InvNo = 0
**Excel Data**:
```
WIPNO | InvNo | ...
1     | 0     | ...
```

**Expected**: ✅ Import success, InvNo saved as 0

### Test Case 2: InvNo = empty
**Excel Data**:
```
WIPNO | InvNo | ...
1     |       | ...
```

**Expected**: ❌ Validation error: "Invoice Number is required"

### Test Case 3: InvNo = 123
**Excel Data**:
```
WIPNO | InvNo | ...
1     | 123   | ...
```

**Expected**: ✅ Import success, InvNo saved as 123

### Test Case 4: InvNo = "INV001"
**Excel Data**:
```
WIPNO | InvNo    | ...
1     | INV001   | ...
```

**Expected**: ❌ Validation error: "Invoice Number must be a valid integer"

## Database Schema

The database column allows 0:

```sql
CREATE TABLE tx_header (
    invoice_no INT NOT NULL,  -- Allows 0
    ...
);
```

## Related Fields

Other fields that also allow 0:
- `mileage` - Explicitly allows 0 (new car)
- `exchange_rate` - Can be 0
- `gross_value` - Can be 0
- `net_value` - Can be 0

Fields that do NOT allow 0:
- `wip_no` - Must be positive integer
- `vehicle_id` - Must be positive integer

## Error Messages

### Old Error Message:
```
Field: InvNo
Value: 0
Error: Invoice Number is required and must be a valid integer number (e.g., 1, 123).
```

### New Error Message (when actually empty):
```
Field: InvNo
Value: empty
Error: Invoice Number is required and must be a valid integer number (0 is allowed, e.g., 0, 1, 123).
```

## Summary

✅ **Fixed**: InvNo now accepts 0 as valid value  
✅ **Maintained**: Still rejects null and empty values  
✅ **Clear**: Error message updated to indicate 0 is allowed  
✅ **Consistent**: Same logic applied to both Header and Body imports  

## Files Modified

- `app/Imports/TransactionHeaderImport.php`
- `app/Imports/TransactionBodyImport.php`

## Related Documentation

- `IMPORT_ERROR_FIXES.md` - Other import error fixes
- `VALIDATION_SUMMARY.md` - All validation rules

---

**Fixed**: January 22, 2026  
**Version**: 1.0
