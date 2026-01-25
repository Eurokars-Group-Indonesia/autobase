@echo off
echo ========================================
echo   SECURITY HEADERS TEST
echo ========================================
echo.

set SERVER_URL=http://localhost:8000

echo Testing HTTP Security Headers...
echo Server: %SERVER_URL%
echo.

echo Fetching headers...
echo.

curl -I %SERVER_URL% 2>nul | findstr /C:"X-Frame-Options" /C:"X-Content-Type-Options" /C:"X-XSS-Protection" /C:"Referrer-Policy" /C:"Permissions-Policy" /C:"Content-Security-Policy"

echo.
echo ========================================
echo.

echo Expected headers:
echo - X-Frame-Options: DENY
echo - X-Content-Type-Options: nosniff
echo - X-XSS-Protection: 1; mode=block
echo - Referrer-Policy: strict-origin-when-cross-origin
echo - Permissions-Policy: geolocation=(), microphone=(), camera=()
echo - Content-Security-Policy: (should be present)
echo.

echo If you see these headers above, security is working!
echo If not, make sure:
echo 1. Application is running (php artisan serve)
echo 2. Cache is cleared (php artisan optimize)
echo 3. Middleware is registered in bootstrap/app.php
echo.

echo To test in browser:
echo 1. Open %SERVER_URL%
echo 2. Press F12 (Developer Tools)
echo 3. Go to Network tab
echo 4. Refresh page
echo 5. Click on first request
echo 6. Check Response Headers
echo.

pause
