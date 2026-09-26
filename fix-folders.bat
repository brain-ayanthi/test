@echo off
REM ====================================================================
REM  Fix "Please provide a valid cache path" error
REM  Run this in D:\xampp\htdocs\clinicms if you see that error.
REM ====================================================================

echo Creating required folders...

cd /d "%~dp0"

if not exist "bootstrap\cache"              mkdir "bootstrap\cache"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions"   mkdir "storage\framework\sessions"
if not exist "storage\framework\views"      mkdir "storage\framework\views"
if not exist "storage\framework\testing"    mkdir "storage\framework\testing"
if not exist "storage\app\public"           mkdir "storage\app\public"
if not exist "storage\app\private"          mkdir "storage\app\private"
if not exist "storage\logs"                 mkdir "storage\logs"

echo Setting permissions...
icacls "bootstrap\cache" /grant "%USERNAME%:(OI)(CI)F" /T /Q >nul 2>&1
icacls "storage" /grant "%USERNAME%:(OI)(CI)F" /T /Q >nul 2>&1

echo Clearing old caches...
php artisan cache:clear 2>nul
php artisan config:clear 2>nul
php artisan route:clear 2>nul
php artisan view:clear 2>nul

echo.
echo Done! Now run:  php artisan serve
echo Then open: http://127.0.0.1:8000
pause
