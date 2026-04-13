-- ============================================================
-- Migration v4: Fix utf8mb4 charset for emoji icon columns
-- Run once in phpMyAdmin on the live database
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Fix charset on categories table and icon column
ALTER TABLE categories
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Fix charset on courses table and icon column
ALTER TABLE courses
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Fix charset on testimonials table (content may have special chars)
ALTER TABLE testimonials
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Fix all other tables too
ALTER TABLE admin_users         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE password_reset_tokens CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE products            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE customers           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE orders              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE reviews             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Re-insert correct emoji icons for categories
UPDATE categories SET icon = '💻' WHERE name = 'Laptops';
UPDATE categories SET icon = '🖥️' WHERE name = 'Desktops';
UPDATE categories SET icon = '📱' WHERE name = 'Smartphones & Tablets';
UPDATE categories SET icon = '🎧' WHERE name = 'Accessories';
UPDATE categories SET icon = '🖨️' WHERE name = 'Printers';
UPDATE categories SET icon = '🌐' WHERE name = 'Networking Hardware';

-- Re-insert correct emoji icons for courses
UPDATE courses SET icon = '🖥️' WHERE sku = 'COURSE-BCL-001';
UPDATE courses SET icon = '📝' WHERE sku = 'COURSE-MSO-002';
UPDATE courses SET icon = '🎨' WHERE sku = 'COURSE-GDF-003';
UPDATE courses SET icon = '🌐' WHERE sku = 'COURSE-WDB-004';
UPDATE courses SET icon = '🔐' WHERE sku = 'COURSE-CSA-005';
UPDATE courses SET icon = '🛠️' WHERE sku = 'COURSE-PMN-006';
UPDATE courses SET icon = '💼' WHERE sku = 'COURSE-BPE-007';
UPDATE courses SET icon = '📊' WHERE sku = 'COURSE-FMG-008';
UPDATE courses SET icon = '📱' WHERE sku = 'COURSE-DMF-009';
