# Storage Permission Error Fix - Import Excel

## Error
```
Permission denied: /var/www/html/storage/...
```

Error ini terjadi saat import Excel karena Laravel tidak bisa menulis ke direktori `/storage`.

## Penyebab

1. **Docker container berjalan dengan user non-root** (UID:GID 1000)
2. **Storage directory tidak memiliki permission yang tepat** untuk user tersebut
3. **File upload Excel perlu ditulis ke storage/app** atau storage/framework

## Solusi

### Solusi 1: Jalankan Script Fix Permission (Recommended)

**Di server yang menjalankan Docker:**

```bash
# Linux/Mac
bash fix-storage-permission.sh

# Windows (jika ada akses ke server)
# Upload file fix-storage-permission.sh ke server, lalu jalankan
```

Script ini akan:
- Set ownership ke www-data:www-data
- Set permission direktori ke 775
- Set permission file ke 664

### Solusi 2: Manual Fix via Docker Exec

**Di server Docker:**

```bash
# Fix ownership dan permission
docker exec -it laravel_app sh -c "
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache
"

# Atau lebih permissive (jika masih error)
docker exec -it laravel_app chmod -R 777 /var/www/html/storage
```

### Solusi 3: Rebuild Container (Permanent Fix)

File `docker/entrypoint.sh` sudah diupdate untuk otomatis fix permission saat container start.

**Di server Docker:**

```bash
# Stop containers
docker-compose down

# Rebuild dengan entrypoint yang baru
docker-compose up -d --build

# Atau gunakan script
docker-rebuild.bat
```

## Verifikasi Permission

**Cek permission di dalam container:**

```bash
docker exec -it laravel_app ls -la /var/www/html/storage
```

Output yang benar:
```
drwxrwxr-x  storage
drwxrwxr-x  app
drwxrwxr-x  framework
drwxrwxr-x  logs
```

**Cek apakah Laravel bisa menulis:**

```bash
docker exec -it laravel_app sh -c "
    touch /var/www/html/storage/test.txt && 
    echo 'Write test successful!' && 
    rm /var/www/html/storage/test.txt
"
```

## Struktur Permission yang Benar

```
storage/
├── app/              (775)
│   ├── public/       (775)
│   └── ...
├── framework/        (775)
│   ├── cache/        (775)
│   ├── sessions/     (775)
│   ├── testing/      (775)
│   └── views/        (775)
└── logs/             (775)
    └── laravel.log   (664)
```

## Troubleshooting

### Masih Error Setelah Fix Permission?

1. **Cek user yang menjalankan PHP-FPM:**
   ```bash
   docker exec -it laravel_app ps aux | grep php-fpm
   ```

2. **Cek user di docker-compose.yml:**
   ```yaml
   app:
     user: "${USER_ID:-1000}:${GROUP_ID:-1000}"
   ```

3. **Pastikan USER_ID dan GROUP_ID sesuai:**
   ```bash
   # Di server
   echo $USER_ID
   echo $GROUP_ID
   
   # Atau cek di .env.docker
   cat .env.docker | grep USER_ID
   ```

4. **Gunakan permission 777 (temporary):**
   ```bash
   docker exec -it laravel_app chmod -R 777 /var/www/html/storage
   ```

### Error: "Operation not permitted"

Jika mendapat error "Operation not permitted", container mungkin tidak punya akses untuk chown:

```bash
# Solusi: Gunakan chmod saja (tanpa chown)
docker exec -it laravel_app chmod -R 777 /var/www/html/storage
```

### Error Persist Setelah Restart Container

Tambahkan volume mount di `docker-compose.yml` untuk persist permission:

```yaml
app:
  volumes:
    - ./storage:/var/www/html/storage
```

**CATATAN:** Ini bisa menyebabkan masalah lain, lebih baik fix di entrypoint.sh

## Best Practice

1. **Gunakan entrypoint.sh** untuk otomatis fix permission saat container start
2. **Set permission 775** untuk direktori (bukan 777)
3. **Set permission 664** untuk file (bukan 666)
4. **Gunakan www-data:www-data** sebagai owner jika memungkinkan

## File yang Diubah

- `docker/entrypoint.sh` - Menambahkan auto-fix permission saat container start
- `fix-storage-permission.sh` - Script untuk manual fix permission (Linux/Mac)
- `fix-storage-permission.bat` - Script untuk manual fix permission (Windows)

## Cara Menjalankan di Server Remote

**Via SSH:**

```bash
# Login ke server
ssh user@your-server.com

# Masuk ke direktori project
cd /path/to/laravel-project

# Jalankan fix
docker exec -it laravel_app chmod -R 775 /var/www/html/storage

# Atau rebuild container
docker-compose down
docker-compose up -d --build
```

**Via Script Upload:**

1. Upload `fix-storage-permission.sh` ke server
2. SSH ke server
3. Jalankan: `bash fix-storage-permission.sh`
