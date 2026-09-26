@echo off
REM ==========================================================
REM  ClinicMS - Windows XAMPP/WAMP Setup Helper
REM  Run this by double-clicking, or from command prompt
REM ==========================================================

echo.
echo ============================================
echo   ClinicMS - First Time Setup (Windows)
echo ============================================
echo.

REM 1. Create required directories (if they don't exist)
echo [1/6] Creating required directories...
if not exist "bootstrap\cache" mkdir "bootstrap\cache"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\framework\testing" mkdir "storage\framework\testing"
if not exist "storage\app\public" mkdir "storage\app\public"
if not exist "storage\app\private" mkdir "storage\app\private"
if not exist "storage\logs" mkdir "storage\logs"
echo      Done.

REM 2. Set permissions (Windows: ensure write access)
echo [2/6] Setting folder permissions...
icacls "bootstrap\cache" /grant "%USERNAME%:(OI)(CI)F" /T /Q >nul 2>&1
icacls "storage" /grant "%USERNAME%:(OI)(CI)F" /T /Q >nul 2>&1
echo      Done.

REM 3. Check if vendor folder exists
echo [3/6] Checking PHP dependencies...
if not exist "vendor\autoload.php" (
    echo      Installing Composer dependencies (this may take a few minutes)...
    composer install
    if errorlevel 1 (
        echo.
        echo [ERROR] composer install failed!
        echo Make sure Composer is installed: https://getcomposer.org/download/
        pause
        exit /b 1
    )
) else (
    echo      Vendor folder already exists, skipping.
)

REM 4. Generate app key
echo [4/6] Generating application key...
php artisan key:generate --force

REM 5. Storage link
echo [5/6] Creating storage link...
php artisan storage:link

REM 6. Reminder about database
echo.
echo [6/6] Database setup:
echo.
echo      If you have NOT imported the database yet:
echo        1. Open phpMyAdmin (http://localhost/phpmyadmin)
echo        2. Create database: clinicms
echo        3. Import: database\sql\clinicms_database.sql
echo.
echo      OR run: php artisan migrate --seed
echo.
echo ============================================
echo   Setup complete!
echo   Start the server with: php artisan serve
echo   Login: admin@clinicms.test / password
echo ============================================
echo.
pause
