-- ============================================================
-- Zaga Technologies Ltd - Migration V2
-- Adds: customers, reviews, testimonials tables
-- Alters: orders table (add customer_id, admin_notes)
-- ============================================================

USE zaga_db;

-- ============================================================
-- 1. CUSTOMERS
-- ============================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) DEFAULT '',
    address TEXT,
    city VARCHAR(100) DEFAULT '',
    country VARCHAR(100) DEFAULT 'Uganda',
    password_hash VARCHAR(255) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Add password_hash column if table already exists without it
-- (safe to run multiple times)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'password_hash');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER country', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 2. REVIEWS
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT DEFAULT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) DEFAULT '',
    item_type ENUM('product','course') NOT NULL DEFAULT 'product',
    item_id INT NOT NULL,
    rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    review_text TEXT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 3. TESTIMONIALS
-- ============================================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(255) DEFAULT '',
    image VARCHAR(500) DEFAULT '',
    content TEXT NOT NULL,
    rating INT DEFAULT 5,
    status TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed existing testimonials
INSERT INTO testimonials (name, role, image, content, rating, status, display_order) VALUES
('Amina N.', 'Small business owner', 'images/user1.jpg', 'The credit option made it possible to upgrade my laptop without draining savings. The deposit was easy and payments were clear and they had excellent service.', 5, 1, 1),
('David M.', 'Freelancer', 'images/user2.jpg', 'I bought a monitor on a 3-month plan. The monthly payments fit my budget and customer support was very helpful with schedule questions.', 5, 1, 2),
('Grace K.', 'Student', 'images/user3.jpg', 'As a student, the deposit and installments meant I could get a tablet for remote classes straight away. The schedule was transparent and easy to follow.', 5, 1, 3),
('Samuel L.', 'IT Consultant', 'images/user4.jpg', 'Clear terms, straightforward checkout and helpful reminders paying over six months made upgrading our office PCs achievable.', 5, 1, 4);
