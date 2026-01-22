# Transaction Header Module Documentation

## Tanggal: 21 Januari 2026

## 📋 Ringkasan

Module Transaction Header telah berhasil dibuat dengan datatable yang memiliki fitur search dan date range picker menggunakan Flatpickr.

## 🗄️ Database Schema

### Tabel: tx_header

```sql
CREATE TABLE tx_header (
    header_id INT UNSIGNED NOT NULL PRIMARY KEY,
    brand_id INT UNSIGNED NOT NULL,
    invoice_no INT UNSIGNED NOT NULL,
    wip_no INT UNSIGNED NOT NULL,
    account_code VARCHAR(20) NULL,
    customer_name VARCHAR(150) NULL,
    address_1 TEXT NULL,
    address_2 TEXT NULL,
    address_3 TEXT NULL,
    address_4