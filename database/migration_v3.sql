-- ============================================================
-- Zaga Technologies - Database Migration V3
-- Adds credit columns to products and courses tables
-- Safe to re-run: uses IF NOT EXISTS
-- ============================================================

-- Products: credit columns (were missing from original schema)
ALTER TABLE products ADD COLUMN IF NOT EXISTS credit_available TINYINT(1) DEFAULT 1;
ALTER TABLE products ADD COLUMN IF NOT EXISTS default_apr DECIMAL(5,2) DEFAULT 0.00;
ALTER TABLE products ADD COLUMN IF NOT EXISTS credit_terms_months VARCHAR(50) DEFAULT '3,6';

-- Courses: credit_terms_months (credit_available and default_apr already exist)
ALTER TABLE courses ADD COLUMN IF NOT EXISTS credit_terms_months VARCHAR(50) DEFAULT '3,6';
