@echo off
REM ====================================================================
REM  FIX "Unsupported cipher or incorrect key length"
REM  Replaces the invalid APP_KEY with a valid 32-byte AES-256 key
REM ====================================================================

echo.
echo ============================================
echo   Fixing APP_KEY...
echo ============================================
echo.

cd /d "%~dp0"

REM Stop running server
taskkill /F /IM php.exe 2>nul

REM Delete cached config so it doesn't override .env
if exist "bootstrap\cache\config.php" del /F /Q "bootstrap\cache\config.php"
if exist "bootstrap\cache\packages.php" del /F /Q "bootstrap\cache\packages.php"
if exist "bootstrap\cache\services.php" del /F /Q "bootstrap\cache\services.php"
if exist "bootstrap\cache\routes-v7.php" del /F /Q "bootstrap\cache\routes-v7.php"

REM Ensure required folders exist
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\logs" mkdir "storage\logs"
if not exist "bootstrap\cache" mkdir "bootstrap\cache"

echo.
echo [1] Letting Laravel generate a fresh, correctly-formatted key...
php artisan key:generate --force

if errorlevel 1 (
    echo.
    echo [2] php artisan key:generate failed. Writing key directly to .env...
    REM Use PowerShell to do the replacement (handles base64 safely)
    powershell -Command "$content = Get-Content .env; $content = $content -replace 'APP_KEY=.*', 'APP_KEY=base64:VDKMdIbu03sSaIKQC6Qn5v0MNNV1dPpec79umnXIOaY='; Set-Content .env $content"
    echo     Key written directly.
)

echo.
echo Clearing caches...
php artisan optimize:clear

echo.
echo ============================================
echo   Done! Now run:  php artisan serve
echo   Login: admin@clinicms.test / password
echo ============================================
pause
