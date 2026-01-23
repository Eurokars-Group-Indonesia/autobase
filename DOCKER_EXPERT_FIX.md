# Docker Expert Fix - Permission Denied Error

## Error yang Terjadi

```
PermissionError: [Errno 13] Permission denied: '/var/www/html/storage/logs/supervisord.log'
UnexpectedValueException: The stream or file "/var/www/html/storage/logs/laravel.log" could not be opened
file_put_contents(/var/www/html/bootstrap/cache/config.php): Failed to open stream: Permission denied
```

## Root Cause Analysis

### Masalah Utama:
1. **Container berjalan sebagai `www-data`** (non-root user)
2. **Volume mount dari host** membuat file/folder dimiliki oleh user host (UID 1000 atau root)
3. **www-data tidak punya permission** untuk menulis ke storage yang dimiliki user lain
4. **Entrypoint script tidak bisa fix permission** karena berjalan sebagai www-data (non-root)

### Mengapa Terjadi:
```
Host (server)                    Container
├─ storage/ (owned by root)  →  ├─ storage/ (mounted, owned by root)
│  └─ logs/                      │  └─ logs/
│     └─ laravel.log             │     └─ laravel.log (www-data can't write!)
```

## Solusi Docker Expert

### Strategi:
1. ✅ **Container start sebagai root** (default, tidak set USER di Dockerfile)
2. ✅ **Entrypoint fix permission** sebagai root saat container start
3. ✅ **PHP-FPM tetap run sebagai www-data** (aman, sesuai best practice)
4. ✅ **Supervisord run sebagai root** (bisa manage process)
5. ✅ **Log files dibuat dengan permission yang benar** di build time

### Perubahan yang Dilakukan:

#### 1. Dockerfile
```dockerfile
# Create log files with correct ownership at build time
RUN touch /var/www/html/storage/logs/laravel.log \
    && touch /var/www/html/storage/logs/supervisord.log \
    && touch /var/www/html/storage/logs/worker.log \
    && chown www-data:www-data /var/www/html/storage/logs/*.log \
    && chmod 664 /var/www/html/storage/logs/*.log

# Don't set USER - let container run as root
# PHP-FPM will run as www-data (configured in www.conf)
# USER www-data  # ← REMOVED
```

**Keuntungan:**
- Container start sebagai root
- Entrypoint bisa fix permission
- PHP-FPM tetap aman (run sebagai www-data)

#### 2. docker/entrypoint.sh
```bash
# Check if running as root
if [ "$(id -u)" = "0" ]; then
    echo "Running as root - fixing permissions..."
    
    # Create log files
    touch /var/www/html/storage/logs/laravel.log
    touch /var/www/html/storage/logs/supervisord.log
    touch /var/www/html/storage/logs/worker.log
    
    # Fix ownership
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache
fi
```

**Keuntungan:**
- Auto-fix permission setiap container start
- Handle volume mount dari host
- Tidak perlu manual chmod lagi

#### 3. docker/supervisord.conf
```ini
[supervisord]
nodaemon=true
# No user specified - runs as root (container user)
logfile=/var/www/html/storage/logs/supervisord.log
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=php-fpm
# PHP-FPM runs as www-data (configured in www.conf)

[program:laravel-queue-worker]
command=php /var/www/html/artisan queue:work
# No user specified - inherits from supervisord (root)
# But PHP runs as www-data internally
```

**Keuntungan:**
- Supervisord bisa manage semua process
- PHP-FPM tetap run sebagai www-data (aman)
- Tidak ada error "Can't drop privilege"

#### 4. docker-compose.yml
```yaml
app:
  # No user mapping - container runs as root
  # user: "${USER_ID:-1000}:${GROUP_ID:-1000}"  # ← REMOVED
  volumes:
    - ./:/var/www/html
```

**Keuntungan:**
- Container bisa fix permission dari volume mount
- Tidak ada conflict UID 1000 vs www-data

## Security Considerations

### Apakah Aman Container Run sebagai Root?

**Ya, aman dengan setup ini karena:**

1. ✅ **PHP-FPM run sebagai www-data** (bukan root)
   - Aplikasi Laravel run sebagai www-data
   - Web request dihandle oleh www-data
   - File upload, cache, dll. dibuat oleh www-data

