# 🗄️ SQL File භාවිතා කර Database එක සෑදීම

ඔබට `php artisan migrate --seed` නොවෙන විට, මෙම SQL file එක සෘජුවම MySQL වෙත import කළ හැකිය.

## 📂 File එක
**Location:** `database/sql/clinicms_database.sql`

---

## ක්‍රමය 1: phpMyAdmin හරහා (පහසුම)

1. **phpMyAdmin** විවෘත කරන්න (`http://localhost/phpmyadmin`)
2. ඉහළ ඇති **"New"** ඔබන්න
3. Database name ලෙස **`clinicms`** යොදන්න
4. Collation ලෙස **`utf8mb4_unicode_ci`** තෝරන්න
5. **"Create"** ඔබන්න
6. දැන් ඉහළ menu එකේ **"Import"** ටැබ් එක ඔබන්න
7. **"Choose File"** ඔබා `database/sql/clinicms_database.sql` තෝරන්න
8. පහළ **"Go"** ඔබන්න
9. "Import has been successfully finished" පණිවිඩය එන තෙක් රැඳී සිටින්න

දැන් සියලුම tables, demo products, doctors, patients සහ admin user සෑදී ඇත.

---

## ක්‍රමය 2: MySQL Command Line හරහා

```bash
# Database එක සාදන්න (මෙය SQL file එක තුළම ඇත, නමුත් කලින් සෑදීම ආරක්ෂිතයි)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS clinicms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# SQL file එක import කරන්න
mysql -u root -p clinicms < database/sql/clinicms_database.sql
```

ඔබේ password එක ඉල්ලා සිටින විට එය යොදන්න.

---

## ක්‍රමය 3: XAMPP / WAMP / Laragon හි

### XAMPP:
1. XAMPP Control Panel එකේ Apache + MySQL start කරන්න
2. phpMyAdmin විවෘත කරන්න
3. උඩ ඇති ක්‍රමය 1 අනුගමනය කරන්න

### MySQL root password නැත්නම් (XAMPP default):
```bash
mysql -u root clinicms < database/sql/clinicms_database.sql
```

---

## ✅ Import කල පසු

1. `.env` file එකේ database credentials පරීක්ෂා කරන්න:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinicms
DB_USERNAME=root
DB_PASSWORD=
```

2. මෙම commands ධාවනය කරන්න:
```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan serve
```

3. `http://localhost:8000` වෙත පිවිසෙන්න

## 🔑 Login
- **Email:** `admin@clinicms.test`
- **Password:** `password`

---

## ⚠️ වැදගත් සටහන්

- SQL file එක මගින් **39 tables** සෑදේ:
  - users, roles, permissions (RBAC)
  - doctors, patients
  - categories, units, drug_types
  - products, product_batches (FEFO)
  - vendors, purchases, purchase_items, purchase_payments
  - cash_accounts, cash_transactions, cash_transfers
  - prescriptions, prescription_items, prescription_documents
  - ready_treatments, ready_treatment_items
  - sales, sale_items, sale_payments, sale_returns
  - activity_logs, jobs, cache, sessions

- එමඟින් **demo data** ද එකතු වේ:
  - 1 Admin user
  - 3 Roles (Admin, Doctor, Staff) + 12 Permissions
  - 2 Doctors
  - 3 Cash accounts (Store Cash, Bank, MFS)
  - 3 Categories, 6 Units, 8 Drug Types, 2 Vendors
  - 12 Medicines (with batches & stock)
  - 7 Patients
  - 1 Ready Treatment template (Common Fever)
  - 1 Demo prescription + sale + purchase

- නැවත import කිරීමට අවශ්‍යනම්, SQL file එකේ `DROP TABLE IF EXISTS` statements ඇති නිසා පැරණි data මකා නවතම data දමයි.

- තවත් demo data අවශ්‍යනම් මෙය ධාවනය කරන්න:
```bash
php artisan db:seed
```

## ❓ ගැටලුවක් ඇත්නම්

**"Unknown database"** ලෙස පෙන්වන්නේ නම්:
- මුලින් `clinicms` database එක අතින් සාදන්න (phpMyAdmin → New)

**"Access denied"** ලෙස පෙන්වන්නේ නම්:
- `.env` හි DB_USERNAME සහ DB_PASSWORD නිවැරදි බව පරීක්ෂා කරන්න

**"Table already exists"** ලෙස පෙන්වන්නේ නම්:
- SQL file එකේ `DROP TABLE IF EXISTS` ඇති නිසා මෙය සාමාන්‍යයෙන් සිදු නොවේ. වුවහොත් database එක drop කර නැවත සාදන්න.
