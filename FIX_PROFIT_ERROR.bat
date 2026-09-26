@echo off
REM ====================================================================
REM  Fix "Unknown column 'profit'" error
REM ====================================================================

echo.
echo Stopping server and clearing ALL caches...
cd /d "%~dp0"

taskkill /F /IM php.exe 2>nul
timeout /t 1 /nobreak >nul

echo.
echo Deleting ALL compiled views...
if exist "storage\framework\views" del /F /Q "storage\framework\views\*.php" 2>nul

echo Deleting bootstrap cache...
if exist "bootstrap\cache" del /F /Q "bootstrap\cache\*.php" 2>nul

echo.
echo Running artisan clear commands...
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo.
echo ============================================
echo   DONE!
echo.
echo   IMPORTANT: Make sure you replaced this file:
echo     app\Services\ReportService.php
echo   from the latest clinicms-full.zip
echo.
echo   Then start: php artisan serve
echo   And hard-refresh browser (Ctrl+F5)
echo ============================================
pause
