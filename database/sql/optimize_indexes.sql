-- =====================================================================
-- PERFORMANCE OPTIMIZATION INDEXES
-- ---------------------------------------------------------------------
-- Run these AFTER importing clinicms_database.sql to make all pages
-- (especially prescriptions, sales, purchases, inventory) much faster.
--
-- phpMyAdmin: select clinicms -> SQL -> paste -> Go
-- Command:   mysql -u root -p clinicms < optimize_indexes.sql
-- =====================================================================

USE `clinicms`;

-- ===== USERS / AUTH =====
CREATE INDEX idx_users_email_active ON users(email, is_active);
CREATE INDEX idx_users_created_at ON users(created_at);

-- ===== PATIENTS (search) =====
CREATE INDEX idx_patients_name ON patients(name);
CREATE INDEX idx_patients_phone ON patients(phone);
CREATE INDEX idx_patients_last_visit ON patients(last_visit);

-- ===== DOCTORS =====
CREATE INDEX idx_doctors_active ON doctors(is_active);

-- ===== PRODUCTS =====
CREATE INDEX idx_products_active_category ON products(is_active, category_id);
CREATE INDEX idx_products_drug_type ON products(drug_type_id);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_sku ON products(sku);

-- ===== PRODUCT BATCHES (FEFO stock deduction) =====
-- The most important index for prescription/sales speed:
-- Finds the earliest-expiring batch with stock in milliseconds.
CREATE INDEX idx_batches_product_expiry_qty ON product_batches(product_id, expiry_date, quantity);
CREATE INDEX idx_batches_expiry_qty ON product_batches(expiry_date, quantity);
CREATE INDEX idx_batches_purchase ON product_batches(purchase_id);
CREATE INDEX idx_batches_batch_number ON product_batches(batch_number);

-- ===== PRESCRIPTIONS =====
CREATE INDEX idx_rx_date ON prescriptions(prescription_date);
CREATE INDEX idx_rx_status ON prescriptions(status);
CREATE INDEX idx_rx_sale_status ON prescriptions(sale_status);
CREATE INDEX idx_rx_patient_date ON prescriptions(patient_id, prescription_date);
CREATE INDEX idx_rx_doctor ON prescriptions(doctor_id);
CREATE INDEX idx_rx_number ON prescriptions(prescription_number);
CREATE INDEX idx_rx_created ON prescriptions(created_at);

-- ===== PRESCRIPTION ITEMS =====
CREATE INDEX idx_rx_items_prescription ON prescription_items(prescription_id);
CREATE INDEX idx_rx_items_product ON prescription_items(product_id);
CREATE INDEX idx_rx_items_batch ON prescription_items(batch_id);
CREATE INDEX idx_rx_items_stock_deducted ON prescription_items(stock_deducted);

-- ===== SALES =====
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_payment_status ON sales(payment_status);
CREATE INDEX idx_sales_patient ON sales(patient_id);
CREATE INDEX idx_sales_prescription ON sales(prescription_id);
CREATE INDEX idx_sales_number ON sales(invoice_number);
CREATE INDEX idx_sales_created ON sales(created_at);
CREATE INDEX idx_sales_date_status ON sales(sale_date, payment_status);

-- ===== SALE ITEMS =====
CREATE INDEX idx_sale_items_sale ON sale_items(sale_id);
CREATE INDEX idx_sale_items_product ON sale_items(product_id);
CREATE INDEX idx_sale_items_batch ON sale_items(batch_id);

-- ===== PURCHASES =====
CREATE INDEX idx_purchases_date ON purchases(purchase_date);
CREATE INDEX idx_purchases_vendor_date ON purchases(vendor_id, purchase_date);
CREATE INDEX idx_purchases_payment_status ON purchases(payment_status);
CREATE INDEX idx_purchases_number ON purchases(invoice_number);

-- ===== PURCHASE ITEMS =====
CREATE INDEX idx_purchase_items_purchase ON purchase_items(purchase_id);
CREATE INDEX idx_purchase_items_product ON purchase_items(product_id);

-- ===== CASH / TRANSACTIONS =====
CREATE INDEX idx_cash_accounts_active ON cash_accounts(is_active);
CREATE INDEX idx_cash_tx_account_date ON cash_transactions(cash_account_id, transaction_date);
CREATE INDEX idx_cash_tx_type ON cash_transactions(type);
CREATE INDEX idx_cash_tx_source ON cash_transactions(source_type, source_id);
CREATE INDEX idx_cash_tx_date ON cash_transactions(transaction_date);

-- ===== VENDORS =====
CREATE INDEX idx_vendors_active ON vendors(is_active);
CREATE INDEX idx_vendors_name ON vendors(name);

-- ===== CATEGORIES / DRUG TYPES =====
CREATE INDEX idx_categories_active ON categories(is_active);
CREATE INDEX idx_drug_types_active_sort ON drug_types(is_active, sort_order);

-- ===== ACTIVITY LOGS =====
CREATE INDEX idx_activity_user ON activity_logs(user_id);
CREATE INDEX idx_activity_module ON activity_logs(module);
CREATE INDEX idx_activity_created ON activity_logs(created_at);

-- ===== SESSIONS =====
CREATE INDEX idx_sessions_last_activity ON sessions(last_activity);
CREATE INDEX idx_sessions_user ON sessions(user_id);

-- =====================================================================
-- Apply MySQL query cache / InnoDB settings (optional, my.cnf)
-- ---------------------------------------------------------------------
-- These settings are recommended in your MySQL config (my.ini / my.cnf):
--
-- [mysqld]
-- innodb_buffer_pool_size = 512M     (or 50-70% of RAM)
-- innodb_log_file_size    = 128M
-- query_cache_type        = 1
-- query_cache_size        = 64M
-- max_connections         = 100
-- table_open_cache        = 4000
-- =====================================================================

SELECT 'Performance indexes added successfully!' AS status;
SELECT COUNT(*) AS total_indexes FROM information_schema.statistics WHERE table_schema = 'clinicms';
