@echo off
echo =========================================
echo Fix Laravel Storage Permissions
echo =========================================
echo.

set CONTAINER_NAME=laravel_app

echo Fixing permissions for storage directory...
echo.

REM Fix permissions inside container
docker exec -it %CONTAINER_NAME% sh -c "chown -R www-data:www-data /var/www/html/storage && chown -R www-data:www-data /var/www/html/bootstrap/cache && find /var/www/html/storage -type d -exec chmod 775 {} \; && find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; && find /var/www/html/storage -type f -exec chmod 664 {} \; && find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \; && echo Permissions fixed successfully!"

echo.
echo =========================================
echo Done!
echo =========================================
echo.
echo If you still have permission issues, try:
echo   docker exec -it laravel_app chmod -R 777 /var/www/html/storage
echo.
pause
