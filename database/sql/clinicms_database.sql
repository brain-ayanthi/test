-- =====================================================================
-- CLINIC & PHARMACY MANAGEMENT SYSTEM - COMPLETE DATABASE
-- ---------------------------------------------------------------------
-- Import this file via phpMyAdmin OR MySQL command line:
--   mysql -u root -p < clinicms_database.sql
-- Or in phpMyAdmin: create database "clinicms" -> Import -> choose file
--
-- After importing, update .env DB credentials and run:
--   php artisan key:generate
--   php artisan storage:link
-- Default login: admin@clinicms.test / password
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `clinicms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `clinicms`;

-- =====================================================================
-- 1. USERS, ROLES & PERMISSIONS
-- =====================================================================
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `model_has_permissions`;
DROP TABLE IF EXISTS `model_has_roles`;
DROP TABLE IF EXISTS `role_has_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 2. DOCTORS
-- =====================================================================
DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `chamber` varchar(255) DEFAULT NULL,
  `visit_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `doctor_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `address` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_id` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctors_email_unique` (`email`),
  KEY `doctors_user_id_foreign` (`user_id`),
  CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. PATIENTS
-- =====================================================================
DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `age_type` varchar(255) NOT NULL DEFAULT 'year',
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text,
  `dob` date DEFAULT NULL,
  `blood_group` varchar(255) DEFAULT NULL,
  `medical_history` text,
  `allergies` text,
  `avatar` varchar(255) DEFAULT NULL,
  `last_visit` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_patient_code_unique` (`patient_code`),
  KEY `patients_created_by_foreign` (`created_by`),
  CONSTRAINT `patients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 4. CATEGORIES
