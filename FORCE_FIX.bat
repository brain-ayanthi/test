@echo off
REM ====================================================================
REM  Force-fix: delete ALL cached views/config and restart
REM  Run this if you still see old errors after updating files.
REM ====================================================================

echo.
echo ============================================
echo   FORCE FIX - Clearing ALL cached files
echo ============================================
echo.

cd /d "%~dp0"

echo [1/5] Stopping any running php artisan serve...
taskkill /F /IM php.exe 2>nul
timeout /t 1 /nobreak >nul

echo.
echo [2/5] Deleting ALL compiled Blade views...
if exist "storage\framework\views" (
    del /F /Q "storage\framework\views\*" 2>nul
    echo      Deleted compiled views.
) else (
    mkdir "storage\framework\views"
)

echo.
echo [3/5] Deleting bootstrap cache (config/routes/events/services)...
if exist "bootstrap\cache" (
    del /F /Q "bootstrap\cache\*.php" 2>nul
    echo      Cleared bootstrap cache.
) else (
    mkdir "bootstrap\cache"
)

echo.
echo [4/5] Ensuring all storage folders exist...
mkdir "storage\framework\cache\data" 2>nul
mkdir "storage\framework\sessions" 2>nul
mkdir "storage\logs" 2>nul
mkdir "storage\app\public" 2>nul
icacls "storage" /grant "Everyone:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "bootstrap\cache" /grant "Everyone:(OI)(CI)F" /T /C /Q >nul 2>&1

echo.
echo [5/5] Clearing Laravel caches via artisan...
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear

echo.
echo ============================================
echo   DONE!
echo.
echo   Now start the server:
echo     php artisan serve
echo.
echo   Then in the browser, press Ctrl+F5
echo   to force-reload without browser cache.
echo ============================================
pause
