@echo off
echo ========================================
echo   SECURITY FIX SCRIPT
echo ========================================
echo.

echo [1/6] Clearing configuration cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo Done!
echo.

echo [2/6] Checking .gitignore...
findstr /C:".env" .gitignore >nul
if %errorlevel% equ 0 (
    echo .env is in .gitignore - Good!
) else (
    echo WARNING: .env not found in .gitignore!
)
echo.

echo [3/6] Checking if .env is tracked by git...
git ls-files .env >nul 2>&1
if %errorlevel% equ 0 (
    echo WARNING: .env is tracked by git!
    echo Run: git rm --cached .env
    echo Then: git commit -m "Remove .env from git"
) else (
    echo .env is not tracked - Good!
)
echo.

echo [4/6] Checking APP_KEY...
findstr /C:"APP_KEY=" .env >nul
if %errorlevel% equ 0 (
    echo APP_KEY found in .env
) else (
    echo WARNING: APP_KEY not set!
    echo Run: php artisan key:generate
)
echo.

echo [5/6] Checking APP_DEBUG setting...
findstr /C:"APP_DEBUG=true" .env >nul
if %errorlevel% equ 0 (
    echo WARNING: APP_DEBUG is set to true!
    echo For production, set APP_DEBUG=false
) else (
    echo APP_DEBUG check passed
)
echo.

echo [6/6] Optimizing application...
php artisan optimize
echo Done!
echo.

echo ========================================
echo   SECURITY CHECK COMPLETE
echo ========================================
echo.
echo Next steps:
echo 1. Review SECURITY_AUDIT_REPORT.md
echo 2. Follow SECURITY_QUICK_FIX.md
echo 3. Change database password
echo 4. Test security headers
echo.
pause
