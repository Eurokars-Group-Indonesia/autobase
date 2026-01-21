# Example Import Data

## Sample Data untuk Testing

Berikut adalah contoh data yang bisa digunakan untuk testing import:

### Row 1 - Data Lengkap
```
WIPNO: WIP001
Account: ACC001
CustName: PT Maju Jaya Motor
Add1: Jl. Sudirman No. 123
Add2: Blok A-5
Add3: Jakarta Pusat
Add4: DKI Jakarta
Add5: 10220
Dept: SERVICE
InvNo: INV2026001
InvDate: 2026-01-15
MAGICH: 1001
DocType: I
ExchangeRate: 1.00
RegNo: B1234XYZ
Chassis: MHKA1234567890123
Mileage: 50000
CurrCode: IDR
GrossValue: 15000000
NetValue: 14250000
CustDisc: 750000
SvcCode: SVC
RegDate: 2020-05-15
Description: Service berkala 50.000 km
EngineNo: ENG123456789
AcctCompany: COMP001
```

### Row 2 - Data Minimal (Hanya Required)
```
WIPNO: WIP002
Account: 
CustName: 
Add1: 
Add2: 
Add3: 
Add4: 
Add5: 
Dept: 
InvNo: 
InvDate: 
MAGICH: 
DocType: 
ExchangeRate: 
RegNo: 
Chassis: 
Mileage: 
CurrCode: 
GrossValue: 
NetValue: 
CustDisc: 
SvcCode: 
RegDate: 
Description: 
EngineNo: 
AcctCompany: 
```

### Row 3 - Data Credit Note
```
WIPNO: WIP003
Account: ACC002
CustName: CV Sejahtera Motor
Add1: Jl. Gatot Subroto No. 45
Add2: 
Add3: Bandung
Add4: Jawa Barat
Add5: 
Dept: SALES
InvNo: CN2026001
InvDate: 2026-01-20
MAGICH: 1002
DocType: C
ExchangeRate: 1.00
RegNo: D5678ABC
Chassis: MHKA9876543210987
Mileage: 25000
CurrCode: IDR
GrossValue: 8500000
NetValue: 8075000
CustDisc: 425000
SvcCode: SLS
RegDate: 2022-03-10
Description: Credit note untuk retur parts
EngineNo: ENG987654321
AcctCompany: COMP002
```

### Row 4 - Update Existing (Same WIPNO)
```
WIPNO: WIP001
Account: ACC001
CustName: PT Maju Jaya Motor (Updated)
Add1: Jl. Sudirman No. 123 (New Address)
Add2: Blok A-5
Add3: Jakarta Pusat
Add4: DKI Jakarta
Add5: 10220
Dept: SERVICE
InvNo: INV2026001
InvDate: 2026-01-15
MAGICH: 1001
DocType: I
ExchangeRate: 1.00
RegNo: B1234XYZ
Chassis: MHKA1234567890123
Mileage: 55000
CurrCode: IDR
GrossValue: 16000000
NetValue: 15200000
CustDisc: 800000
SvcCode: SVC
RegDate: 2020-05-15
Description: Service berkala 55.000 km (Updated)
EngineNo: ENG123456789
AcctCompany: COMP001
```

## Format CSV untuk Copy-Paste

```csv
WIPNO,Account,CustName,Add1,Add2,Add3,Add4,Add5,Dept,InvNo,InvDate,MAGICH,DocType,ExchangeRate,RegNo,Chassis,Mileage,CurrCode,GrossValue,NetValue,CustDisc,SvcCode,RegDate,Description,EngineNo,AcctCompany
WIP001,ACC001,PT Maju Jaya Motor,Jl. Sudirman No. 123,Blok A-5,Jakarta Pusat,DKI Jakarta,10220,SERVICE,INV2026001,2026-01-15,1001,I,1.00,B1234XYZ,MHKA1234567890123,50000,IDR,15000000,14250000,750000,SVC,2020-05-15,Service berkala 50.000 km,ENG123456789,COMP001
WIP002,,,,,,,,,,,,,,,,,,,,,,,,,
WIP003,ACC002,CV Sejahtera Motor,Jl. Gatot Subroto No. 45,,Bandung,Jawa Barat,,SALES,CN2026001,2026-01-20,1002,C,1.00,D5678ABC,MHKA9876543210987,25000,IDR,8500000,8075000,425000,SLS,2022-03-10,Credit note untuk retur parts,ENG987654321,COMP002
```

## Expected Results

Setelah import:
- **Row 1**: Created - 1 record baru dengan WIPNO = WIP001
- **Row 2**: Created - 1 record baru dengan WIPNO = WIP002 (hanya WIPNO terisi)
- **Row 3**: Created - 1 record baru dengan WIPNO = WIP003
- **Row 4**: Updated - Record WIP001 akan di-update dengan data baru

Total records di database: **3 records** (WIP001 updated, WIP002 & WIP003 new)

## Testing Steps

1. **Buat file Excel baru** dengan nama `test_import.xlsx`
2. **Copy header** dari template yang di-download
3. **Paste data** dari contoh di atas
4. **Pilih Brand** di form import (pastikan brand sudah ada)
5. **Upload file** dan klik Import
6. **Cek hasil** di transaction list
7. **Import ulang** dengan row 4 untuk test update functionality

## Validation Test Cases

### Test Case 1: Valid Data
- Input: Row 1 (data lengkap)
- Expected: ✅ Success - Record created

### Test Case 2: Minimal Data
- Input: Row 2 (hanya WIPNO)
- Expected: ✅ Success - Record created with null values

### Test Case 3: Credit Note
- Input: Row 3 (DocType = C)
- Expected: ✅ Success - Record created with document type Credit Note

### Test Case 4: Update Existing
- Input: Row 4 (WIPNO sama dengan Row 1)
- Expected: ✅ Success - Record WIP001 updated

### Test Case 5: Missing WIPNO
- Input: Row tanpa WIPNO
- Expected: ❌ Error - Validation failed

### Test Case 6: Invalid Date Format
- Input: InvDate = "invalid-date"
- Expected: ⚠️ Warning - Date set to null, record still created

### Test Case 7: Large File
- Input: File > 10MB
- Expected: ❌ Error - File too large

### Test Case 8: Wrong Format
- Input: .pdf file
- Expected: ❌ Error - Invalid file format

## Notes

- Semua field kecuali WIPNO adalah optional
- Date format bisa YYYY-MM-DD atau Excel date serial number
- Numeric values akan otomatis di-cast
- Empty cells akan di-set sebagai null
- DocType: I = Invoice, C = Credit Note
- Brand harus dipilih sebelum upload
