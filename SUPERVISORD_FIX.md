# Supervisord Error Fix - "Can't drop privilege as nonroot user"

## Error
```
Error: Can't drop privilege as nonroot user
For help, use /usr/bin/supervisord -h
```

## Penyebab

Error ini terjadi karena:

1. **Docker container berjalan sebagai non-root user**
   - Di `docker-compose.yml`, service `app` dan `queue` menggunakan:
     ```yaml
     user: "${USER_ID:-1000}:${GROUP_ID:-1000}"
     ```
   - Ini berarti container berjalan dengan UID/GID 1000 (bukan root)

2. **Supervisord config mencoba drop privilege**
   - File `docker/supervisord.conf` memiliki:
     ```ini
     [supervisord]
     user=root  # ❌ Ini menyebabkan error
     
     [program:laravel-queue-worker]
     user=www-data  # ❌ Ini juga menyebabkan error
     ```
   - Supervisord mencoba switch ke user `root` atau `www-data`, tapi container sudah berjalan sebagai non-root user
   - Non-root user tidak bisa drop privilege atau switch user

## Solusi

### 1. Hapus `user=root` dari section `[supervisord]`

**Sebelum:**
```ini
[supervisord]
nodaemon=true
user=root  # ❌ Hapus baris ini
logfile=/var/www/html/storage/logs/supervisord.log
pidfile=/var/run/supervisord.pid
```

**Sesudah:**
```ini
[supervisord]
nodaemon=true
logfile=/var/www/html/storage/logs/supervisord.log
pidfile=/var/run/supervisord.pid
```

### 2. Hapus `user=www-data` dari section `[program:laravel-queue-worker]`

**Sebelum:**
```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=1 --tries=3 --max-time=3600 --memory=512
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data  # ❌ Hapus baris ini
numprocs=8
```

**Sesudah:**
```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=1 --tries=3 --max-time=3600 --memory=512
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=8
```

## Rebuild Docker Container

Setelah mengubah `docker/supervisord.conf`, rebuild container:

```bash
# Stop containers
docker-compose down

# Rebuild dan start
docker-compose up -d --build

# Atau gunakan script rebuild
docker-rebuild.bat
```

## Verifikasi

Cek log untuk memastikan tidak ada error:

```bash
# Cek log app container
docker-compose logs app

# Cek log queue container
docker-compose logs queue

# Cek status supervisord
docker exec -it laravel_app supervisorctl status
```

Output yang benar:
```
laravel-queue-worker:laravel-queue-worker_00   RUNNING   pid 123, uptime 0:01:23
laravel-queue-worker:laravel-queue-worker_01   RUNNING   pid 124, uptime 0:01:23
...
php-fpm                                        RUNNING   pid 122, uptime 0:01:23
```

## Catatan

- Karena container sudah berjalan dengan user yang sesuai (dari docker-compose.yml), tidak perlu lagi specify user di supervisord config
- Semua process akan berjalan dengan user yang sama dengan container (USER_ID:GROUP_ID)
- Ini lebih aman dan sesuai dengan best practice Docker

## File yang Diubah

- `docker/supervisord.conf`
  - Menghapus `user=root` dari section `[supervisord]`
  - Menghapus `user=www-data` dari section `[program:laravel-queue-worker]`