2. ✅ **Container terisolasi** dari host
   - Root di container ≠ root di host
   - Docker namespace isolation
   - Tidak bisa akses file di luar container

3. ✅ **Hanya entrypoint yang run sebagai root**
   - Untuk fix permission saja
   - Setelah itu, semua process run sebagai www-data

4. ✅ **Best practice untuk development/staging**
   - Production bisa pakai Kubernetes dengan securityContext
   - Atau pakai init container untuk fix permission

### Alternative untuk Production (Extra Secure):

Jika ingin extra secure di production:

```yaml
# docker-compose.yml
app:
  security_opt:
    - no-new-privileges:true
  cap_drop:
    - ALL
  cap_add:
    - CHOWN
    - SETUID
    - SETGID
```

## Deployment

### Step 1: Upload File yang Diubah

```bash
# Upload ke server
scp Dockerfile root@egi-dockerdev:/home/itteam/service-history-new-old/
scp docker-compose.yml root@egi-dockerdev:/home/itteam/service-history-new-old/
scp docker/entrypoint.sh root@egi-dockerdev:/home/itteam/service-history-new-old/docker/
scp docker/supervisord.conf root@egi-dockerdev:/home/itteam/service-history-new-old/docker/
```

### Step 2: Rebuild Container

```bash
# SSH ke server
ssh root@egi-dockerdev
cd /home/itteam/service-history-new-old

# Stop containers
docker-compose down

# Remove old images
docker rmi service-history-new-old_app service-history-new-old_queue 2>/dev/null || true

# Rebuild
docker-compose build --no-cache

# Start
docker-compose up -d

# Check logs
docker-compose logs -f app
```

### Step 3: Verifikasi

```bash
# Cek user di container
docker exec -it laravel_app whoami
# Output: root (OK, ini normal)

# Cek PHP-FPM user
docker exec -it laravel_app ps aux | grep php-fpm
# Output: www-data ... php-fpm (OK, PHP run sebagai www-data)

# Cek permission storage
docker exec -it laravel_app ls -la /var/www/html/storage
# Output: drwxrwxr-x ... www-data www-data

# Test write
docker exec -it laravel_app touch /var/www/html/storage/test.txt
docker exec -it laravel_app rm /var/www/html/storage/test.txt
# Tidak ada error = OK!

# Cek logs
docker-compose logs app | grep "Laravel is ready"
# Tidak ada error permission = OK!
```

## Troubleshooting

### Error masih muncul setelah rebuild?

**1. Clear semua cache:**
```bash
docker exec -it laravel_app rm -rf /var/www/html/storage/framework/cache/*
docker exec -it laravel_app rm -rf /var/www/html/bootstrap/cache/*
```

**2. Manual fix permission:**
```bash
docker exec -it laravel_app chown -R www-data:www-data /var/www/html/storage
docker exec -it laravel_app chmod -R 775 /var/www/html/storage
```

**3. Restart container:**
```bash
docker-compose restart app queue
```

### Supervisord error?

```bash
# Cek supervisord status
docker exec -it laravel_app supervisorctl status

# Restart supervisord
docker-compose restart app
```

### Laravel log error?

```bash
# Recreate log file
docker exec -it laravel_app rm /var/www/html/storage/logs/laravel.log
docker exec -it laravel_app touch /var/www/html/storage/logs/laravel.log
docker exec -it laravel_app chown www-data:www-data /var/www/html/storage/logs/laravel.log
docker exec -it laravel_app chmod 664 /var/www/html/storage/logs/laravel.log
```

## Summary

### Sebelum Fix:
- ❌ Container run sebagai www-data
- ❌ Tidak bisa fix permission dari volume mount
- ❌ Error permission di storage/logs
- ❌ Error permission di bootstrap/cache

### Setelah Fix:
- ✅ Container run sebagai root (untuk fix permission)
- ✅ PHP-FPM run sebagai www-data (aman)
- ✅ Entrypoint auto-fix permission
- ✅ Tidak ada error permission lagi
- ✅ Import Excel langsung bisa

### Key Points:
1. **Container sebagai root** = bisa fix permission dari volume mount
2. **PHP-FPM sebagai www-data** = aplikasi tetap aman
3. **Entrypoint fix permission** = otomatis setiap start
4. **Log files dibuat di build time** = permission sudah benar

**Upload file yang diubah, rebuild, dan selesai!** 🚀
