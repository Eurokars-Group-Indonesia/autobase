# Timeout Settings - Complete Overview

## 📋 Summary

| Component | Setting | Current Value | Purpose |
|-----------|---------|---------------|---------|
| **PHP Execution** | `max_execution_time` | **600 seconds (10 min)** | Max script execution time |
| **PHP Input** | `max_input_time` | **600 seconds (10 min)** | Max time parsing input data |
| **PHP-FPM Request** | `request_terminate_timeout` | **300 seconds (5 min)** | Max time per FPM request |
| **PHP-FPM Idle** | `pm.process_idle_timeout` | **10 seconds** | Idle worker timeout |
| **Nginx FastCGI** | `fastcgi_read_timeout` | **300 seconds (5 min)** | Max time waiting for PHP response |
| **Queue Worker** | `--max-time` | **3600 seconds (60 min)** | Max worker lifetime |
| **Queue Job Tries** | `--tries` | **3 times** | Max retry attempts |

---

## 🔧 Detailed Configuration

### 1. PHP Configuration (`docker/php/php.ini`)

```ini
max_execution_time = 600    # 10 minutes
max_input_time = 600        # 10 minutes
memory_limit = 1024M        # 1GB
```

**Purpose**: 
- `max_execution_time`: Berapa lama script PHP boleh berjalan
- `max_input_time`: Berapa lama PHP boleh parsing input (upload file, POST data)
- `memory_limit`: Max memory per request

**Impact on Import**:
- Import job bisa jalan sampai **10 menit** per chunk
- Cukup untuk process ~10,000-50,000 rows per chunk

---

### 2. PHP-FPM Configuration (`docker/php/www.conf`)

```ini
request_terminate_timeout = 300s    # 5 minutes
pm.process_idle_timeout = 10s       # 10 seconds
```

**Purpose**:
- `request_terminate_timeout`: Kill request yang terlalu lama (safety)
- `pm.process_idle_timeout`: Kill idle worker untuk free memory

**⚠️ Important**: 
- FPM timeout (300s) **lebih kecil** dari PHP execution time (600s)
- Ini bisa menyebabkan request terminated sebelum selesai!

**Recommendation**: Tingkatkan FPM timeout ke 600s

---

### 3. Nginx Configuration (`docker/nginx/default.conf`)

```nginx
fastcgi_read_timeout 300;    # 5 minutes
client_max_body_size 100M;   # Max upload size
```

**Purpose**:
- `fastcgi_read_timeout`: Berapa lama Nginx tunggu response dari PHP-FPM
- `client_max_body_size`: Max file upload size

**⚠️ Important**: 
- Nginx timeout (300s) **lebih kecil** dari PHP execution time (600s)
- Bisa menyebabkan 504 Gateway Timeout!

**Recommendation**: Tingkatkan ke 600s

---

### 4. Queue Worker Configuration (`docker/supervisord.conf`)

```bash
command=php artisan queue:work --sleep=1 --tries=3 --max-time=3600 --memory=512
```

**Parameters**:
- `--sleep=1`: Sleep 1 detik antara jobs
- `--tries=3`: Retry 3x jika gagal
- `--max-time=3600`: Worker restart setelah 1 jam (60 menit)
- `--memory=512`: Worker restart jika memory >512MB

**Purpose**: 
- Worker bisa process jobs sampai **1 jam** sebelum restart
- Cukup untuk import file besar

---

## 🚨 Potential Issues

### Issue 1: Request Timeout Before Completion

**Scenario**: Import file besar via web upload (bukan queue)

**Problem Chain**:
```
User uploads file (100MB)
↓
Nginx receives (OK - max 100M)
↓
PHP starts processing (OK - max 600s)
↓
After 300 seconds...
↓
PHP-FPM kills request (request_terminate_timeout = 300s) ❌
OR
Nginx gives up (fastcgi_read_timeout = 300s) ❌
```

**Result**: 504 Gateway Timeout atau 502 Bad Gateway

**Solution**: Tingkatkan timeout atau gunakan queue

---

### Issue 2: Queue Job Timeout

**Scenario**: Import 100,000 rows dalam 1 job

**Problem**:
- PHP execution time: 600s (10 min)
- Job butuh 15 menit
- Result: Job failed after 10 minutes

**Solution**: 
- Chunk size lebih kecil (500 rows instead of 1000)
- Atau tingkatkan `max_execution_time`

---

## 🔧 Recommended Fixes

### Fix 1: Align All Timeouts to 600s

**File**: `docker/php/www.conf`
```ini
request_terminate_timeout = 600s  # Changed from 300s
```

**File**: `docker/nginx/default.conf`
```nginx
fastcgi_read_timeout 600;  # Changed from 300
```

**Why**: Semua timeout harus sama atau lebih besar dari `max_execution_time`

---

