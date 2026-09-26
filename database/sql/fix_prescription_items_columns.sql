-- =====================================================================
-- FIX: Add missing prescription_patient_id and instruction_note columns
-- to prescription_items table.
--
-- Run this if Qty/Total values are missing in prescription details.
-- phpMyAdmin: select clinicms -> SQL -> paste -> Go
-- =====================================================================

USE `clinicms`;

-- Add prescription_patient_id if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'clinicms' AND TABLE_NAME = 'prescription_items'
    AND COLUMN_NAME = 'prescription_patient_id');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `prescription_items` ADD COLUMN `prescription_patient_id` BIGINT UNSIGNED NULL AFTER `prescription_id`',
    'SELECT "prescription_patient_id already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index if it doesn't exist
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = 'clinicms' AND TABLE_NAME = 'prescription_items'
    AND INDEX_NAME = 'prescription_items_patient_id_foreign');

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `prescription_items` ADD INDEX `prescription_items_patient_id_foreign` (`prescription_patient_id`)',
    'SELECT "index already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key if it doesn't exist
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'clinicms' AND TABLE_NAME = 'prescription_items'
    AND CONSTRAINT_NAME = 'prescription_items_patient_id_foreign');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `prescription_items` ADD CONSTRAINT `prescription_items_patient_id_foreign` FOREIGN KEY (`prescription_patient_id`) REFERENCES `prescription_patients`(`id`) ON DELETE CASCADE',
    'SELECT "foreign key already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add instruction_note column if it doesn't exist
SET @col_exists2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'clinicms' AND TABLE_NAME = 'prescription_items'
    AND COLUMN_NAME = 'instruction_note');

SET @sql = IF(@col_exists2 = 0,
    'ALTER TABLE `prescription_items` ADD COLUMN `instruction_note` TEXT NULL AFTER `instruction`',
    'SELECT "instruction_note already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Make prescriptions.patient_id nullable (multi-patient support)
ALTER TABLE `prescriptions` MODIFY `patient_id` BIGINT UNSIGNED NULL;

SELECT 'prescription_items columns fixed successfully!' AS status;
