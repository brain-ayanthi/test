@echo off
REM ====================================================================
REM  Fix "Route [treatments.update] not defined" error
REM ====================================================================

echo.
echo Clearing ALL Laravel caches...
cd /d "%~dp0"

echo.
echo [1/4] Stopping php artisan serve...
taskkill /F /IM php.exe 2>nul
timeout /t 1 /nobreak >nul

echo [2/4] Deleting cached routes/config/views...
if exist "bootstrap\cache\routes-v7.php" del /F /Q "bootstrap\cache\routes-v7.php"
if exist "bootstrap\cache\routes.php" del /F /Q "bootstrap\cache\routes.php"
if exist "bootstrap\cache\config.php" del /F /Q "bootstrap\cache\config.php"
if exist "bootstrap\cache\events.php" del /F /Q "bootstrap\cache\events.php"
if exist "bootstrap\cache\services.php" del /F /Q "bootstrap\cache\services.php"
if exist "bootstrap\cache\packages.php" del /F /Q "bootstrap\cache\packages.php"
if exist "storage\framework\views" del /F /Q "storage\framework\views\*.php" 2>nul

echo [3/4] Running artisan cache:clear...
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

echo.
echo [4/4] Verifying treatments.update route exists...
php artisan route:list --name=treatments

echo.
echo ============================================
echo   DONE! Now start the server:
echo     php artisan serve
echo.
echo   Then refresh the page with Ctrl+F5
echo ============================================
pause
