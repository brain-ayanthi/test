-- ClinicMS: one-time Opening Stock import control table
-- Import this file ONCE in phpMyAdmin before using the Opening Stock CSV importer.

CREATE TABLE IF NOT EXISTS `opening_stock_imports` (
    `id` BIGINT UNSIGNED NOT NULL,
    `imported_at` TIMESTAMP NOT NULL,
    `imported_by` BIGINT UNSIGNED NULL,
    `products_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `file_name` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `opening_stock_imports_imported_by_foreign` (`imported_by`),
    CONSTRAINT `opening_stock_imports_imported_by_foreign`
        FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IMPORTANT:
-- After a successful import, the application inserts the permanent control row with id = 1.
-- Do not delete that row. Deleting it would re-enable the one-time importer.