-- =====================================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text,
  `parent_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 5. UNITS (Box, Packet, Strip, Piece)
-- =====================================================================
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) NOT NULL,
  `conversion` decimal(12,2) NOT NULL DEFAULT '1.00',
  `is_base_unit` tinyint(1) NOT NULL DEFAULT '0',
  `base_unit_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `units_base_unit_id_foreign` (`base_unit_id`),
  CONSTRAINT `units_base_unit_id_foreign` FOREIGN KEY (`base_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. DRUG TYPES
-- =====================================================================
DROP TABLE IF EXISTS `drug_types`;
CREATE TABLE `drug_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#22c55e',
  `description` text,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `drug_types_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 7. VENDORS
-- =====================================================================
DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text,
  `tax_number` varchar(255) DEFAULT NULL,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 8. CASH ACCOUNTS
-- =====================================================================
DROP TABLE IF EXISTS `cash_transfers`;
DROP TABLE IF EXISTS `cash_transactions`;
DROP TABLE IF EXISTS `cash_accounts`;
CREATE TABLE `cash_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cash_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cash_account_id` bigint unsigned NOT NULL,
  `type` enum('income','expense','transfer_in','transfer_out') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL DEFAULT '0.00',
  `source_type` varchar(255) DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `note` text,
  `transaction_date` date NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_transactions_cash_account_id_foreign` (`cash_account_id`),
  KEY `cash_transactions_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `cash_transactions_transaction_date_index` (`transaction_date`),
  CONSTRAINT `cash_transactions_cash_account_id_foreign` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cash_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_account_id` bigint unsigned NOT NULL,
  `to_account_id` bigint unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `transfer_date` date NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `note` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_transfers_from_account_id_foreign` (`from_account_id`),
  KEY `cash_transfers_to_account_id_foreign` (`to_account_id`),
  CONSTRAINT `cash_transfers_from_account_id_foreign` FOREIGN KEY (`from_account_id`) REFERENCES `cash_accounts` (`id`),
  CONSTRAINT `cash_transfers_to_account_id_foreign` FOREIGN KEY (`to_account_id`) REFERENCES `cash_accounts` (`id`),
  CONSTRAINT `cash_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 9. PURCHASES
-- =====================================================================
DROP TABLE IF EXISTS `purchase_return_items`;
DROP TABLE IF EXISTS `purchase_returns`;
DROP TABLE IF EXISTS `purchase_payments`;
DROP TABLE IF EXISTS `purchase_items`;
DROP TABLE IF EXISTS `purchases`;

CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `purchase_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(14,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','bank','mfs','cheque') NOT NULL DEFAULT 'cash',
  `notes` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchases_invoice_number_unique` (`invoice_number`),
  KEY `purchases_vendor_id_foreign` (`vendor_id`),
  KEY `purchases_purchase_date_index` (`purchase_date`),
  CONSTRAINT `purchases_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `purchase_quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_unit_id` bigint unsigned DEFAULT NULL,
  `pieces_per_unit` decimal(12,2) NOT NULL DEFAULT '1.00',
  `total_pieces` decimal(12,2) NOT NULL DEFAULT '0.00',
  `purchase_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unit_cost` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `selling_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `batch_number` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('cash','bank','mfs','cheque') NOT NULL DEFAULT 'cash',
  `reference` varchar(255) DEFAULT NULL,
  `notes` text,
  `account_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_payments_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_payments_account_id_foreign` (`account_id`),
  CONSTRAINT `purchase_payments_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_payments_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_number` varchar(255) NOT NULL,
  `purchase_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `return_date` date NOT NULL,
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `reason` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_returns_return_number_unique` (`return_number`),
  KEY `purchase_returns_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_returns_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `purchase_returns_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_returns_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_return_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_return_id` bigint unsigned NOT NULL,
  `purchase_item_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_return_items_purchase_return_id_foreign` (`purchase_return_id`),
  CONSTRAINT `purchase_return_items_purchase_return_id_foreign` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 10. PRODUCTS
-- =====================================================================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `drug_type_id` bigint unsigned DEFAULT NULL,
  `form_type` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `description` text,
  `purchase_unit_id` bigint unsigned DEFAULT NULL,
  `pieces_per_purchase_unit` decimal(12,2) NOT NULL DEFAULT '1.00',
  `selling_unit_id` bigint unsigned DEFAULT NULL,
  `purchase_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `selling_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `mrp` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `rack_number` varchar(50) DEFAULT NULL,
  `shelf_number` varchar(50) DEFAULT NULL,
  `min_stock` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_prescription_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `track_batch` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_barcode_index` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_drug_type_id_foreign` (`drug_type_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_drug_type_id_foreign` FOREIGN KEY (`drug_type_id`) REFERENCES `drug_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_selling_unit_id_foreign` FOREIGN KEY (`selling_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 11. PRODUCT BATCHES (FEFO)
-- =====================================================================
DROP TABLE IF EXISTS `product_batches`;
CREATE TABLE `product_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `batch_number` varchar(255) NOT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `purchase_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `selling_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `initial_quantity` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rack_number` varchar(255) DEFAULT NULL,
  `purchase_id` bigint unsigned DEFAULT NULL,
  `purchase_item_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_batches_product_id_foreign` (`product_id`),
  KEY `product_batches_batch_number_index` (`batch_number`),
  KEY `product_batches_expiry_date_index` (`expiry_date`),
  KEY `product_batches_product_id_expiry_date_index` (`product_id`,`expiry_date`),
  CONSTRAINT `product_batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_batches_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 12. READY TREATMENTS
-- =====================================================================
DROP TABLE IF EXISTS `ready_treatment_items`;
DROP TABLE IF EXISTS `ready_treatments`;
CREATE TABLE `ready_treatments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `disease` varchar(255) DEFAULT NULL,
  `description` text,
  `doctor_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ready_treatments_doctor_id_foreign` (`doctor_id`),
  CONSTRAINT `ready_treatments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ready_treatment_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ready_treatment_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `drug_name` varchar(255) NOT NULL,
  `form_type` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `timing` enum('OD','BD','BID','TDS','QID') NOT NULL DEFAULT 'TDS',
  `timing_multiplier` int NOT NULL DEFAULT '3',
  `times_of_day` json DEFAULT NULL,
  `meal_relation` varchar(255) DEFAULT NULL,
  `duration_days` int NOT NULL,
  `instruction` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ready_treatment_items_ready_treatment_id_foreign` (`ready_treatment_id`),
  KEY `ready_treatment_items_product_id_foreign` (`product_id`),
  CONSTRAINT `ready_treatment_items_ready_treatment_id_foreign` FOREIGN KEY (`ready_treatment_id`) REFERENCES `ready_treatments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ready_treatment_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 13. PRESCRIPTIONS
-- =====================================================================
DROP TABLE IF EXISTS `prescription_documents`;
DROP TABLE IF EXISTS `prescription_items`;
DROP TABLE IF EXISTS `prescriptions`;

CREATE TABLE `prescriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prescription_number` varchar(255) NOT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `doctor_id` bigint unsigned DEFAULT NULL,
  `ready_treatment_id` bigint unsigned DEFAULT NULL,
  `prescription_date` date NOT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `sale_status` enum('unsold','partial','sold') NOT NULL DEFAULT 'unsold',
  `patient_name` varchar(255) DEFAULT NULL,
  `patient_age` int DEFAULT NULL,
  `patient_phone` varchar(255) DEFAULT NULL,
  `patient_code` varchar(255) DEFAULT NULL,
  `medicine_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `doctor_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','bank','mfs') DEFAULT NULL,
  `diagnosis` text,
  `lab_workup` text,
  `precautions` text,
  `physiotherapy` text,
  `notes` text,
  `next_visit` text,
  `is_printed` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescriptions_prescription_number_unique` (`prescription_number`),
  KEY `prescriptions_patient_id_foreign` (`patient_id`),
  KEY `prescriptions_doctor_id_foreign` (`doctor_id`),
  KEY `prescriptions_ready_treatment_id_foreign` (`ready_treatment_id`),
  KEY `prescriptions_prescription_date_index` (`prescription_date`),
  CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_ready_treatment_id_foreign` FOREIGN KEY (`ready_treatment_id`) REFERENCES `ready_treatments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prescription_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prescription_id` bigint unsigned NOT NULL,
  `prescription_patient_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `drug_name` varchar(255) NOT NULL,
  `form_type` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `timing` enum('OD','BD','BID','TDS','QID') NOT NULL DEFAULT 'TDS',
  `timing_multiplier` int NOT NULL DEFAULT '3',
  `times_of_day` json DEFAULT NULL,
  `meal_relation` varchar(255) DEFAULT NULL,
  `duration_days` int NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `instruction` varchar(255) DEFAULT NULL,
  `instruction_note` text,
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock_deducted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prescription_items_prescription_id_foreign` (`prescription_id`),
  KEY `prescription_items_patient_id_foreign` (`prescription_patient_id`),
  KEY `prescription_items_product_id_foreign` (`product_id`),
  KEY `prescription_items_batch_id_foreign` (`batch_id`),
  CONSTRAINT `prescription_items_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_items_patient_id_foreign` FOREIGN KEY (`prescription_patient_id`) REFERENCES `prescription_patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `prescription_items_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `prescription_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prescription_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prescription_documents_prescription_id_foreign` (`prescription_id`),
  CONSTRAINT `prescription_documents_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 14. SALES
-- =====================================================================
DROP TABLE IF EXISTS `sale_return_items`;
DROP TABLE IF EXISTS `sale_returns`;
DROP TABLE IF EXISTS `sale_payments`;
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;

CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `sale_date` date NOT NULL,
  `prescription_id` bigint unsigned DEFAULT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `doctor_id` bigint unsigned DEFAULT NULL,
  `customer_type` varchar(255) NOT NULL DEFAULT 'walking',
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(14,2) NOT NULL DEFAULT '0.00',
  `doctor_fee` decimal(14,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `change_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','bank','mfs') NOT NULL DEFAULT 'cash',
  `cash_account_id` bigint unsigned DEFAULT NULL,
  `notes` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoice_number_unique` (`invoice_number`),
  KEY `sales_sale_date_index` (`sale_date`),
  KEY `sales_prescription_id_foreign` (`prescription_id`),
  KEY `sales_patient_id_foreign` (`patient_id`),
  KEY `sales_cash_account_id_foreign` (`cash_account_id`),
  CONSTRAINT `sales_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_cash_account_id_foreign` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `prescription_item_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('cash','bank','mfs') NOT NULL DEFAULT 'cash',
  `cash_account_id` bigint unsigned DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_payments_sale_id_foreign` (`sale_id`),
  KEY `sale_payments_cash_account_id_foreign` (`cash_account_id`),
  CONSTRAINT `sale_payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_payments_cash_account_id_foreign` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_number` varchar(255) NOT NULL,
  `sale_id` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `return_date` date NOT NULL,
  `total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `refund_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `refund_method` enum('cash','bank','mfs') NOT NULL DEFAULT 'cash',
  `cash_account_id` bigint unsigned DEFAULT NULL,
  `reason` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_returns_return_number_unique` (`return_number`),
  KEY `sale_returns_sale_id_foreign` (`sale_id`),
  CONSTRAINT `sale_returns_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_return_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_return_id` bigint unsigned NOT NULL,
  `sale_item_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `batch_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_return_items_sale_return_id_foreign` (`sale_return_id`),
  CONSTRAINT `sale_return_items_sale_return_id_foreign` FOREIGN KEY (`sale_return_id`) REFERENCES `sale_returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 15. ACTIVITY LOGS
-- =====================================================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 16. QUEUE / CACHE TABLES
-- =====================================================================
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--                            SEED DATA
-- =====================================================================
USE `clinicms`;

-- Admin user (password = "password")
INSERT INTO `users` (`id`,`name`,`email`,`email_verified_at`,`password`,`phone`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'System Administrator','admin@clinicms.test',NOW(),'$2y$12$70oISlEdklu3koGmVBwWN.ACOVlBAmDIzA17RH1oDlgWq76s7ps0W','+94770000000',1,NOW(),NOW());

-- Roles
INSERT INTO `roles` (`id`,`name`,`guard_name`,`created_at`,`updated_at`) VALUES
(1,'Admin','web',NOW(),NOW()),
(2,'Doctor','web',NOW(),NOW()),
(3,'Staff','web',NOW(),NOW());

-- Permissions
INSERT INTO `permissions` (`name`,`guard_name`,`created_at`,`updated_at`) VALUES
('manage users','web',NOW(),NOW()),
('manage doctors','web',NOW(),NOW()),
('manage patients','web',NOW(),NOW()),
('create prescription','web',NOW(),NOW()),
('view prescription','web',NOW(),NOW()),
('edit prescription','web',NOW(),NOW()),
('manage inventory','web',NOW(),NOW()),
('manage purchase','web',NOW(),NOW()),
('manage sales','web',NOW(),NOW()),
('view reports','web',NOW(),NOW()),
('manage cash','web',NOW(),NOW()),
('manage settings','web',NOW(),NOW());

-- Assign all permissions to Admin
INSERT INTO `role_has_permissions` (`permission_id`,`role_id`)
SELECT id, 1 FROM `permissions`;
INSERT INTO `role_has_permissions` (`permission_id`,`role_id`) VALUES
(4,2),(5,2),(3,2),
(4,3),(5,3),(9,3),(3,3);

-- Assign Admin role to user
INSERT INTO `model_has_roles` (`role_id`,`model_type`,`model_id`) VALUES (1,'App\\Models\\User',1);

-- Doctors
INSERT INTO `doctors` (`id`,`name`,`email`,`phone`,`specialization`,`qualification`,`chamber`,`visit_fee`,`doctor_fee`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'Dr. Perera','perera@clinic.lk','+94770000001','General Physician','MBBS, MD','Colombo Medical Center',500.00,500.00,1,NOW(),NOW()),
(2,'Dr. Silva','silva@clinic.lk','+94770000002','Cardiologist','MBBS, MD Cardiology','Heart Care Center',1500.00,1000.00,1,NOW(),NOW());

-- Cash accounts
INSERT INTO `cash_accounts` (`id`,`name`,`type`,`opening_balance`,`balance`,`icon`,`color`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'Store Cash','cash',1000000.00,1000000.00,'wallet','#22c55e',1,NOW(),NOW()),
(2,'Bank Balance','bank',53354.31,53354.31,'bank','#3b82f6',1,NOW(),NOW()),
(3,'MFS Balance','mfs',2801.24,2801.24,'mobile','#a855f7',1,NOW(),NOW());

-- Categories
INSERT INTO `categories` (`id`,`name`,`slug`,`icon`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'Medicines','medicines','pill',1,NOW(),NOW()),
(2,'Devices','devices','device',1,NOW(),NOW()),
(3,'Vitamins & Supplements','vitamins','vitamin',1,NOW(),NOW());

-- Units (Piece is base; Box/Packet/Strip convert to pieces)
INSERT INTO `units` (`id`,`name`,`short_name`,`conversion`,`is_base_unit`,`base_unit_id`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'Piece','pc',1,1,NULL,1,NOW(),NOW()),
(2,'Strip','strip',10,0,1,1,NOW(),NOW()),
(3,'Packet','pkt',100,0,1,1,NOW(),NOW()),
(4,'Box (100)','box100',100,0,1,1,NOW(),NOW()),
(5,'Box (500)','box500',500,0,1,1,NOW(),NOW()),
(6,'Box (1000)','box1000',1000,0,1,1,NOW(),NOW());

-- Drug Types
INSERT INTO `drug_types` (`id`,`name`,`slug`,`icon`,`color`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'Antibiotic','antibiotic','shield-virus','#ef4444',1,1,NOW(),NOW()),
(2,'Analgesic','analgesic','bolt','#f59e0b',2,1,NOW(),NOW()),
(3,'Antipyretic','antipyretic','temperature-high','#3b82f6',3,1,NOW(),NOW()),
(4,'Antihistamine','antihistamine','allergies','#8b5cf6',4,1,NOW(),NOW()),
(5,'Antacid','antacid','stomach','#10b981',5,1,NOW(),NOW()),
(6,'Vitamin','vitamin','capsules','#f97316',6,1,NOW(),NOW()),
(7,'Antidiabetic','antidiabetic','syringe','#06b6d4',7,1,NOW(),NOW()),
(8,'Cardiac','cardiac','heart-pulse','#ec4899',8,1,NOW(),NOW());

-- Vendors
INSERT INTO `vendors` (`id`,`name`,`company`,`phone`,`opening_balance`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'ABC Pharmaceuticals','ABC Pharma (Pvt) Ltd','+94112222222',0,1,NOW(),NOW()),
(2,'HealthCare Distributors','HealthCare Ltd','+94113333333',0,1,NOW(),NOW());

-- Products
INSERT INTO `products` (`id`,`name`,`sku`,`category_id`,`drug_type_id`,`form_type`,`strength`,`generic_name`,`purchase_unit_id`,`pieces_per_purchase_unit`,`selling_unit_id`,`purchase_price`,`selling_price`,`mrp`,`min_stock`,`rack_number`,`is_prescription_required`,`is_active`,`track_batch`,`created_at`,`updated_at`) VALUES
(1,'Paracetamol 500mg','MED-PARA-500',1,3,'tablet','500mg','Paracetamol',6,1000,1,2500.00,5.00,5.00,100,'A-01',0,1,1,NOW(),NOW()),
(2,'Amoxicillin 250mg','MED-AMOX-250',1,1,'capsule','250mg','Amoxicillin',6,1000,1,4500.00,8.00,10.00,50,'A-02',1,1,1,NOW(),NOW()),
(3,'Ciprofloxacin 500mg','MED-CIPRO-500',1,1,'tablet','500mg','Ciprofloxacin',5,500,1,6000.00,15.00,18.00,30,'A-03',1,1,1,NOW(),NOW()),
(4,'Azithromycin 500mg','MED-AZI-500',1,1,'tablet','500mg','Azithromycin',4,100,1,3000.00,35.00,40.00,20,'A-04',1,1,1,NOW(),NOW()),
(5,'Metformin 500mg','MED-MET-500',1,7,'tablet','500mg','Metformin HCl',6,1000,1,3000.00,6.00,8.00,100,'B-01',1,1,1,NOW(),NOW()),
(6,'Omeprazole 20mg','MED-OME-20',1,5,'capsule','20mg','Omeprazole',6,1000,1,2800.00,7.00,10.00,50,'B-02',0,1,1,NOW(),NOW()),
(7,'Multivitamin','VIT-MULTI',3,6,'tablet','Complex','Multivitamin',4,100,1,2500.00,30.00,35.00,30,'C-01',0,1,1,NOW(),NOW()),
(8,'Vitamin C 1000mg','VIT-C-1000',3,6,'tablet','1000mg','Ascorbic Acid',3,100,1,1500.00,20.00,25.00,20,'C-02',0,1,1,NOW(),NOW()),
(9,'Losartan 50mg','MED-LOS-50',1,8,'tablet','50mg','Losartan Potassium',5,500,1,4000.00,10.00,12.00,50,'D-01',1,1,1,NOW(),NOW()),
(10,'Ibuprofen 400mg','MED-IBU-400',1,2,'tablet','400mg','Ibuprofen',6,1000,1,3500.00,8.00,10.00,80,'A-05',0,1,1,NOW(),NOW()),
(11,'Cetirizine 10mg','MED-CET-10',1,4,'tablet','10mg','Cetirizine',6,1000,1,1800.00,4.00,5.00,60,'E-01',0,1,1,NOW(),NOW()),
(12,'Aspirin 75mg','MED-ASP-75',1,8,'tablet','75mg','Acetylsalicylic Acid',6,1000,1,1500.00,3.00,4.00,100,'D-02',0,1,1,NOW(),NOW());

-- Product Batches (FEFO - earliest expiry first will be deducted)
INSERT INTO `product_batches` (`product_id`,`batch_number`,`manufacturing_date`,`expiry_date`,`purchase_price`,`selling_price`,`quantity`,`initial_quantity`,`rack_number`,`created_at`,`updated_at`) VALUES
(1,'BPARA001','2026-01-01','2028-06-01',2.50,5.00,245,300,'A-01',NOW(),NOW()),
(2,'BAMOX001','2026-02-01','2028-02-01',4.50,8.00,180,200,'A-02',NOW(),NOW()),
(3,'BCIPRO001','2025-06-01','2026-11-01',6.00,15.00,5,50,'A-03',NOW(),NOW()),
(4,'BAZI001','2025-03-01','2026-09-01',30.00,35.00,0,50,'A-04',NOW(),NOW()),
(5,'BMET001','2026-01-01','2029-01-01',3.00,6.00,320,400,'B-01',NOW(),NOW()),
(6,'BOME001','2026-03-01','2028-03-01',2.80,7.00,210,250,'B-02',NOW(),NOW()),
(7,'BMULTI001','2025-05-01','2026-12-01',25.00,30.00,0,50,'C-01',NOW(),NOW()),
(8,'BVITC001','2026-04-01','2028-04-01',15.00,20.00,95,100,'C-02',NOW(),NOW()),
(9,'BLOS001','2026-02-01','2029-02-01',8.00,10.00,140,150,'D-01',NOW(),NOW()),
(10,'BIBU001','2026-01-15','2028-01-15',3.50,8.00,200,250,'A-05',NOW(),NOW()),
(11,'BCET001','2026-03-01','2028-03-01',1.80,4.00,160,200,'E-01',NOW(),NOW()),
(12,'BASP001','2026-02-01','2029-02-01',1.50,3.00,280,300,'D-02',NOW(),NOW());

-- Patients
INSERT INTO `patients` (`id`,`patient_code`,`name`,`email`,`phone`,`age`,`age_type`,`gender`,`address`,`blood_group`,`last_visit`,`created_by`,`created_at`,`updated_at`) VALUES
(1,'P202607270001','Elmer Stamm','brittany52@example.net','+1-419-428-0787',39,'year','other','9702 Wehner Junction Apt. 035 Andersonville, IL 47268-7016',NULL,'1992-04-22',1,NOW(),NOW()),
(2,'P202607270002','Patient',NULL,'+1 (629) 329-5071',45,'year','male',NULL,NULL,CURDATE(),1,NOW(),NOW()),
(3,'P202607270003','Raymundo Upton',NULL,'+1-929-835-2469',28,'year','male',NULL,NULL,CURDATE(),1,NOW(),NOW()),
(4,'P202607270004','Lenny Weissnat',NULL,'1-484-236-3246',52,'year','female',NULL,NULL,CURDATE(),1,NOW(),NOW()),
(5,'P202607270005','Jesse Murphy',NULL,'580.439.9985',33,'year','male',NULL,NULL,CURDATE(),1,NOW(),NOW()),
(6,'P202607270006','Grover Weber',NULL,'1-930-756-0203',61,'year','male',NULL,NULL,CURDATE(),1,NOW(),NOW()),
(7,'P202607270007','Miss Marjolaine Rodriguez MD',NULL,'(463) 596-1611',47,'year','female',NULL,NULL,CURDATE(),1,NOW(),NOW());

-- Ready Treatment example (Common Fever template)
INSERT INTO `ready_treatments` (`id`,`name`,`disease`,`description`,`doctor_id`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES
(1,'Common Fever','Viral Fever','Standard medication for viral fever',1,1,1,NOW(),NOW());

INSERT INTO `ready_treatment_items` (`ready_treatment_id`,`product_id`,`drug_name`,`form_type`,`strength`,`timing`,`timing_multiplier`,`times_of_day`,`meal_relation`,`duration_days`,`instruction`,`created_at`,`updated_at`) VALUES
(1,1,'Paracetamol 500mg','tablet','500mg','TDS',3,'[\"Morning\",\"Afternoon\",\"Night\"]','after',3,'Take after meals',NOW(),NOW()),
(1,11,'Cetirizine 10mg','tablet','10mg','BD',2,'[\"Morning\",\"Night\"]','after',3,'Take at night if needed',NOW(),NOW()),
(1,6,'Omeprazole 20mg','capsule','20mg','OD',1,'[\"Morning\"]','before',3,'Take before breakfast',NOW(),NOW());

-- Demo prescription
INSERT INTO `prescriptions` (`id`,`prescription_number`,`patient_id`,`doctor_id`,`prescription_date`,`status`,`sale_status`,`patient_name`,`patient_age`,`patient_phone`,`patient_code`,`medicine_cost`,`doctor_fee`,`discount`,`total_fee`,`paid_amount`,`due_amount`,`payment_status`,`payment_method`,`diagnosis`,`created_by`,`created_at`,`updated_at`) VALUES
(1,'RX-20260727-00001',1,1,'2026-07-27','active','unsold','Elmer Stamm',39,'+1-419-428-0787','P202607270001',75.00,500.00,0.00,575.00,575.00,0.00,'paid','cash','Viral fever with cold',1,NOW(),NOW());

INSERT INTO `prescription_items` (`prescription_id`,`product_id`,`batch_id`,`drug_name`,`form_type`,`strength`,`timing`,`timing_multiplier`,`times_of_day`,`meal_relation`,`duration_days`,`quantity`,`instruction`,`unit_price`,`discount`,`total`,`stock_deducted`,`created_at`,`updated_at`) VALUES
(1,1,1,'Paracetamol 500mg','tablet','500mg','TDS',3,'[\"Morning\",\"Afternoon\",\"Night\"]','after',3,9,'Take after meals',5.00,0.00,45.00,1,NOW(),NOW()),
(1,11,11,'Cetirizine 10mg','tablet','10mg','BD',2,'[\"Morning\",\"Night\"]','after',3,6,'Take at night',4.00,0.00,24.00,1,NOW(),NOW()),
(1,6,6,'Omeprazole 20mg','capsule','20mg','OD',1,'[\"Morning\"]','before',3,3,'Before breakfast',7.00,0.00,21.00,0,NOW(),NOW());

-- Demo sale (paid)
INSERT INTO `sales` (`id`,`invoice_number`,`sale_date`,`prescription_id`,`patient_id`,`doctor_id`,`customer_type`,`customer_name`,`customer_phone`,`subtotal`,`discount`,`tax`,`doctor_fee`,`total`,`paid_amount`,`due_amount`,`change_amount`,`payment_status`,`payment_method`,`cash_account_id`,`created_by`,`created_at`,`updated_at`) VALUES
(1,'INV-20260727-00001','2026-07-27',1,1,1,'patient','Elmer Stamm','+1-419-428-0787',75.00,0.00,0.00,500.00,575.00,575.00,0.00,0.00,'paid','cash',1,1,NOW(),NOW());

INSERT INTO `sale_items` (`sale_id`,`product_id`,`batch_id`,`prescription_item_id`,`product_name`,`batch_number`,`quantity`,`unit_price`,`discount`,`tax`,`total`,`created_at`,`updated_at`) VALUES
(1,1,1,1,'Paracetamol 500mg','BPARA001',9,5.00,0,0,45.00,NOW(),NOW()),
(1,11,11,2,'Cetirizine 10mg','BCET001',6,4.00,0,0,24.00,NOW(),NOW()),
(1,6,6,3,'Omeprazole 20mg','BOME001',3,7.00,0,0,21.00,NOW(),NOW());

-- Demo purchase
INSERT INTO `purchases` (`id`,`invoice_number`,`vendor_id`,`purchase_date`,`subtotal`,`discount`,`tax`,`shipping`,`total`,`paid_amount`,`due_amount`,`payment_status`,`payment_method`,`created_by`,`created_at`,`updated_at`) VALUES
(1,'PI-20260727-00001',1,'2026-07-27',125000.00,0.00,0.00,0.00,125000.00,125000.00,0.00,'paid','cash',1,NOW(),NOW());

INSERT INTO `purchase_items` (`purchase_id`,`product_id`,`purchase_quantity`,`purchase_unit_id`,`pieces_per_unit`,`total_pieces`,`purchase_price`,`unit_cost`,`selling_price`,`batch_number`,`expiry_date`,`discount`,`tax`,`total`,`created_at`,`updated_at`) VALUES
(1,1,50,6,1000,50000,2500.00,2.50,5.00,'BPARA001','2028-06-01',0.00,0.00,125000.00,NOW(),NOW());

-- Demo cash transaction
INSERT INTO `cash_transactions` (`cash_account_id`,`type`,`amount`,`balance_after`,`source_type`,`source_id`,`reference`,`note`,`transaction_date`,`created_by`,`created_at`,`updated_at`) VALUES
(1,'income',575.00,1000575.00,'App\\Models\\Sale',1,'INV-20260727-00001','Sale payment','2026-07-27',1,NOW(),NOW());

-- Update cash account balance
UPDATE `cash_accounts` SET `balance` = 1000575.00 WHERE `id` = 1;

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

-- =====================================================================
-- DONE!
-- =====================================================================
SELECT 'ClinicMS database imported successfully!' AS status;
SELECT CONCAT('Admin login: admin@clinicms.test / password') AS login_info;
-- =====================================================================
-- UPDATE: Multi-Patient Prescriptions + Radiology/Extra Tests
-- ---------------------------------------------------------------------
-- Run this if you already imported an older clinicms_database.sql.
-- For a fresh install, this is already included in the latest dump.
--
-- phpMyAdmin: select clinicms -> SQL -> paste -> Go
-- =====================================================================

USE `clinicms`;

-- 1. Multi-patient support
CREATE TABLE IF NOT EXISTS `prescription_patients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prescription_id` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `doctor_id` bigint unsigned DEFAULT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_age` int DEFAULT NULL,
  `patient_phone` varchar(255) DEFAULT NULL,
  `patient_code` varchar(255) DEFAULT NULL,
  `diagnosis` text,
  `precautions` text,
  `next_visit` text,
  `medicine_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `doctor_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `radiology_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pp_prescription` (`prescription_id`),
  KEY `idx_pp_patient` (`patient_id`),
  CONSTRAINT `fk_pp_prescription` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pp_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_pp_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Radiology master
CREATE TABLE IF NOT EXISTS `radiology_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `category` varchar(255) DEFAULT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rad_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Radiology assigned per prescription patient
CREATE TABLE IF NOT EXISTS `prescription_patient_radiology` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prescription_patient_id` bigint unsigned NOT NULL,
  `radiology_test_id` bigint unsigned DEFAULT NULL,
  `test_name` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ppr_pp` (`prescription_patient_id`),
  CONSTRAINT `fk_ppr_pp` FOREIGN KEY (`prescription_patient_id`) REFERENCES `prescription_patients`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ppr_test` FOREIGN KEY (`radiology_test_id`) REFERENCES `radiology_tests`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add multi-patient link + instruction note to prescription_items
SET @dbname = DATABASE();
SET @tablename = 'prescription_items';
SET @columnname = 'prescription_patient_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` bigint unsigned NULL AFTER `prescription_id`, ADD CONSTRAINT `fk_pi_pp` FOREIGN KEY (`', @columnname, '`) REFERENCES `prescription_patients`(`id`) ON DELETE SET NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'instruction_note';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` text NULL AFTER `instruction`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 5. Add radiology_cost column to prescriptions
SET @tablename = 'prescriptions';
SET @columnname = 'radiology_cost';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` decimal(12,2) NOT NULL DEFAULT 0.00 AFTER `doctor_fee`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 6. Seed radiology tests (ECG=500, X-RAY=1000, etc.)
INSERT INTO `radiology_tests` (`id`,`name`,`code`,`price`,`category`,`sort_order`,`is_active`,`created_at`,`updated_at`) VALUES
(1,'X-RAY','XR',1000.00,'Radiology',1,1,NOW(),NOW()),
(2,'ECG (Heart Test)','ECG',500.00,'Cardiology',2,1,NOW(),NOW()),
(3,'RLE (Resting 12 lead ECG)','RLE',500.00,'Cardiology',3,1,NOW(),NOW()),
(4,'USG OBS (Ultrasound Pregnancy)','USG',1500.00,'Radiology',4,1,NOW(),NOW()),
(5,'2D Echo','2DE',2500.00,'Cardiology',5,1,NOW(),NOW()),
(6,'DEXA Bone Densitometry','DEXA',3000.00,'Radiology',6,1,NOW(),NOW()),
(7,'Mammography','MAM',2000.00,'Radiology',7,1,NOW(),NOW()),
(8,'CT Scan','CT',5000.00,'Radiology',8,1,NOW(),NOW()),
(9,'MRI','MRI',8000.00,'Radiology',9,1,NOW(),NOW()),
(10,'Blood Test - FBC','FBC',350.00,'Laboratory',10,1,NOW(),NOW()),
(11,'Blood Test - Fasting Blood Sugar','FBS',250.00,'Laboratory',11,1,NOW(),NOW()),
(12,'Urine Full Report','UFR',200.00,'Laboratory',12,1,NOW(),NOW()),
(13,'Lipid Profile','LIPID',800.00,'Laboratory',13,1,NOW(),NOW()),
(14,'Liver Function Test','LFT',900.00,'Laboratory',14,1,NOW(),NOW()),
(15,'Thyroid Profile','TSH',700.00,'Laboratory',15,1,NOW(),NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name);

SELECT 'Multi-patient + Radiology tables added successfully!' AS status;
SELECT COUNT(*) AS radiology_tests_count FROM radiology_tests;
