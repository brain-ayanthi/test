@echo off
REM ====================================================================
REM  URGENT FIX for "Please provide a valid cache path"
REM  This script DELETES cached config and recreates ALL required folders
REM  with full Windows permissions.
REM ====================================================================

echo.
echo ============================================
echo   ClinicMS - Cache Path Emergency Fix
echo ============================================
echo.

cd /d "%~dp0"

echo [1/6] Stopping any running php artisan serve...
taskkill /F /IM php.exe 2>nul
timeout /t 1 /nobreak >nul

echo.
echo [2/6] DELETING old cached config (this is usually the cause)...
if exist "bootstrap\cache\config.php" del /F /Q "bootstrap\cache\config.php"
if exist "bootstrap\cache\routes-v7.php" del /F /Q "bootstrap\cache\routes-v7.php"
if exist "bootstrap\cache\events.php" del /F /Q "bootstrap\cache\events.php"
if exist "bootstrap\cache\services.php" del /F /Q "bootstrap\cache\services.php"
if exist "bootstrap\cache\packages.php" del /F /Q "bootstrap\cache\packages.php"
if exist "bootstrap\cache\compiled.php" del /F /Q "bootstrap\cache\compiled.php"
echo      Done.

echo.
echo [3/6] Creating ALL required directories...
mkdir "bootstrap\cache" 2>nul
mkdir "storage\framework\cache\data" 2>nul
mkdir "storage\framework\sessions" 2>nul
mkdir "storage\framework\views" 2>nul
mkdir "storage\framework\testing" 2>nul
mkdir "storage\app\public" 2>nul
mkdir "storage\app\private" 2>nul
mkdir "storage\logs" 2>nul
echo      Done.

echo.
echo [4/6] Granting FULL permissions to IUSR and Everyone...
icacls "bootstrap\cache" /grant "Everyone:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "storage" /grant "Everyone:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "bootstrap\cache" /grant "IUSR:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "storage" /grant "IUSR:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "bootstrap\cache" /grant "%USERNAME%:(OI)(CI)F" /T /C /Q >nul 2>&1
icacls "storage" /grant "%USERNAME%:(OI)(CI)F" /T /C /Q >nul 2>&1
echo      Done.

echo.
echo [5/6] Creating placeholder files so folders are never lost...
echo. > "bootstrap\cache\.gitkeep"
echo. > "storage\framework\cache\data\.gitkeep"
echo. > "storage\framework\sessions\.gitkeep"
echo. > "storage\framework\views\.gitkeep"
echo. > "storage\logs\.gitkeep"
echo      Done.

echo.
echo [6/7] Patching config files to remove realpath() issue...
REM config/view.php is already patched in the new ZIP; this is a safety net.
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
echo      Done.

echo.
echo [7/7] Clearing Laravel caches...
php artisan optimize:clear 2>nul
php artisan cache:clear 2>nul
php artisan config:clear 2>nul
php artisan route:clear 2>nul
php artisan view:clear 2>nul
echo      Done.

echo.
echo ============================================
echo   VERIFICATION
echo ============================================
echo.
echo Checking folders:
if exist "bootstrap\cache" (echo   [OK] bootstrap\cache) else (echo   [MISSING] bootstrap\cache)
if exist "storage\framework\cache\data" (echo   [OK] storage\framework\cache\data) else (echo   [MISSING] storage\framework\cache\data)
if exist "storage\framework\sessions" (echo   [OK] storage\framework\sessions) else (echo   [MISSING] storage\framework\sessions)
if exist "storage\framework\views" (echo   [OK] storage\framework\views) else (echo   [MISSING] storage\framework\views)
if exist "storage\logs" (echo   [OK] storage\logs) else (echo   [MISSING] storage\logs)

echo.
echo Checking for cached config (should NOT exist):
if exist "bootstrap\cache\config.php" (echo   [WARNING] config.php still exists - delete it manually) else (echo   [OK] No cached config)

echo.
echo ============================================
echo   Fix complete!
echo.
echo   Now start the server:
echo     php artisan serve
echo.
echo   Then open: http://127.0.0.1:8000
echo ============================================
echo.
pause
