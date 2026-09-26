-- ClinicMS Expenses Module
-- Import once using phpMyAdmin when migrations are not used.

CREATE TABLE IF NOT EXISTS `expense_categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `color` VARCHAR(20) NOT NULL DEFAULT '#ef4444',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_category_id` BIGINT UNSIGNED NOT NULL,
  `expense_date` DATE NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `payment_method` VARCHAR(30) NOT NULL DEFAULT 'cash',
  `reference` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_category_date_index` (`expense_category_id`, `expense_date`),
  KEY `expenses_created_by_foreign` (`created_by`),
  CONSTRAINT `expenses_category_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `expense_categories` (`name`, `description`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
('Rent', 'Building or chamber rent', '#ef4444', 1, NOW(), NOW()),
('Electricity', 'Electricity bills', '#f59e0b', 1, NOW(), NOW()),
('Water', 'Water bills', '#3b82f6', 1, NOW(), NOW()),
('Salaries', 'Staff salaries and wages', '#8b5cf6', 1, NOW(), NOW()),
('Transport', 'Transport and delivery costs', '#10b981', 1, NOW(), NOW()),
('Maintenance', 'Repairs and maintenance', '#64748b', 1, NOW(), NOW()),
('Other', 'Other operating expenses', '#ec4899', 1, NOW(), NOW());
