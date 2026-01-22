# Transaction Body Import Feature

## Overview
Fitur import Excel untuk Transaction Body telah berhasil dibuat dengan menggunakan `updateOrCreate` berdasarkan kombinasi: `part_no + invoice_no + wip_no + line`.

## Files Created/Modified

### 1. Import Class
- **File**: `app/Imports/TransactionBodyImport.php`
- **Fungsi**: Menangani proses import dari Excel ke database
- **Validasi**:
  - Required fields: Part, InvNo, WIPNo, Line, AnalCode, InvStat, SaleType, Parts/Labour
  - Numeric validation untuk field-field numerik
  - Date parsing untuk DateDecard
  - Enum validation untuk InvStat (X/C) dan Parts/Labour (P/L)

### 2. Controller
- **File**: `app/Http/Controllers/TransactionBodyController.php`
- **Methods Added**:
  - `showImport()` - Menampilkan halaman import
  - `import()` - Memproses file import
  - `downloadTemplate()` - Download template CSV
  - `clearTransactionBodyCache()` - Clear cache setelah import
  - `parseSqlErrorDetailed()` - Parse SQL error menjadi user-friendly message
  - `getFriendlyColumnName()` - Mapping column name ke friendly name

### 3. Views
- **File**: `resources/views/transaction-body/import.blade.php`
- **Features**:
  - Drag & drop file upload
  - Validasi mime type (csv, xls, xlsx)
  - Error display dengan detail row, field, value, dan error message
  - Success count display
  - Template download button
  - Instructions panel

- **File**: `resources/views/transaction-body/index.blade.php` (Modified)
- **Changes**: Menambahkan button "Import Excel" dengan permission check

### 4. Routes
- **File**: `routes/web.php`
- **Routes Added**:
  - `GET /transaction-body/import` - Show import page
  - `POST /transaction-body/import` - Process import
  - `GET /transaction-body/import/template` - Download template
- **Permission**: `transaction-body.import`

### 5. Permission Seeder
- **File**: `database/seeders/TransactionBodyImportPermissionSeeder.php`
- **Permission Code**: `transaction-body.import`
- **Permission Name**: Import Transaction Body
- **Auto-assigned to**: Super Admin role (role_id = 1)

## Excel Template Columns

| No | Excel Column | Database Field | Type | Required | Notes |
|----|--------------|----------------|------|----------|-------|
| 1 | Part | part_no | string | Yes | Part Number |
| 2 | Desc | description | string | No | Description |
| 3 | Qty | qty | decimal | No | Quantity |
| 4 | SellPrice | selling_price | decimal | No | Selling Price |
| 5 | Disc% | discount | decimal | No | Discount Percentage |
| 6 | ExtPrice | extended_price | decimal | No | Extended Price |
| 7 | MP | menu_price | decimal | No | Menu Price |
| 8 | VAT | vat | char(1) | No | VAT |
| 9 | MV | menu_vat | char(1) | No | Menu VAT |
| 10 | CostPr | cost_price | decimal | No | Cost Price |
| 11 | AnalCode | analysis_code | char(1) | Yes | Analysis Code |
| 12 | InvStat | invoice_status | char(1) | Yes | X = Closed, C = Completed |
| 13 | UOI | unit | string | No | Unit of Issue |
| 14 | MpU | mins_per_unit | integer | No | Minutes Per Unit |
| 15 | WIPNo | wip_no | integer | Yes | WIP Number |
| 16 | Line | line | integer | Yes | Line Number |
| 17 | Acct | account_code | string | No | Account Code |
| 18 | Dept | department | string | No | Department |
| 19 | InvNo | invoice_no | integer | Yes | Invoice Number |
| 20 | FC | franchise_code | string | No | Franchise Code |
| 21 | SaleType | sales_type | char(1) | Yes | Sales Type |
| 22 | Wcode | warranty_code | string | No | Warranty Code (max 3 chars) |
| 23 | MenuFlag | menu_flag | char(1) | No | Menu Flag |
| 24 | Contrib | contribution | decimal | No | Contribution |
| 25 | DateDecard | date_decard | date | No | Date Decard (YYYY-MM-DD) |
| 26 | HMagic1 | magic_1 | integer | No | Magic 1 |
| 27 | HMagic2 | magic_2 | integer | No | Magic 2 |
| 28 | PO | po_no | integer | No | PO Number |
| 29 | GRN | grn_no | integer | No | GRN Number |
| 30 | Menu | menu_code | integer | No | Menu Code |
| 31 | LR | labour_rates | char(1) | No | Labour Rates |
| 32 | Supp | supplier_code | string | No | Supplier Code |
| 33 | MenuLink | menu_link | integer | No | Menu Link |
| 34 | CurPrice | currency_price | decimal | No | Currency Price |
| 35 | Parts/Labour | part_or_labour | enum | Yes | P = Part, L = Labour |

## Import Logic

### UpdateOrCreate Condition
Record akan di-update jika sudah ada, atau di-create jika belum ada, berdasarkan kombinasi:
- `part_no` (Part)
- `invoice_no` (InvNo)
- `wip_no` (WIPNo)
- `line` (Line)

### Error Handling
1. **Validation Errors**: Ditampilkan per row dengan detail field dan value
2. **SQL Errors**: Di-parse menjadi user-friendly message
3. **Success Count**: Menampilkan jumlah record yang berhasil di-import
4. **Error List**: Menampilkan list error dengan:
   - Row number (Excel row)
   - Field name
   - Value yang error
   - Error message

### Cache Management
Setelah import (baik sukses maupun ada error), cache akan di-clear untuk memastikan data yang ditampilkan adalah data terbaru.

## Usage

1. Login dengan user yang memiliki permission `transaction-body.import`
2. Buka halaman Transaction Body
3. Klik button "Import Excel"
4. Download template jika diperlukan
5. Upload file Excel (csv, xls, xlsx)
6. Sistem akan memproses dan menampilkan hasil:
   - Jika sukses semua: redirect ke index dengan success message
   - Jika ada error: tetap di halaman import dengan detail error per row

## Testing Checklist

- [x] Import class created
- [x] Controller methods added
- [x] Views created/modified
- [x] Routes added
- [x] Permission seeder created
- [x] Button import added to index page
- [ ] Test upload valid file
- [ ] Test upload invalid file type
- [ ] Test upload file with errors
- [ ] Test updateOrCreate logic
- [ ] Test error display
- [ ] Test template download

## Notes

- File size limit: 10MB
- Supported formats: .xlsx, .xls, .csv
- Date format: YYYY-MM-DD or Excel date serial number
- Numeric fields akan di-parse untuk menghilangkan karakter non-numeric
- Empty rows akan di-skip
- Cache akan di-clear setelah import untuk memastikan data fresh
