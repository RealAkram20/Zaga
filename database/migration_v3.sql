-- ============================================================
-- Zaga Technologies - Database Migration V3
-- Adds credit fields to products table
-- ============================================================

-- Add credit configuration fields to products
ALTER TABLE products ADD COLUMN credit_available TINYINT(1) DEFAULT 1;
ALTER TABLE products ADD COLUMN default_apr DECIMAL(5,2) DEFAULT 0.00;
ALTER TABLE products ADD COLUMN credit_terms_months VARCHAR(50) DEFAULT '3,6';
