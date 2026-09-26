# 🚀 Installation Guide / ස්ථාපනය කිරීමේ මාර්ගෝපදේශය

## අවශ්‍යතා (Requirements)
- PHP >= 8.1
- MySQL >= 5.7 / MariaDB >= 10.3
- Composer 2.x
- (Optional) Node.js & NPM (assets build සඳහා — CDN භාවිතා කරන නිසා අත්‍යවශ්‍ය නොවේ)

## පියවරින් පියවර

### 1. Project එක extract කරගන්න
```bash
unzip clinicms.zip
cd clinicms
```

### 2. PHP dependencies install කරන්න
```bash
composer install
```

### 3. Environment file එක සකසන්න
`.env` file එක දැනටමත් තිබේ — ඔබේ MySQL credentials අනුව වෙනස් කරන්න:
```env
DB_DATABASE=clinicms
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Application key generate කරන්න
```bash
php artisan key:generate
```

### 5. MySQL database එක සාදන්න
```bash
mysql -u root -p -e "CREATE DATABASE clinicms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 6. Migrations සහ seed data ධාවනය කරන්න
```bash
php artisan migrate --seed
```
මෙය මගින් සියලුම tables, demo products, doctors, patients, සහ admin user සෑදෙනු ඇත.

### 7. Storage link කරන්න
```bash
php artisan storage:link
```

### 8. Server එක start කරන්න
```bash
php artisan serve
```
දැන් `http://localhost:8000` වෙත පිවිසෙන්න.

## 🔑 Default Login
- **Email:** `admin@clinicms.test`
- **Password:** `password`

## 📁 Project Structure
```
clinicms/
├── app/
│   ├── Http/Controllers/   # Controllers
│   ├── Models/             # 28 Eloquent models
│   └── Services/           # Business logic (Stock, Prescription, Purchase, Sale, Cash, Reports)
├── database/
│   ├── migrations/         # 17 migration files
│   └── seeders/            # Demo data
├── resources/views/        # Blade templates (screenshot designs අනුව)
├── routes/web.php          # All routes
└── public/                 # Document root
```

## 🔧 Production Deployment (cPanel / VPS)
1. ZIP file එක server එකට upload කර extract කරන්න.
2. `public` folder එක document root ලෙස set කරන්න.
3. `.env` file එකේ `APP_ENV=production`, `APP_DEBUG=false` සකසන්න.
4. මෙම commands ධාවනය කරන්න:
```bash
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🖨️ Thermal Printer Setup
- Prescription save කල විට 80mm thermal PDF එකක් auto-generate වේ.
- Browser print dialog එකේ "Save as PDF" වෙනුවට thermal printer තෝරන්න.
- Paper size: 80mm (or 72mm print area).

## ❓ Troubleshooting
- **"SQLSTATE[HY000] Access denied"**: `.env` හි DB password පරීක්ෂා කරන්න.
- **"Key not set"**: `php artisan key:generate` ධාවනය කරන්න.
- **Storage permission error**: `chmod -R 775 storage bootstrap/cache`
- **Class not found**: `composer dump-autoload`
