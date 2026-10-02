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

```
clinicms
├─ app
│  ├─ Console
│  │  └─ Kernel.php
│  ├─ Exceptions
│  │  └─ Handler.php
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ Auth
│  │  │  │  └─ LoginController.php
│  │  │  ├─ CashController.php
│  │  │  ├─ Controller.php
│  │  │  ├─ DashboardController.php
│  │  │  ├─ DoctorController.php
│  │  │  ├─ ExpenseController.php
│  │  │  ├─ Inventory
│  │  │  │  ├─ CategoryController.php
│  │  │  │  ├─ DrugTypeController.php
│  │  │  │  └─ UnitController.php
│  │  │  ├─ PatientController.php
│  │  │  ├─ PrescriptionController - Copy.php
│  │  │  ├─ PrescriptionController.php
│  │  │  ├─ ProductController.php
│  │  │  ├─ PurchaseController.php
│  │  │  ├─ ReadyTreatmentController.php
│  │  │  ├─ ReportController.php
│  │  │  ├─ Sales
│  │  │  │  └─ SaleController.php
│  │  │  ├─ UserController.php
│  │  │  ├─ VendorController.php
│  │  │  └─ VendorPaymentController.php
│  │  ├─ Kernel.php
│  │  └─ Middleware
│  │     ├─ Authenticate.php
│  │     ├─ EncryptCookies.php
│  │     ├─ RedirectIfAuthenticated.php
│  │     └─ VerifyCsrfToken.php
│  ├─ Models
│  │  ├─ ActivityLog.php
│  │  ├─ CashAccount.php
│  │  ├─ CashTransaction.php
│  │  ├─ CashTransfer.php
│  │  ├─ Category.php
│  │  ├─ Doctor.php
│  │  ├─ DrugType.php
│  │  ├─ Expense.php
│  │  ├─ ExpenseCategory.php
│  │  ├─ Patient.php
│  │  ├─ Prescription.php
│  │  ├─ PrescriptionDocument.php
│  │  ├─ PrescriptionItem.php
│  │  ├─ PrescriptionPatient.php
│  │  ├─ PrescriptionPatientRadiology.php
│  │  ├─ Product.php
│  │  ├─ ProductBatch.php
│  │  ├─ Purchase.php
│  │  ├─ PurchaseItem.php
│  │  ├─ PurchasePayment.php
│  │  ├─ PurchaseReturn.php
│  │  ├─ PurchaseReturnItem.php
│  │  ├─ RadiologyTest.php
│  │  ├─ ReadyTreatment.php
│  │  ├─ ReadyTreatmentItem.php
│  │  ├─ Sale.php
│  │  ├─ SaleItem.php
│  │  ├─ SalePayment.php
│  │  ├─ SaleReturn.php
│  │  ├─ SaleReturnItem.php
│  │  ├─ Unit.php
│  │  ├─ User.php
│  │  ├─ Vendor.php
│  │  └─ VendorPayment.php
│  ├─ Providers
│  │  ├─ AppServiceProvider.php
│  │  ├─ AuthServiceProvider.php
│  │  ├─ EventServiceProvider.php
│  │  └─ RouteServiceProvider.php
│  └─ Services
│     ├─ CashService.php
│     ├─ PrescriptionService.php
│     ├─ PurchaseService.php
│     ├─ ReportService.php
│     ├─ SaleService.php
│     └─ StockService.php
├─ artisan
├─ bootstrap
│  ├─ app.php
│  └─ cache
│     ├─ packages.php
│     └─ services.php
├─ check-environment.php
├─ CLEAR_CACHE.bat
├─ composer.json
├─ config
│  ├─ app.php
│  ├─ auth.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ hashing.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ permission.php
│  ├─ queue.php
│  ├─ session.php
│  └─ view.php
├─ database
│  ├─ migrations
│  │  ├─ 2024_01_01_000001_create_users_table.php
│  │  ├─ 2024_01_01_000002_create_permission_tables.php
│  │  ├─ 2024_01_01_000003_create_doctors_table.php
│  │  ├─ 2024_01_01_000004_create_patients_table.php
│  │  ├─ 2024_01_01_000005_create_categories_table.php
│  │  ├─ 2024_01_01_000006_create_units_table.php
│  │  ├─ 2024_01_01_000007_create_drug_types_table.php
│  │  ├─ 2024_01_01_000008_create_products_table.php
│  │  ├─ 2024_01_01_000010_create_vendors_table.php
│  │  ├─ 2024_01_01_000011_create_purchases_table.php
│  │  ├─ 2024_01_01_000012_create_product_batches_table.php
│  │  ├─ 2024_01_01_000013_create_cash_accounts_table.php
│  │  ├─ 2024_01_01_000014_create_prescriptions_table.php
│  │  ├─ 2024_01_01_000015_create_sales_table.php
│  │  ├─ 2024_01_01_000016_create_activity_logs_table.php
│  │  ├─ 2024_01_01_000017_create_jobs_tables.php
│  │  ├─ 2024_01_01_000018_create_multi_patient_and_radiology_tables.php
│  │  ├─ 2024_01_01_000019_add_prescription_patient_to_items.php
│  │  ├─ 2024_01_01_000020_create_vendor_payments_table.php
│  │  ├─ 2026_08_17_000021_create_opening_stock_imports_table.php
│  │  └─ 2026_08_22_000022_create_expenses_tables.php
│  ├─ seeders
│  │  └─ DatabaseSeeder.php
│  └─ sql
│     ├─ add_expenses_module.sql
│     ├─ add_opening_stock_import_control.sql
│     ├─ add_vendor_payments.sql
│     ├─ clinicms_database.sql
│     ├─ fix_admin_password.sql
│     ├─ fix_prescriptions_nullable.sql
│     ├─ fix_prescription_items_columns.sql
│     ├─ optimize_indexes.sql
│     └─ update_multi_patient.sql
├─ fix-folders.bat
├─ FIX_APP_KEY.bat
├─ FIX_CACHE_ERROR.bat
├─ FIX_PROFIT_ERROR.bat
├─ FIX_VIEW_ERROR.bat
├─ FORCE_FIX.bat
├─ INSTALLATION.md
├─ optimize.bat
├─ public
│  ├─ .htaccess
│  ├─ check_db.php
│  ├─ css
│  │  └─ app.css
│  ├─ db_test.php
│  ├─ debug_save.php
│  ├─ index.php
│  ├─ js
│  │  ├─ app.js
│  │  ├─ clinicms-print-server.js
│  │  └─ clinicms1-print-server.js
│  └─ test_save.php
├─ README.md
├─ resources
│  └─ views
│     ├─ auth
│     │  └─ login.blade.php
│     ├─ cash
│     │  └─ index.blade.php
│     ├─ components
│     │  └─ sidebar.blade.php
│     ├─ dashboard
│     │  └─ index.blade.php
│     ├─ doctors
│     │  └─ index.blade.php
│     ├─ expenses
│     │  ├─ index.blade.php
│     │  └─ report.blade.php
│     ├─ inventory
│     │  ├─ categories
│     │  │  └─ index.blade.php
│     │  ├─ drug-types
│     │  │  └─ index.blade.php
│     │  ├─ products
│     │  │  ├─ create.blade.php
│     │  │  ├─ edit.blade.php
│     │  │  ├─ index.blade.php
│     │  │  ├─ opening-stock-import.blade.php
│     │  │  └─ show.blade.php
│     │  └─ units
│     │     └─ index.blade.php
│     ├─ layouts
│     │  └─ app.blade.php
│     ├─ patients
│     │  ├─ create.blade.php
│     │  ├─ edit.blade.php
│     │  ├─ index.blade.php
│     │  └─ show.blade.php
│     ├─ prescriptions
│     │  ├─ create.blade.php
│     │  ├─ edit.blade.php
│     │  ├─ index.blade.php
│     │  ├─ index1.blade.php
│     │  ├─ print.blade.php
│     │  ├─ show.blade.php
│     │  ├─ show1.blade.php
│     │  └─ treatments
│     │     ├─ create.blade.php
│     │     ├─ edit.blade.php
│     │     └─ index.blade.php
│     ├─ purchase
│     │  ├─ create.blade.php
│     │  ├─ index.blade.php
│     │  └─ show.blade.php
│     ├─ reports
│     │  ├─ doctor-wise.blade.php
│     │  ├─ expiry.blade.php
│     │  ├─ index.blade.php
│     │  ├─ low-stock.blade.php
│     │  ├─ product-sales.blade.php
│     │  ├─ profit-loss.blade.php
│     │  ├─ purchases.blade.php
│     │  ├─ radiology.blade.php
│     │  └─ sales.blade.php
│     ├─ sales
│     │  ├─ index.blade.php
│     │  ├─ pos.blade.php
│     │  ├─ print.blade.php
│     │  └─ show.blade.php
│     └─ users
│        ├─ create.blade.php
│        ├─ edit.blade.php
│        └─ index.blade.php
├─ routes
│  ├─ api.php
│  ├─ console.php
│  └─ web.php
├─ ROUTE_FIX.bat
├─ server.php
├─ setup-windows.bat
├─ setup.sh
├─ SQL_IMPORT_GUIDE.md
└─ storage
   ├─ app
   │  ├─ private
   │  └─ public
   ├─ framework
   │  ├─ cache
   │  │  └─ data
   │  │     ├─ 27
   │  │     │  └─ 6e
   │  │     │     └─ 276e64775a36243e7c995ac704150c726bab0fa0
   │  │     ├─ 32
   │  │     │  └─ 33
   │  │     │     └─ 3233db091fe8938cdfa408d0dc70aa2138b4abaa
   │  │     ├─ 3c
   │  │     │  └─ fb
   │  │     │     └─ 3cfb1cb5c7eeaba2c58e89980a3d3b0198fde97a
   │  │     ├─ 3f
   │  │     │  └─ e5
   │  │     │     └─ 3fe572711d69e70016f44e6aad98bdb2d8db13ec
   │  │     ├─ 43
   │  │     │  └─ 7e
   │  │     │     └─ 437e4e9d66e15fa4bc337d2567d7356bbffe1c25
   │  │     ├─ 4e
   │  │     │  └─ 73
   │  │     │     └─ 4e73a8c4ef3887b1982fe1bd9cd4004a49b937ae
   │  │     ├─ 50
   │  │     │  └─ 16
   │  │     │     └─ 50167ce819fc53225da271247d0d6cb95ebfdb6b
   │  │     ├─ 5a
   │  │     │  └─ 0e
   │  │     │     └─ 5a0e3a270abfe10d7dc8dc1ac12cbf5d28fe9c39
   │  │     ├─ 5b
   │  │     │  └─ af
   │  │     │     └─ 5baf1c8212cf123636d63847bf67a9df68f26f52
   │  │     ├─ 5f
   │  │     │  └─ 80
   │  │     │     └─ 5f80c8f12f19e0a04a9d8bac7d369ef08db12906
   │  │     ├─ 64
   │  │     │  └─ 35
   │  │     │     └─ 643525cd9ad6e497f88db4c36dbe73866f2fc02c
   │  │     ├─ 66
   │  │     │  └─ 63
   │  │     │     └─ 666363eba123a4b08531d8af5fefa105dbb9ae15
   │  │     ├─ 7b
   │  │     │  └─ 7c
   │  │     │     └─ 7b7cb0c3ab3c764b30e89be5fad306ab20816783
   │  │     ├─ 92
   │  │     │  └─ 3f
   │  │     │     └─ 923f154e4c24b15c3a86dcd0959d92792112b029
   │  │     ├─ ab
   │  │     │  └─ a0
   │  │     │     └─ aba00cee31ed34a4d0e634bc71fdd5d6466442a7
   │  │     ├─ c6
   │  │     │  └─ 5e
   │  │     │     └─ c65eb542281c4a59c41193f80242724352d59d5d
   │  │     ├─ ca
   │  │     │  └─ 7d
   │  │     │     └─ ca7d62146e2b93a655b0c4a7a91d06a40218f016
   │  │     ├─ ee
   │  │     │  └─ 82
   │  │     │     └─ ee82fa2116f579915892ffbff884e369f67c7d11
   │  │     └─ f4
   │  │        └─ 78
   │  │           └─ f478adaa15cc5757e28bb8e0cb3af97040015147
   │  ├─ sessions
   │  │  ├─ 43O0G4TZ3E8itSsqrOngIyqWOFkILQjka4s85ssP
   │  │  ├─ 9odJfBQWXs4xvlnRTGN8DDU1YmZ4aQiaYVx5imLr
   │  │  ├─ aY9Mmdc9qAYBWblQeDsLsnSUaY11QEjRuHsKoaHk
   │  │  ├─ lXJiMszZ4jC4eRUNwc37tUlsK7YjVb27NYGXcDHF
   │  │  └─ QCMNZSjwBs3YNdy7TaS3mgfJnD35KdYS7mJJEGT7
   │  ├─ testing
   │  └─ views
   │     ├─ 0021be9055e016a739349bf1011ce27c.php
   │     ├─ 330bb7e1ee164dc8f4075d5a4218eb0f.php
   │     ├─ 3f8bf32e81b401b2365faa9163511622.php
   │     ├─ 4c9e412dd6afdb19d2f1ba88ff3527bf.php
   │     ├─ 51c4f10d86cc725b28aa1eaab3a9ae4c.php
   │     ├─ 5962acf8a5e03f369c43dba8340c7cc5.php
   │     ├─ 60024af6f702078c931aaaa34775d629.php
   │     ├─ 64f33dffe39e2a702beb17daae54253a.php
   │     ├─ 66e50a6b61e4ad38bdfb01696d05187e.php
   │     ├─ 6f2f39b8bb7190f3c5f3f263ad8fcbd2.php
   │     ├─ 81cb7c7cab244ebf3c56b709190e619e.php
   │     ├─ 8296e1399281c576f3b1769303c005ed.php
   │     ├─ 8651bbe7da46d21d8c12daf6367f54b5.php
   │     ├─ 93467002586632519ee53e752269ca0b.php
   │     ├─ 936c89929497fb2a4ab7a8a985a299aa.php
   │     ├─ 971813b3ef880c4878ceae378652252c.php
   │     ├─ 9c33ecdf135c01c5d635e32178626373.php
   │     ├─ a56b01dc7a974d65ce2fe3f03710af5e.php
   │     ├─ ad855bc46b5e2dee25b8b569392b6cb3.php
   │     ├─ be6c1ba35b45ed974591fbe8e6ade8e9.php
   │     ├─ bf113de3dd57335b09ac8695122fc144.php
   │     ├─ c0a66bef900166d07d7f6bc457dc1a91.php
   │     ├─ c194851e36e6e2d8ecb55d8f6f72ba6d.php
   │     ├─ c20a33faef8fecd9b70305f43bd0fe55.php
   │     ├─ c87a78a5bfc1bb90f0179c08b1141d64.php
   │     ├─ c966d111ed174c0427ed58911a978038.php
   │     ├─ d90ebf7eeebed6ac29c8d27b74450a78.php
   │     ├─ db9a1efcd13b5191693ad3395853e6b5.php
   │     ├─ f3fc0c0a7159d62b5f31f6652b883233.php
   │     ├─ f45cd37bcff1a5687d229d74da50094d.php
   │     ├─ f865194c5161e8513d9ebe4084e42803.php
   │     ├─ fafcf95eef8aa62158641e3e1baf2c6f.php
   │     └─ fcf1d64a8e6ba092ef5485c876db37ca.php
   └─ logs
      └─ laravel.log

```
```
clinicms
├─ app
│  ├─ Console
│  │  └─ Kernel.php
│  ├─ Exceptions
│  │  └─ Handler.php
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ Auth
│  │  │  │  └─ LoginController.php
│  │  │  ├─ CashController.php
│  │  │  ├─ Controller.php
│  │  │  ├─ DashboardController.php
│  │  │  ├─ DoctorController.php
│  │  │  ├─ ExpenseController.php
│  │  │  ├─ Inventory
│  │  │  │  ├─ CategoryController.php
│  │  │  │  ├─ DrugTypeController.php
│  │  │  │  └─ UnitController.php
│  │  │  ├─ PatientController.php
│  │  │  ├─ PrescriptionController - Copy.php
│  │  │  ├─ PrescriptionController.php
│  │  │  ├─ ProductController.php
│  │  │  ├─ PurchaseController.php
│  │  │  ├─ ReadyTreatmentController.php
│  │  │  ├─ ReportController.php
│  │  │  ├─ Sales
│  │  │  │  └─ SaleController.php
│  │  │  ├─ UserController.php
│  │  │  ├─ VendorController.php
│  │  │  └─ VendorPaymentController.php
│  │  ├─ Kernel.php
│  │  └─ Middleware
│  │     ├─ Authenticate.php
│  │     ├─ EncryptCookies.php
│  │     ├─ RedirectIfAuthenticated.php
│  │     └─ VerifyCsrfToken.php
│  ├─ Models
│  │  ├─ ActivityLog.php
│  │  ├─ CashAccount.php
│  │  ├─ CashTransaction.php
│  │  ├─ CashTransfer.php
│  │  ├─ Category.php
│  │  ├─ Doctor.php
│  │  ├─ DrugType.php
│  │  ├─ Expense.php
│  │  ├─ ExpenseCategory.php
│  │  ├─ Patient.php
│  │  ├─ Prescription.php
│  │  ├─ PrescriptionDocument.php
│  │  ├─ PrescriptionItem.php
│  │  ├─ PrescriptionPatient.php
│  │  ├─ PrescriptionPatientRadiology.php
│  │  ├─ Product.php
│  │  ├─ ProductBatch.php
│  │  ├─ Purchase.php
│  │  ├─ PurchaseItem.php
│  │  ├─ PurchasePayment.php
│  │  ├─ PurchaseReturn.php
│  │  ├─ PurchaseReturnItem.php
│  │  ├─ RadiologyTest.php
│  │  ├─ ReadyTreatment.php
│  │  ├─ ReadyTreatmentItem.php
│  │  ├─ Sale.php
│  │  ├─ SaleItem.php
│  │  ├─ SalePayment.php
│  │  ├─ SaleReturn.php
│  │  ├─ SaleReturnItem.php
│  │  ├─ Unit.php
│  │  ├─ User.php
│  │  ├─ Vendor.php
│  │  └─ VendorPayment.php
│  ├─ Providers
│  │  ├─ AppServiceProvider.php
│  │  ├─ AuthServiceProvider.php
│  │  ├─ EventServiceProvider.php
│  │  └─ RouteServiceProvider.php
│  └─ Services
│     ├─ CashService.php
│     ├─ PrescriptionService.php
│     ├─ PurchaseService.php
│     ├─ ReportService.php
│     ├─ SaleService.php
│     └─ StockService.php
├─ artisan
├─ bootstrap
│  ├─ app.php
│  └─ cache
│     ├─ packages.php
│     └─ services.php
├─ check-environment.php
├─ CLEAR_CACHE.bat
├─ composer.json
├─ config
│  ├─ app.php
│  ├─ auth.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ hashing.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ permission.php
│  ├─ queue.php
│  ├─ session.php
│  └─ view.php
├─ database
│  ├─ migrations
│  │  ├─ 2024_01_01_000001_create_users_table.php
│  │  ├─ 2024_01_01_000002_create_permission_tables.php
│  │  ├─ 2024_01_01_000003_create_doctors_table.php
│  │  ├─ 2024_01_01_000004_create_patients_table.php
│  │  ├─ 2024_01_01_000005_create_categories_table.php
│  │  ├─ 2024_01_01_000006_create_units_table.php
│  │  ├─ 2024_01_01_000007_create_drug_types_table.php
│  │  ├─ 2024_01_01_000008_create_products_table.php
│  │  ├─ 2024_01_01_000010_create_vendors_table.php
│  │  ├─ 2024_01_01_000011_create_purchases_table.php
│  │  ├─ 2024_01_01_000012_create_product_batches_table.php
│  │  ├─ 2024_01_01_000013_create_cash_accounts_table.php
│  │  ├─ 2024_01_01_000014_create_prescriptions_table.php
│  │  ├─ 2024_01_01_000015_create_sales_table.php
│  │  ├─ 2024_01_01_000016_create_activity_logs_table.php
│  │  ├─ 2024_01_01_000017_create_jobs_tables.php
│  │  ├─ 2024_01_01_000018_create_multi_patient_and_radiology_tables.php
│  │  ├─ 2024_01_01_000019_add_prescription_patient_to_items.php
│  │  ├─ 2024_01_01_000020_create_vendor_payments_table.php
│  │  ├─ 2026_08_17_000021_create_opening_stock_imports_table.php
│  │  └─ 2026_08_22_000022_create_expenses_tables.php
│  ├─ seeders
│  │  └─ DatabaseSeeder.php
│  └─ sql
│     ├─ add_expenses_module.sql
│     ├─ add_opening_stock_import_control.sql
│     ├─ add_vendor_payments.sql
│     ├─ clinicms_database.sql
│     ├─ fix_admin_password.sql
│     ├─ fix_prescriptions_nullable.sql
│     ├─ fix_prescription_items_columns.sql
│     ├─ optimize_indexes.sql
│     └─ update_multi_patient.sql
├─ fix-folders.bat
├─ FIX_APP_KEY.bat
├─ FIX_CACHE_ERROR.bat
├─ FIX_PROFIT_ERROR.bat
├─ FIX_VIEW_ERROR.bat
├─ FORCE_FIX.bat
├─ INSTALLATION.md
├─ optimize.bat
├─ public
│  ├─ .htaccess
│  ├─ check_db.php
│  ├─ css
│  │  └─ app.css
│  ├─ db_test.php
│  ├─ debug_save.php
│  ├─ index.php
│  ├─ js
│  │  ├─ app.js
│  │  ├─ clinicms-print-server.js
│  │  └─ clinicms1-print-server.js
│  └─ test_save.php
├─ README.md
├─ resources
│  └─ views
│     ├─ auth
│     │  └─ login.blade.php
│     ├─ cash
│     │  └─ index.blade.php
│     ├─ components
│     │  └─ sidebar.blade.php
│     ├─ dashboard
│     │  └─ index.blade.php
│     ├─ doctors
│     │  └─ index.blade.php
│     ├─ expenses
│     │  ├─ index.blade.php
│     │  └─ report.blade.php
│     ├─ inventory
│     │  ├─ categories
│     │  │  └─ index.blade.php
│     │  ├─ drug-types
│     │  │  └─ index.blade.php
│     │  ├─ products
│     │  │  ├─ create.blade.php
│     │  │  ├─ edit.blade.php
│     │  │  ├─ index.blade.php
│     │  │  ├─ opening-stock-import.blade.php
│     │  │  └─ show.blade.php
│     │  └─ units
│     │     └─ index.blade.php
│     ├─ layouts
│     │  └─ app.blade.php
│     ├─ patients
│     │  ├─ create.blade.php
│     │  ├─ edit.blade.php
│     │  ├─ index.blade.php
│     │  └─ show.blade.php
│     ├─ prescriptions
│     │  ├─ create.blade.php
│     │  ├─ edit.blade.php
│     │  ├─ index.blade.php
│     │  ├─ index1.blade.php
│     │  ├─ print.blade.php
│     │  ├─ show.blade.php
│     │  ├─ show1.blade.php
│     │  └─ treatments
│     │     ├─ create.blade.php
│     │     ├─ edit.blade.php
│     │     └─ index.blade.php
│     ├─ purchase
│     │  ├─ create.blade.php
│     │  ├─ index.blade.php
│     │  └─ show.blade.php
│     ├─ reports
│     │  ├─ doctor-wise.blade.php
│     │  ├─ expiry.blade.php
│     │  ├─ index.blade.php
│     │  ├─ low-stock.blade.php
│     │  ├─ product-sales.blade.php
│     │  ├─ profit-loss.blade.php
│     │  ├─ purchases.blade.php
│     │  ├─ radiology.blade.php
│     │  └─ sales.blade.php
│     ├─ sales
│     │  ├─ index.blade.php
│     │  ├─ pos.blade.php
│     │  ├─ print.blade.php
│     │  └─ show.blade.php
│     └─ users
│        ├─ create.blade.php
│        ├─ edit.blade.php
│        └─ index.blade.php
├─ routes
│  ├─ api.php
│  ├─ console.php
│  └─ web.php
├─ ROUTE_FIX.bat
├─ server.php
├─ setup-windows.bat
├─ setup.sh
├─ SQL_IMPORT_GUIDE.md
└─ storage
   ├─ app
   │  ├─ private
   │  └─ public
   ├─ framework
   │  ├─ cache
   │  │  └─ data
   │  │     ├─ 27
   │  │     │  └─ 6e
   │  │     │     └─ 276e64775a36243e7c995ac704150c726bab0fa0
   │  │     ├─ 32
   │  │     │  └─ 33
   │  │     │     └─ 3233db091fe8938cdfa408d0dc70aa2138b4abaa
   │  │     ├─ 3c
   │  │     │  └─ fb
   │  │     │     └─ 3cfb1cb5c7eeaba2c58e89980a3d3b0198fde97a
   │  │     ├─ 3f
   │  │     │  └─ e5
   │  │     │     └─ 3fe572711d69e70016f44e6aad98bdb2d8db13ec
   │  │     ├─ 43
   │  │     │  └─ 7e
   │  │     │     └─ 437e4e9d66e15fa4bc337d2567d7356bbffe1c25
   │  │     ├─ 4e
   │  │     │  └─ 73
   │  │     │     └─ 4e73a8c4ef3887b1982fe1bd9cd4004a49b937ae
   │  │     ├─ 50
   │  │     │  └─ 16
   │  │     │     └─ 50167ce819fc53225da271247d0d6cb95ebfdb6b
   │  │     ├─ 5a
   │  │     │  └─ 0e
   │  │     │     └─ 5a0e3a270abfe10d7dc8dc1ac12cbf5d28fe9c39
   │  │     ├─ 5b
   │  │     │  └─ af
   │  │     │     └─ 5baf1c8212cf123636d63847bf67a9df68f26f52
   │  │     ├─ 5f
   │  │     │  └─ 80
   │  │     │     └─ 5f80c8f12f19e0a04a9d8bac7d369ef08db12906
   │  │     ├─ 64
   │  │     │  └─ 35
   │  │     │     └─ 643525cd9ad6e497f88db4c36dbe73866f2fc02c
   │  │     ├─ 66
   │  │     │  └─ 63
   │  │     │     └─ 666363eba123a4b08531d8af5fefa105dbb9ae15
   │  │     ├─ 7b
   │  │     │  └─ 7c
   │  │     │     └─ 7b7cb0c3ab3c764b30e89be5fad306ab20816783
   │  │     ├─ 92
   │  │     │  └─ 3f
   │  │     │     └─ 923f154e4c24b15c3a86dcd0959d92792112b029
   │  │     ├─ ab
   │  │     │  └─ a0
   │  │     │     └─ aba00cee31ed34a4d0e634bc71fdd5d6466442a7
   │  │     ├─ c6
   │  │     │  └─ 5e
   │  │     │     └─ c65eb542281c4a59c41193f80242724352d59d5d
   │  │     ├─ ca
   │  │     │  └─ 7d
   │  │     │     └─ ca7d62146e2b93a655b0c4a7a91d06a40218f016
   │  │     ├─ ee
   │  │     │  └─ 82
   │  │     │     └─ ee82fa2116f579915892ffbff884e369f67c7d11
   │  │     └─ f4
   │  │        └─ 78
   │  │           └─ f478adaa15cc5757e28bb8e0cb3af97040015147
   │  ├─ sessions
   │  │  ├─ 43O0G4TZ3E8itSsqrOngIyqWOFkILQjka4s85ssP
   │  │  ├─ 9odJfBQWXs4xvlnRTGN8DDU1YmZ4aQiaYVx5imLr
   │  │  ├─ aY9Mmdc9qAYBWblQeDsLsnSUaY11QEjRuHsKoaHk
   │  │  ├─ lXJiMszZ4jC4eRUNwc37tUlsK7YjVb27NYGXcDHF
   │  │  └─ QCMNZSjwBs3YNdy7TaS3mgfJnD35KdYS7mJJEGT7
   │  ├─ testing
   │  └─ views
   │     ├─ 0021be9055e016a739349bf1011ce27c.php
   │     ├─ 330bb7e1ee164dc8f4075d5a4218eb0f.php
   │     ├─ 3f8bf32e81b401b2365faa9163511622.php
   │     ├─ 4c9e412dd6afdb19d2f1ba88ff3527bf.php
   │     ├─ 51c4f10d86cc725b28aa1eaab3a9ae4c.php
   │     ├─ 5962acf8a5e03f369c43dba8340c7cc5.php
   │     ├─ 60024af6f702078c931aaaa34775d629.php
   │     ├─ 64f33dffe39e2a702beb17daae54253a.php
   │     ├─ 66e50a6b61e4ad38bdfb01696d05187e.php
   │     ├─ 6f2f39b8bb7190f3c5f3f263ad8fcbd2.php
   │     ├─ 81cb7c7cab244ebf3c56b709190e619e.php
   │     ├─ 8296e1399281c576f3b1769303c005ed.php
   │     ├─ 8651bbe7da46d21d8c12daf6367f54b5.php
   │     ├─ 93467002586632519ee53e752269ca0b.php
   │     ├─ 936c89929497fb2a4ab7a8a985a299aa.php
   │     ├─ 971813b3ef880c4878ceae378652252c.php
   │     ├─ 9c33ecdf135c01c5d635e32178626373.php
   │     ├─ a56b01dc7a974d65ce2fe3f03710af5e.php
   │     ├─ ad855bc46b5e2dee25b8b569392b6cb3.php
   │     ├─ be6c1ba35b45ed974591fbe8e6ade8e9.php
   │     ├─ bf113de3dd57335b09ac8695122fc144.php
   │     ├─ c0a66bef900166d07d7f6bc457dc1a91.php
   │     ├─ c194851e36e6e2d8ecb55d8f6f72ba6d.php
   │     ├─ c20a33faef8fecd9b70305f43bd0fe55.php
   │     ├─ c87a78a5bfc1bb90f0179c08b1141d64.php
   │     ├─ c966d111ed174c0427ed58911a978038.php
   │     ├─ d90ebf7eeebed6ac29c8d27b74450a78.php
   │     ├─ db9a1efcd13b5191693ad3395853e6b5.php
   │     ├─ f3fc0c0a7159d62b5f31f6652b883233.php
   │     ├─ f45cd37bcff1a5687d229d74da50094d.php
   │     ├─ f865194c5161e8513d9ebe4084e42803.php
   │     ├─ fafcf95eef8aa62158641e3e1baf2c6f.php
   │     └─ fcf1d64a8e6ba092ef5485c876db37ca.php
   └─ logs
      └─ laravel.log

```