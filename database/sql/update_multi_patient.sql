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
