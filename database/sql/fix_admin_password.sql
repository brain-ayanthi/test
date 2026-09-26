-- =====================================================================
-- PASSWORD FIX - Run this if you already imported the database but
-- get "Invalid credentials" when logging in.
--
-- This sets the admin password to: password
-- Email: admin@clinicms.test
--
-- Usage (phpMyAdmin): Select clinicms database -> SQL -> paste -> Go
-- Or command line:
--   mysql -u root -p clinicms < fix_admin_password.sql
-- =====================================================================

USE `clinicms`;

UPDATE `users`
SET `password` = '$2y$12$70oISlEdklu3koGmVBwWN.ACOVlBAmDIzA17RH1oDlgWq76s7ps0W',
    `updated_at` = NOW()
WHERE `email` = 'admin@clinicms.test';

-- Verify it worked
SELECT id, name, email, LEFT(password, 20) AS password_prefix FROM users WHERE email = 'admin@clinicms.test';
