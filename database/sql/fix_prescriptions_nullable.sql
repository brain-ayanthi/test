-- =====================================================================
-- FIX: Make prescription patient_id/doctor_id nullable
-- ---------------------------------------------------------------------
-- The multi-patient system stores patients in prescription_patients,
-- so the top-level prescriptions.patient_id column must allow NULL.
-- Run this if you get: "Field 'patient_id' doesn't have a default value"
-- =====================================================================

USE `clinicms`;

ALTER TABLE `prescriptions`
    MODIFY `patient_id` BIGINT UNSIGNED NULL,
    MODIFY `doctor_id` BIGINT UNSIGNED NULL,
    MODIFY `ready_treatment_id` BIGINT UNSIGNED NULL,
    MODIFY `patient_name` VARCHAR(255) NULL,
    MODIFY `patient_age` INT NULL,
    MODIFY `patient_phone` VARCHAR(255) NULL,
    MODIFY `patient_code` VARCHAR(255) NULL;

SELECT 'prescriptions columns are now nullable - multi-patient saving works!' AS status;
