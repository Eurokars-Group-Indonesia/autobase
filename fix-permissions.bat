@echo off
echo ========================================
echo Fix Storage Permissions
echo ========================================
echo.

echo [1/3] Fixing storage directory permissions...
docker exec laravel_app chmod -R 775 /var/www/html/storage
docker exec laravel_app chmod -R 775 /var/www/html/bootstrap/cache
echo.

echo [2/3] Creating storage subdirectories if not exist...
docker exec laravel_app mkdir -p /var/www/html/storage/framework/cache
docker exec laravel_app mkdir -p /var/www/html/storage/framework/sessions
docker exec laravel_app mkdir -p /var/www/html/storage/framework/views
docker exec laravel_app mkdir -p /var/www/html/storage/logs
docker exec laravel_app mkdir -p /var/www/html/storage/app/public
echo.

echo [3/3] Setting ownership (if needed)...
docker exec laravel_app chown -R $(id -u):$(id -g) /var/www/html/storage 2>nul
docker exec laravel_app chown -R $(id -u):$(id -g) /var/www/html/bootstrap/cache 2>nul
echo.

echo ========================================
echo Verifying permissions...
echo ========================================
docker exec laravel_app ls -la /var/www/html/storage
echo.

echo ========================================
echo Testing write access...
echo ========================================
docker exec laravel_app touch /var/www/html/storage/test-write.txt
if %ERRORLEVEL% EQU 0 (
    echo SUCCESS: Write access is working!
    docker exec laravel_app rm /var/www/html/storage/test-write.txt
) else (
    echo ERROR: Still cannot write to storage directory
)
echo.

echo ========================================
echo Permission fix completed!
echo ========================================
echo.
echo If you still have issues, try:
echo 1. Run this script again
echo 2. Restart containers: docker-compose restart
echo 3. Check .env.docker for correct USER_ID and GROUP_ID
echo.
pause
