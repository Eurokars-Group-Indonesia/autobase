# Quick Fix Commands - Storage Permission

## Untuk Server Remote (via SSH)

### 1. Login ke Server
```bash
ssh user@your-server-ip
```

### 2. Masuk ke Direktori Project
```bash
cd /path/to/your/laravel-project
```

### 3. Fix Permission (Pilih salah satu)

#### Option A: Quick Fix (Recommended)
```bash
docker exec -it laravel_app chmod -R 775 /var/www/html/storage
docker exec -it laravel_app chmod -R 775 /var/www/html/bootstrap/cache
```

#### Option B: Full Fix dengan Ownership
```bash
docker exec -it laravel_app sh -c "
    chown -R www-data:www-data /var/www/html/storage && 
    chown -R www-data:www-data /var/www/html/bootstrap/cache && 
    chmod -R 775 /var/www/html/storage && 
    chmod -R 775 /var/www/html/bootstrap/cache
"
```

#### Option C: Permissive Fix (jika masih error)
```bash
docker exec -it laravel_app chmod -R 777 /var/www/html/storage
```

### 4. Verifikasi
```bash
# Cek permission
docker exec -it laravel_app ls -la /var/www/html/storage

# Test write
docker exec -it laravel_app touch /var/www/html/storage/test.txt
docker exec -it laravel_app rm /var/www/html/storage/test.txt
```

### 5. Restart Container (Optional)
```bash
docker-compose restart app
```

---

## Untuk Permanent Fix

### Update Entrypoint dan Rebuild

File `docker/entrypoint.sh` sudah diupdate. Rebuild container:

```bash
# Di server
cd /path/to/your/laravel-project

# Stop containers
docker-compose down

# Rebuild
docker-compose up -d --build

# Cek logs
docker-compose logs -f app
```

---

## One-Liner Commands

### Fix Permission (Copy-Paste Ready)
```bash
docker exec -it laravel_app sh -c "chmod -R 775 /var/www/html/storage && chmod -R 775 /var/www/html/bootstrap/cache && echo 'Permission fixed!'"
```

### Fix Permission + Ownership
```bash
docker exec -it laravel_app sh -c "chown -R www-data:www-data /var/www/html/storage && chown -R www-data:www-data /var/www/html/bootstrap/cache && chmod -R 775 /var/www/html/storage && chmod -R 775 /var/www/html/bootstrap/cache && echo 'Permission and ownership fixed!'"
```

### Permissive Fix (777)
```bash
docker exec -it laravel_app sh -c "chmod -R 777 /var/www/html/storage && echo 'Permissive permission set!'"
```

---

## Troubleshooting

### Container Name Berbeda?

Cek nama container:
```bash
docker ps
```

Ganti `laravel_app` dengan nama container yang sesuai:
```bash
docker exec -it YOUR_CONTAINER_NAME chmod -R 775 /var/www/html/storage
```

### Masih Error "Permission Denied"?

1. **Cek user yang menjalankan container:**
   ```bash
   docker exec -it laravel_app whoami
   docker exec -it laravel_app id
   ```

2. **Cek permission saat ini:**
   ```bash
   docker exec -it laravel_app ls -la /var/www/html/storage
   ```

3. **Gunakan 777 (temporary):**
   ```bash
   docker exec -it laravel_app chmod -R 777 /var/www/html/storage
   ```

4. **Restart container:**
   ```bash
   docker-compose restart app
   ```

### Error "Operation not permitted"?

Container tidak punya akses untuk chown. Gunakan chmod saja:
```bash
docker exec -it laravel_app chmod -R 777 /var/www/html/storage
```

---

## Untuk Windows Users (Akses ke Server Linux)

### Via PuTTY atau PowerShell SSH

1. **Connect ke server:**
   ```powershell
   ssh user@server-ip
   ```

2. **Run fix command:**
   ```bash
   docker exec -it laravel_app chmod -R 775 /var/www/html/storage
   ```

### Via WinSCP + PuTTY

1. Upload file `fix-storage-permission.sh` ke server
2. SSH ke server via PuTTY
3. Run:
   ```bash
   bash fix-storage-permission.sh
   ```

---

## Summary

**Paling Simple (Copy-Paste ke SSH):**

```bash
docker exec -it laravel_app chmod -R 775 /var/www/html/storage && docker exec -it laravel_app chmod -R 775 /var/www/html/bootstrap/cache && echo "Done!"
```

**Jika masih error, gunakan 777:**

```bash
docker exec -it laravel_app chmod -R 777 /var/www/html/storage && echo "Done!"
```

**Untuk permanent fix, rebuild container:**

```bash
docker-compose down && docker-compose up -d --build
```
