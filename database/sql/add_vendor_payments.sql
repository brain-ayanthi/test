-- =====================================================================
-- VENDOR PAYMENTS: Outstanding balance & payments tracking
-- Run this in phpMyAdmin -> clinicms -> SQL
-- =====================================================================
USE `clinicms`;

CREATE TABLE IF NOT EXISTS `vendor_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `purchase_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `reference` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vp_vendor_date` (`vendor_id`,`payment_date`),
  KEY `idx_vp_purchase` (`purchase_id`),
  CONSTRAINT `fk_vp_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vp_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vp_user` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'vendor_payments table created!' AS status;
