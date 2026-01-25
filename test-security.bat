@echo off
echo ========================================
echo   SECURITY TEST SCRIPT
echo ========================================
echo.

set SERVER_URL=http://localhost:8000

echo Testing security measures...
echo.

echo [Test 1] Checking if .env is accessible...
curl -s -o nul -w "%%{http_code}" %SERVER_URL%/.env > temp.txt
set /p STATUS=<temp.txt
if "%STATUS%"=="404" (
    echo [PASS] .env returns 404
) else if "%STATUS%"=="403" (
    echo [PASS] .env returns 403
) else (
    echo [FAIL] .env returns %STATUS% - Should be 403 or 404!
)
del temp.txt
echo.

echo [Test 2] Checking if composer.json is accessible...
curl -s -o nul -w "%%{http_code}" %SERVER_URL%/composer.json > temp.txt
set /p STATUS=<temp.txt
if "%STATUS%"=="404" (
    echo [PASS] composer.json returns 404
) else if "%STATUS%"=="403" (
    echo [PASS] composer.json returns 403
) else (
    echo [FAIL] composer.json returns %STATUS% - Should be 403 or 404!
)
del temp.txt
echo.

echo [Test 3] Checking security headers...
echo Fetching headers from %SERVER_URL%...
curl -I %SERVER_URL% 2>nul | findstr /C:"X-Frame-Options" /C:"X-Content-Type-Options" /C:"Referrer-Policy"
echo.

echo [Test 4] Checking APP_DEBUG setting...
findstr /C:"APP_DEBUG=true" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [WARN] APP_DEBUG is set to true - Should be false in production!
) else (
    echo [PASS] APP_DEBUG is not set to true
)
echo.

echo [Test 5] Checking if APP_KEY is set...
findstr /C:"APP_KEY=base64:" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [PASS] APP_KEY is set
) else (
    echo [FAIL] APP_KEY is not set - Run: php artisan key:generate
)
echo.

echo [Test 6] Checking if .env is in git...
git ls-files .env >nul 2>&1
if %errorlevel% equ 0 (
    echo [FAIL] .env is tracked by git - Run: git rm --cached .env
) else (
    echo [PASS] .env is not tracked by git
)
echo.

echo [Test 7] Checking Laravel logs...
if exist "storage\logs\laravel.log" (
    echo [INFO] Last 5 log entries:
    powershell -Command "Get-Content storage\logs\laravel.log -Tail 5"
) else (
    echo [INFO] No log file found yet
)
echo.

echo ========================================
echo   SECURITY TEST COMPLETE
echo ========================================
echo.
echo Review the results above.
echo For detailed fixes, see SECURITY_FIXES_APPLIED.md
echo.
pause
