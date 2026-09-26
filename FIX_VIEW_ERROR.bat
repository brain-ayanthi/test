@echo off
REM ====================================================================
REM  QUICK FIX for "View path not found" error
REM  Run this in D:\xampp\htdocs\clinicms
REM ====================================================================

echo.
echo Fixing "View path not found" error...
echo.

cd /d "%~dp0"

REM 1. Stop running server
taskkill /F /IM php.exe 2>nul

REM 2. Create the missing view compiled path (the actual cause)
echo Creating compiled views folder...
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"

REM 3. Delete cached config (it may point to the now-missing path)
echo Deleting old cached config...
if exist "bootstrap\cache\config.php" del /F /Q "bootstrap\cache\config.php"
if exist "bootstrap\cache\routes-v7.php" del /F /Q "bootstrap\cache\routes-v7.php"
if exist "bootstrap\cache\events.php" del /F /Q "bootstrap\cache\events.php"
if exist "bootstrap\cache\services.php" del /F /Q "bootstrap\cache\services.php"
if exist "bootstrap\cache\packages.php" del /F /Q "bootstrap\cache\packages.php"

REM 4. Fix permissions
icacls "storage" /grant "Everyone:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "bootstrap\cache" /grant "Everyone:(OI)(CI)F" /T /C /Q >nul 2>&1

REM 5. Clear caches (now should work because path exists)
echo Clearing caches...
php artisan optimize:clear

echo.
echo ============================================
echo   Done! Now run:  php artisan serve
echo ============================================
pause
