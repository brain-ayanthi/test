@echo off
REM ==========================================================
REM  ClinicMS Performance Optimizer (Windows)
REM  Run this after deployment or whenever pages feel slow.
REM ==========================================================

echo.
echo ============================================
echo   Optimizing ClinicMS for Performance...
echo ============================================
echo.

cd /d "%~dp0"

echo [1/8] Clearing old caches...
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan event:clear

echo.
echo [2/8] Caching configuration...
php artisan config:cache

echo.
echo [3/8] Caching routes...
php artisan route:cache

echo.
echo [4/8] Caching views (Blade compilation)...
php artisan view:cache

echo.
echo [5/8] Caching events...
php artisan event:cache

echo.
echo [6/8] Optimizing class autoloader (Composer)...
composer dump-autoload --optimize --no-dev 2>nul
if errorlevel 1 (
    echo      (Composer not in PATH, skipping autoloader optimization)
)

echo.
echo [7/8] Running database optimizations...
php artisan optimize

echo.
echo [8/8] Clearing OPcache if available...
php artisan opcache:clear 2>nul

echo.
echo ============================================
echo   Optimization complete!
echo.
echo   IMPORTANT: For best speed in production:
echo   - Set APP_DEBUG=false in .env
echo   - Set APP_ENV=production in .env
echo   - Keep SESSION_DRIVER=file or use redis/memcached
echo   - Use PHP OPcache (enabled in php.ini)
echo.
echo   After code changes, run: php artisan optimize:clear
echo ============================================
pause