### Fix 2: Increase for Large Imports (Optional)

Jika import sangat besar (>100,000 rows):

**File**: `docker/php/php.ini`
```ini
max_execution_time = 1200    # 20 minutes (from 600)
max_input_time = 1200        # 20 minutes (from 600)
```

**File**: `docker/php/www.conf`
```ini
request_terminate_timeout = 1200s  # 20 minutes
```

**File**: `docker/nginx/default.conf`
```nginx
fastcgi_read_timeout 1200;  # 20 minutes
```

---

## 📊 Timeout Hierarchy

```
Queue Worker (3600s = 60 min)
    ↓
PHP Execution Time (600s = 10 min)
    ↓
PHP-FPM Request (300s = 5 min) ⚠️ BOTTLENECK
    ↓
Nginx FastCGI (300s = 5 min) ⚠️ BOTTLENECK
```

**Current Bottleneck**: PHP-FPM dan Nginx timeout di 300s (5 menit)

**Recommendation**: Tingkatkan ke 600s untuk match PHP execution time

---

## 🧪 Testing Timeouts

### Test 1: Web Upload Timeout
```bash
# Upload file besar via web
# Monitor berapa lama sampai timeout
# Expected: Tidak timeout jika <10 menit
```

### Test 2: Queue Job Timeout
```bash
# Import file dengan 50,000 rows
# Monitor queue logs
docker-compose logs -f queue

# Expected: Job selesai tanpa timeout
```

### Test 3: Check Current PHP Settings
```bash
# Check dari container
docker exec laravel_app php -i | grep "max_execution_time"
docker exec laravel_app php -i | grep "max_input_time"
docker exec laravel_app php -i | grep "memory_limit"
```

---

## 🔄 Apply Timeout Changes

### Step 1: Update Configuration Files

Edit files sesuai recommendation di atas.

### Step 2: Rebuild Docker
```bash
docker-rebuild.bat
```

### Step 3: Verify Settings
```bash
# Check PHP settings
docker exec laravel_app php -i | grep timeout

# Check Nginx config
docker exec laravel_nginx nginx -T | grep timeout

# Check PHP-FPM config
docker exec laravel_app cat /usr/local/etc/php-fpm.d/www.conf | grep timeout
```

---

## 💡 Best Practices

### For Web Uploads:
1. **Use Queue**: Selalu gunakan queue untuk file >1000 rows
2. **Show Progress**: Tampilkan progress bar ke user
3. **Async Processing**: Jangan block user menunggu

### For Queue Jobs:
1. **Chunk Size**: 1000 rows per chunk (current)
2. **Timeout**: 600s per chunk (10 min)
3. **Memory**: 512MB per worker
4. **Workers**: 8 parallel workers

### For Large Files:
1. **Split Files**: Split file besar jadi beberapa file kecil
2. **Batch Import**: Import per batch (10,000 rows per batch)
3. **Monitor**: Monitor memory dan CPU usage

---

## 📈 Performance Estimates

### With Current Settings (600s timeout):

| Rows | Estimated Time | Will Timeout? |
|------|----------------|---------------|
| 1,000 | ~2-3 seconds | ❌ No |
| 10,000 | ~20-30 seconds | ❌ No |
| 50,000 | ~100-150 seconds | ❌ No |
| 100,000 | ~200-300 seconds | ❌ No |
| 500,000 | ~1000-1500 seconds | ⚠️ Maybe (if not chunked) |

**Note**: Dengan 8 workers dan chunking, import 500K rows akan di-split jadi multiple jobs yang masing-masing <600s

---

## 🆘 Troubleshooting

### Error: 504 Gateway Timeout
**Cause**: Nginx timeout sebelum PHP selesai  
**Fix**: Tingkatkan `fastcgi_read_timeout` di nginx config

### Error: 502 Bad Gateway
**Cause**: PHP-FPM crash atau timeout  
**Fix**: Tingkatkan `request_terminate_timeout` di PHP-FPM config

### Error: Maximum execution time exceeded
**Cause**: PHP script timeout  
**Fix**: Tingkatkan `max_execution_time` di php.ini

### Error: Worker timeout
**Cause**: Queue job terlalu lama  
**Fix**: Kurangi chunk size atau tingkatkan `--max-time`

---

## 📚 Related Files

- `docker/php/php.ini` - PHP configuration
- `docker/php/www.conf` - PHP-FPM pool configuration
- `docker/nginx/default.conf` - Nginx configuration
- `docker/supervisord.conf` - Queue worker configuration

## 📖 Related Documentation

- `OPTIMIZATION_SUMMARY.md` - Full optimization details
- `DOCKER_OPTIMIZATION.md` - Docker configuration guide
- `IMPORT_ERROR_FIXES.md` - Import error solutions

---

**Last Updated**: January 22, 2026  
**Version**: 1.0
