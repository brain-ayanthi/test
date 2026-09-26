#!/usr/bin/env bash
# ==========================================================
#  ClinicMS - Linux / macOS First-Time Setup
#  Usage:  chmod +x setup.sh && ./setup.sh
# ==========================================================
set -e

echo ""
echo "============================================"
echo "  ClinicMS - First Time Setup (Linux/macOS)"
echo "============================================"
echo ""

# 1. Create required directories
echo "[1/6] Creating required directories..."
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/app/public
mkdir -p storage/app/private
mkdir -p storage/logs
echo "     Done."

# 2. Set permissions
echo "[2/6] Setting folder permissions..."
chmod -R 775 storage bootstrap/cache
echo "     Done."

# 3. Composer install
echo "[3/6] Checking PHP dependencies..."
if [ ! -f "vendor/autoload.php" ]; then
    echo "     Installing Composer dependencies..."
    composer install
else
    echo "     Vendor folder already exists, skipping."
fi

# 4. Key generate
echo "[4/6] Generating application key..."
php artisan key:generate --force

# 5. Storage link
echo "[5/6] Creating storage link..."
php artisan storage:link

# 6. Database reminder
echo ""
echo "[6/6] Database setup:"
echo "     If you have NOT imported the database yet:"
echo "       1. mysql -u root -p -e \"CREATE DATABASE clinicms CHARACTER SET utf8mb4;\""
echo "       2. mysql -u root -p clinicms < database/sql/clinicms_database.sql"
echo "     OR run: php artisan migrate --seed"
echo ""
echo "============================================"
echo "  Setup complete!"
echo "  Start with: php artisan serve"
echo "  Login: admin@clinicms.test / password"
echo "============================================"
