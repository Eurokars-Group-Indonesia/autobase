# Transaction Body Module Documentation

## Tanggal: 22 Januari 2026

## 📋 Ringkasan

Module Transaction Body telah berhasil dibuat dengan datatable yang memiliki fitur search dan date range picker menggunakan Flatpickr. Module ini hanya memiliki fitur READ (view data).

## 🗄️ Database Schema

### Tabel: tx_body

```sql
CREATE TABLE tx_body (
    body_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    part_no VARCHAR(100) NOT NULL,
    invoice_no INT UNSIGNED NOT NULL,
    description VARCHAR(250) NULL,
    qty DOUBLE(10,2) NOT NULL DEFAULT 0,
    selling_price DOUBLE(20,2) NOT NULL,
    discount DOUBLE(3,2) NOT NULL DEFAULT 0,
    extended_price DOUBLE(20,2) NOT NULL DEFAULT 0,
    menu_price DOUBLE(20,2) NOT NULL DEFAULT 0,
    vat CHAR(1) NULL,
    menu_vat CHAR(1) NULL,
    cost_price DOUBLE(20,2) NOT NULL DEFAULT 0,
    analysis_code CHAR(1) NOT NULL,
    invoice_status CHAR(1) NOT NULL COMMENT 'X = Closed, C = Completed',
    unit VARCHAR(10) NOT NULL COMMENT 'Example : Litre, Each, Pieces',
    mins_per_unit INT UNSIGNED NULL,
    wip_no INT UNSIGNED NOT NULL,
    line MEDIUMINT UNSIGNED NOT NULL,
    account_code VARCHAR(20) NULL,
    department VARCHAR(50) NULL,
    franchise_code VARCHAR(3) NULL,
    sales_type CHAR(1) NOT NULL,
    warranty_code VARCHAR(3) NOT NULL,
    menu_flag CHAR(1) NULL,
    contribution DOUBLE(3,2) NOT NULL DEFAULT 0,
    date_decard DATE NULL,
    magic_1 INT UNSIGNED NOT NULL,
    magic_2 INT UNSIGNED NOT NULL,
    po_no INT UNSIGNED NULL,
    grn_no INT UNSIGNED NULL,
    menu_code TINYINT UNSIGNED NULL,
    labour_rates CHAR(1) NULL,
    supplier_code VARCHAR(20) NULL,
    menu_link TINYINT UNSIGNED NOT NULL DEFAULT 0,
    currency_price DOUBLE(20,2) NULL,
    part_or_labour ENUM('P', 'L') NOT NULL,
    account_company VARCHAR(10) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED NULL,
    updated_date DATETIME NULL,
    unique_id CHAR(36) NOT NULL UNIQUE COMMENT 'UUIDV4, di gunakan untuk Get Data dari URL',
    is_active ENUM('0', '1') NULL DEFAULT '1',
    
    INDEX idx_part_no (part_no),
    INDEX idx_invoice_no (invoice_no),
    INDEX idx_wip_no (wip_no),
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by),
    INDEX idx_unique_id (unique_id),
    INDEX idx_is_active (is_active)
);
```

## 📁 File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── TransactionBodyController.php
└── Models/
    └── TransactionBody.php

database/
└── migrations/
    └── 2026_01_22_100000_create_tx_body_table.php

resources/
└── views/
    └── transaction-body/
        └── index.blade.php

routes/
└── web.php
```

## 🔧 Fitur

### 1. Read (View Data)
- **Route**: `GET /transaction-body`
- **Permission**: `transaction-body.view`
- **Fitur**:
  - Datatable dengan pagination (10, 25, 50, 100 per page)
  - Search by: Part No, Invoice No, WIP No, Description
  - Filter by date range (date_decard)
  - Cache hasil search (1 jam)
  - Flatpickr date picker

### 2. Search & Filter
- **Text Search**: Mencari berdasarkan part_no, invoice_no, wip_no, description
- **Date Range**: Filter berdasarkan date_decard (dari tanggal - sampai tanggal)
- **Cache Key**: `body:{user_id}:{search}:{date_from}:{date_to}:{per_page}:{page}`

## 🎨 UI Components

### Datatable Columns
1. Part No
2. Invoice No
3. WIP No
4. Description
5. Qty
6. Unit
7. Selling Price
8. Discount
9. Extended Price
10. Type (Part/Labour)
11. Status (Closed/Completed)
12. Active Status

### Badges
- **Type**: 
  - Part (P) = Blue badge
  - Labour (L) = Yellow badge
- **Status**:
  - Closed (X) = Red badge
  - Completed (C) = Green badge
- **Active**:
  - Active (1) = Green badge
  - Inactive (0) = Red badge

## 🔐 Permission Required

Untuk mengakses module ini, user harus memiliki permission:
- `transaction-body.view` - Untuk melihat daftar transaction body

## 📊 Caching Strategy

Module ini menggunakan caching untuk meningkatkan performa:
- **Cache Duration**: 1 jam
- **Cache Key Pattern**: `body:{user_id}:{search}:{date_from}:{date_to}:{per_page}:{page}`
- **Cache Driver**: Sesuai konfigurasi Laravel (default: file)

## 🚀 Cara Penggunaan

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Setup Permission
Tambahkan permission `transaction-body.view` ke database dan assign ke role yang sesuai.

### 3. Akses Module
Buka browser dan akses: `http://your-domain/transaction-body`

## 📝 Model Features

### TransactionBody Model
- **Primary Key**: `body_id` (auto increment)
- **Route Key**: `unique_id` (UUID v4)
- **Timestamps**: `created_date`, `updated_date`
- **Soft Delete**: Tidak ada (menggunakan `is_active` flag)

### Helper Methods
- `getInvoiceStatusLabel()` - Mengembalikan label status invoice
- `getPartOrLabourLabel()` - Mengembalikan label tipe (Part/Labour)

## 🔍 Index Optimization

Index yang dibuat untuk optimasi query:
- `idx_part_no` - Untuk search part number
- `idx_invoice_no` - Untuk search invoice number
- `idx_wip_no` - Untuk search WIP number
- `idx_created_by` - Untuk filter by creator
- `idx_updated_by` - Untuk filter by updater
- `idx_unique_id` - Untuk routing by UUID
- `idx_is_active` - Untuk filter active records

## 📌 Notes

1. Module ini hanya memiliki fitur READ, tidak ada Create, Update, atau Delete
2. Search menggunakan pattern `search%` untuk memanfaatkan B-tree index
3. Date filter menggunakan field `date_decard`
4. Pagination default: 10 records per page
5. Cache otomatis di-generate berdasarkan parameter search
6. Flatpickr digunakan untuk date picker dengan validasi date range

## 🔄 Future Enhancements

Jika diperlukan di masa depan:
- Export to Excel/CSV
- Detail view untuk setiap transaction body
- Relasi dengan Transaction Header
- Advanced filtering (by type, status, etc.)
- Bulk operations
