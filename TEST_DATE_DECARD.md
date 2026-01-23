# Quick Test - Date Decard Fix

## 1. Deploy Fix

```bash
# Upload file yang diubah
scp app/Imports/TransactionBodyImport.php root@egi-dockerdev:/home/itteam/service-history-new-old/app/Imports/

# SSH ke server
ssh root@egi-dockerdev
cd /home/itteam/service-history-new-old

# Clear cache
docker exec -it laravel_app php artisan cache:clear
docker exec -it laravel_app php artisan config:clear
```

## 2. Test Import

1. Buka aplikasi di browser
2. Import file Excel Transaction Body
3. Cek hasilnya

## 3. Cek Log (Untuk Debug)

```bash
# Lihat log date parsing
docker exec -it laravel_app tail -50 /var/www/html/storage/logs/laravel.log | grep "Date parsing"

# Atau lihat semua log
docker exec -it laravel_app tail -100 /var/www/html/storage/logs/laravel.log
```

**Expected Output:**
```
Date parsing for row 5: {"original_value":40000,"parsed_value":"2009-07-06","is_numeric":true}
Date parsing for row 6: {"original_value":"31/08/2009","parsed_value":"2009-08-31","is_numeric":false}
```

## 4. Cek Database

```bash
# Masuk ke MySQL
docker exec -it percona-mysql mysql -u root -p service_history_new

# Cek berapa banyak 1970-01-01
SELECT COUNT(*) FROM transaction_body WHERE date_decard = '1970-01-01';

# Cek sample data
SELECT part_no, invoice_no, date_decard 
FROM transaction_body 
ORDER BY body_id DESC 
LIMIT 10;

# Cek distribusi tahun
SELECT YEAR(date_decard) as year, COUNT(*) as count 
FROM transaction_body 
WHERE date_decard IS NOT NULL
GROUP BY YEAR(date_decard) 
ORDER BY year;
```

**Expected Result:**
- Tidak ada atau sangat sedikit 1970-01-01
- Date terdistribusi di tahun yang reasonable (2000-2024)

## 5. Jika Masih Ada 1970-01-01

### A. Cek Format Date di Excel

1. Buka file Excel yang diimport
2. Klik cell date_decard
3. Lihat value di formula bar
4. Cek format cell (Right click > Format Cells)

### B. Test Manual Parsing

```bash
# Masuk ke tinker
docker exec -it laravel_app php artisan tinker

# Test dengan value dari Excel
$date = 40000; // Ganti dengan value asli dari Excel
$parsed = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
echo $parsed->format('Y-m-d');

# Test dengan string
$date = '31/08/2009';
$parsed = \Carbon\Carbon::createFromFormat('d/m/Y', $date);
echo $parsed->format('Y-m-d');
```

### C. Cek Log Warning

```bash
# Cek log warning untuk date parsing
docker exec -it laravel_app grep "Failed to parse date" /var/www/html/storage/logs/laravel.log

# Cek log error
docker exec -it laravel_app grep "Could not parse date" /var/www/html/storage/logs/laravel.log
```

## 6. Update Data Lama (Optional)

Jika ada data lama dengan 1970-01-01 yang seharusnya NULL:

```sql
-- Set 1970-01-01 menjadi NULL
UPDATE transaction_body 
SET date_decard = NULL 
WHERE date_decard = '1970-01-01';

-- Verify
SELECT COUNT(*) FROM transaction_body WHERE date_decard = '1970-01-01';
-- Should be 0
```

## 7. Re-import (Recommended)

Untuk hasil terbaik, re-import data dengan fix yang baru:

1. Hapus data lama (optional):
   ```sql
   DELETE FROM transaction_body WHERE date_decard = '1970-01-01';
   ```

2. Import ulang file Excel
3. Cek hasilnya

## Expected Results

✅ **Date parsing berhasil:**
- Date dari Excel terparsing dengan benar
- Tidak ada atau minimal 1970-01-01
- Date terdistribusi di tahun yang reasonable

✅ **Log menunjukkan parsing sukses:**
```
Date parsing for row X: {"original_value":...,"parsed_value":"2009-08-31",...}
```

✅ **Database berisi date yang benar:**
```sql
SELECT date_decard FROM transaction_body LIMIT 10;
-- Output: 2009-08-31, 2010-05-15, 2011-03-20, etc.
```

## Troubleshooting Quick Reference

| Issue | Solution |
|-------|----------|
| Masih banyak 1970-01-01 | Cek log, lihat format date di Excel |
| Log tidak muncul | Set `LOG_LEVEL=debug` di .env.docker |
| Parsing gagal untuk format tertentu | Tambahkan format ke array `$formats` |
| Excel Mac (1904 system) | Uncomment handling 1904 di code |

---

**TL;DR:**
1. Upload file TransactionBodyImport.php
2. Clear cache
3. Import Excel
4. Cek log dan database
5. Seharusnya tidak ada 1970-01-01 lagi!
