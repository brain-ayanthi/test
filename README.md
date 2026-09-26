# Clinic & Pharmacy Management System

A self-hosted professional system for **doctor chambers, prescription management, and pharmacy operations** built with **Laravel 10, PHP 8.1+, and MySQL**.

Designed for clinics / medical diagnosis centres where multiple doctors run chambers and visit patients. Doctors can share patients and view each other's medical history (prescriptions, reports, diagnosis status).

## Features

### 1. Prescription Management
- Prescription entry & tracking with status (Active / Completed / Cancelled)
- **Patient-first workflow**: when opening the prescription page, the **Patient List & Create form is shown first** (as in `screen_Patient.png`, `screen_Patient_Create.png`)
- After selecting/creating a patient, the prescription builder auto-opens with patient **name, age, phone, Patient ID, and barcode** on the right side
- **Switch patient** if the wrong one was selected
- Old prescription / medical history visible per patient
- **Ready Treatment** templates (e.g. common fever → auto-fill fixed medicines)
- **Timing abbreviations**: OD (1×), BD/BID (2×), TDS (3×), QID (4×) — quantity auto-calculated as `timing × days` (e.g. TDS × 3 days = 9 pieces) and stock deducted automatically
- Selling price shown per medicine; **medicine cost is auto-calculated and not editable**
- **Doctor fee** is entered separately; total fee = medicine cost + doctor fee − discount
- **Thermal POS printer** output (80mm) on save
- Date-wise auto prescription numbers with barcode

### 2. Inventory Management
- Product catalog with categories, drug types, variants & pricing
- **Batch tracking with expiry dates**
- **FEFO (First Expiry First Out)** stock deduction
- Low-stock and expiring-product alerts
- Bulk upload via Excel/CSV (Maatwebsite/Excel)
- Rack / shelf location
- Real-time stock updates

### 3. Purchase Management
- Vendor management
- Purchase invoices, returns, payment tracking (Paid / Partial / Pending)
- Multiple payment methods (Cash / Bank / MFS / Cheque)
- Due-date management
- **Box → Piece conversion**: purchase in boxes/packets (e.g. 1 box = 1000 pieces), prescription/sales in pieces
- Units support different piece counts (500, 100, 50, etc.)
- Automatic stock updates on purchase
- Purchase history & reports

### 4. Patient Management
- Patient list with search
- Create / edit / delete / history
- Auto-generated Patient ID (used as barcode)

### 5. Reports & Analytics
- Sales reports (daily / weekly / monthly)
- Purchase reports
- Profit & Loss statements
- Product-wise and vendor-wise reports
- Batch stock & expiry reports
- Low-stock alerts
- Refund reports
- Cash-flow reports
- Net income

### 6. Cash / Store Management
- Multi-account: **Store Cash, Bank, MFS** balances
- Top-up and account-to-account transfers
- Transaction history
- Automatic balance updates on every sale / purchase / refund

### 7. User & Role Management (RBAC)
- Unlimited roles & permissions (Admin / Doctor / Staff)
- Multi-user / multi-staff
- Permission-based access (spatie/laravel-permission)
- User activity tracking

### Additional
- Responsive design
- Standalone **and linked** invoicing (POS + prescription-to-sale conversion)
- Patient panel (API ready for mobile app)
- Advance drug management by type (quick picker in prescription builder)
- Advance file manager for medical documents
- Barcode generation (patient code = barcode)
- Product search & quick add
- Thermal invoice printing (dompdf)

---

## Tech Stack
- Laravel 10
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Tailwind CSS 2 (CDN)
- DomPDF (thermal printing)
- Spatie Permission (RBAC)
- Maatwebsite Excel (bulk import/export)

---

## Installation

```bash
# 1. Clone / copy the project
cd clinicms

# 2. Install PHP dependencies
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure .env (DB credentials, currency LKR)
#    DB_DATABASE=clinicms
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Create MySQL database
mysql -u root -p -e "CREATE DATABASE clinicms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Run migrations & seeders (demo data + admin login)
php artisan migrate --seed

# 7. Storage link
php artisan storage:link

# 8. Start server
php artisan serve
```

Visit `http://localhost:8000`

### Default Login
- **Email:** `admin@clinicms.test`
- **Password:** `password`

---

## Key Business Logic

### Prescription Quantity Calculation
```
quantity = timing_multiplier × duration_days
OD  = 1 × days
BD  = 2 × days
TDS = 3 × days     (e.g. 3 × 3 days = 9 pieces)
QID = 4 × days
```
On save, this quantity is deducted from inventory batches using **FEFO** (earliest expiry first).

### Box → Piece Conversion
```
Purchase unit = Box (1 box = 1000 pieces)
Purchase: 5 boxes × 1000 = 5000 pieces added to stock
Prescription/sale always in pieces
```

### Fee Calculation
```
Medicine Cost = Σ(qty × unit_price)  ← not editable
Total Fee = Medicine Cost + Doctor Fee − Discount + Tax
```

---

## Project Structure
```
app/
├── Models/            # All Eloquent models (Patient, Prescription, Product, Batch...)
├── Http/Controllers/  # Web controllers
├── Services/          # Business logic
│   ├── StockService.php         # FEFO stock engine
│   ├── PrescriptionService.php  # Rx timing, save & print
│   ├── PurchaseService.php      # Box→piece conversion
│   ├── SaleService.php          # POS & Rx→Sale
│   ├── CashService.php          # Multi-account cash
│   └── ReportService.php        # All reports
database/
├── migrations/        # 16 migration files (full schema)
└── seeders/           # Demo data (doctors, products, patients)
resources/views/       # Blade views (dashboard, prescriptions, inventory...)
routes/web.php         # All web routes
```

---

## License
MIT - free to use and modify for your clinic / pharmacy.
