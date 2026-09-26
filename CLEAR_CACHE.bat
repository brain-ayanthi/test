@echo off
echo Clearing ALL Laravel caches...
cd /d "%~dp0"
if exist bootstrap\cache\config.php del /F /Q bootstrap\cache\config.php
if exist bootstrap\cache\routes-v7.php del /F /Q bootstrap\cache\routes-v7.php
if exist bootstrap\cache\events.php del /F /Q bootstrap\cache\events.php
php artisan optimize:clear
echo.
echo ============================================
echo   Cache cleared! Now run:
echo   php artisan serve
echo ============================================
pause
