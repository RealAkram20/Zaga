-- ============================================================
-- Zaga Technologies Ltd - Database Schema
-- MySQL Database Setup
-- ============================================================

CREATE DATABASE IF NOT EXISTS zaga_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE zaga_db;

-- ============================================================
-- 1. ADMIN USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username = admin, password = ZagaAdmin2025!
INSERT INTO admin_users (username, password, full_name) VALUES
('admin', '$2y$10$8K1p/YGZ0Q5Y0Q5Y0Q5YGeX5Y0Q5Y0Q5Y0Q5Y0Q5Y0Q5Y0Q5Y0Q5Y', 'Zaga Admin');

-- We'll update this hash properly via PHP on first run

-- ============================================================
-- 2. PRODUCT CATEGORIES
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT '',
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (name, icon, description) VALUES
('Laptops', '💻', 'Portable computing devices for work and entertainment'),
('Desktops', '🖥️', 'High-performance desktop computers and workstations'),
('Smartphones & Tablets', '📱', 'Mobile phones and tablet devices'),
('Accessories', '🎧', 'Computer peripherals, cables, and accessories'),
('Printers', '🖨️', 'Inkjet, laser, and multifunction printers'),
('Networking Hardware', '🌐', 'Routers, switches, and networking equipment');

-- ============================================================
-- 3. PRODUCTS
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    original_price DECIMAL(15,2) DEFAULT NULL,
    discount INT DEFAULT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    reviews INT DEFAULT 0,
    description TEXT,
    features TEXT,
    sku VARCHAR(50) DEFAULT '',
    warranty VARCHAR(100) DEFAULT '',
    in_stock TINYINT(1) DEFAULT 1,
    stock INT DEFAULT 0,
    image VARCHAR(500) DEFAULT '',
    additional_images TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample products
INSERT INTO products (title, category_id, price, original_price, discount, rating, reviews, description, features, sku, warranty, in_stock, stock, image) VALUES
('Astra Air 2025', 1, 1250000, 1500000, 17, 4.5, 234, 'The Astra Air 2025 is a powerful, portable laptop featuring modern processors, vivid display, and long battery life. Perfect for productivity and light content creation.', '["Intel Core i7 CPU","16GB RAM","512GB NVMe SSD","Backlit keyboard","Wi-Fi 6"]', 'TS-00001', '2 Years', 1, 45, 'images/l1.jpg'),
('Orion Pro 2024', 1, 1850000, 2100000, 12, 4.7, 189, 'Premium business laptop with enterprise-grade security and all-day battery. Built for professionals who demand reliability.', '["Intel Core i9 CPU","32GB RAM","1TB NVMe SSD","Fingerprint reader","Thunderbolt 4"]', 'TS-00002', '3 Years', 1, 30, 'images/l2.jpg'),
('Zephyr Slim 2025', 1, 980000, NULL, NULL, 4.3, 156, 'Ultra-thin and lightweight laptop ideal for students and everyday use. Great value for essential computing tasks.', '["AMD Ryzen 5","8GB RAM","256GB SSD","14-inch FHD display","USB-C charging"]', 'TS-00003', '1 Year', 1, 78, 'images/l3.jpg'),
('Nimbus Max 2024', 1, 2200000, 2500000, 12, 4.8, 312, 'High-performance creator laptop with stunning 4K display and dedicated GPU for video editing and 3D work.', '["Intel Core i9","64GB RAM","2TB NVMe SSD","RTX 4060 GPU","16-inch 4K OLED"]', 'TS-00004', '3 Years', 1, 15, 'images/l4.jpg'),
('Vertex Studio 2025', 1, 1600000, NULL, NULL, 4.6, 201, 'Content creation powerhouse with color-accurate display and high-end audio. Perfect for designers and video editors.', '["Apple M3 Pro","16GB RAM","512GB SSD","15-inch Retina","6 speakers"]', 'TS-00005', '2 Years', 1, 52, 'images/l5.jpg'),

('Orion Ranger Desktop 001', 2, 1450000, NULL, NULL, 4.4, 167, 'A high-performance desktop built for gaming, creativity and business. Expandable, serviceable and ready for heavy workloads.', '["High-performance CPU","Discrete GPU","Large cooling system","Multiple storage bays","Upgradeable RAM"]', 'TS-00006', '2 Years', 1, 38, 'images/d1.jpg'),
('Zephyr Titan Desktop 002', 2, 2100000, 2400000, 13, 4.7, 145, 'Enterprise workstation with server-grade reliability and expandable storage. Built for demanding professional applications.', '["Intel Xeon CPU","64GB ECC RAM","Dual NVMe RAID","Quadro GPU","3-Year warranty"]', 'TS-00007', '3 Years', 1, 20, 'images/d2.jpg'),
('Nimbus Core Desktop 003', 2, 850000, NULL, NULL, 4.2, 223, 'Budget-friendly desktop PC for home and office use. Reliable performance for everyday computing needs.', '["Intel Core i5","8GB RAM","500GB HDD + 128GB SSD","Integrated graphics","Wi-Fi built-in"]', 'TS-00008', '1 Year', 1, 65, 'images/d3.jpg'),

('Astra Tab Plus', 3, 650000, 750000, 13, 4.3, 198, 'A sleek tablet for media, reading, and light productivity. Crisp display and long battery life make it a great companion.', '["10.4-inch FHD display","Stylus support","Wi-Fi & LTE","64GB storage","12-hour battery"]', 'TS-00009', '1 Year', 1, 90, 'images/t1.jpg'),
('Vertex Pad Mini', 3, 450000, NULL, NULL, 4.1, 176, 'Compact and portable tablet perfect for reading, browsing, and entertainment on the go.', '["8-inch HD display","4GB RAM","32GB storage","Dual cameras","All-day battery"]', 'TS-00010', '1 Year', 1, 120, 'images/t2.jpg'),
('Photon Smartphone X1', 3, 890000, 1000000, 11, 4.6, 345, 'Flagship smartphone with pro-grade camera system and blazing fast performance for power users.', '["6.7-inch AMOLED","128GB storage","50MP triple camera","5000mAh battery","5G ready"]', 'TS-00011', '1 Year', 1, 55, 'images/t3.jpg'),

('Universal Headset by Nimbus', 4, 85000, NULL, NULL, 4.4, 432, 'Premium wireless headset with noise cancellation. Crystal clear audio for calls and music.', '["Active Noise Cancelling","30-hour battery","Bluetooth 5.2","USB-C charging","Foldable design"]', 'TS-00012', '1 Year', 1, 200, 'images/h1.jpg'),
('Universal Charger by Vertex', 4, 45000, NULL, NULL, 4.2, 567, 'Fast charging adapter compatible with most devices. GaN technology for compact, cool operation.', '["65W GaN charger","USB-C + USB-A","Universal voltage","Compact design","LED indicator"]', 'TS-00013', '1 Year', 1, 300, 'images/h2.jpg'),
('Universal Dock by Photon', 4, 120000, 150000, 20, 4.5, 189, 'Multi-port docking station for laptops. Connect all your peripherals with a single cable.', '["HDMI 4K output","3x USB 3.0","Ethernet port","SD card reader","100W passthrough"]', 'TS-00014', '2 Years', 1, 80, 'images/h3.jpg'),

('Zephyr LaserJet Pro', 5, 750000, NULL, NULL, 4.3, 145, 'High-speed laser printer for office use. Sharp text and fast output for busy workplaces.', '["30 ppm printing","Auto duplex","Wi-Fi & Ethernet","Mobile printing","250-sheet tray"]', 'TS-00015', '2 Years', 1, 35, 'images/p1.jpg'),
('Astra InkTank Color', 5, 480000, 550000, 13, 4.1, 210, 'Economical ink tank color printer with ultra-low cost per page. Perfect for home and small office.', '["Color printing","Ink tank system","Wi-Fi","Scan & Copy","Low cost per page"]', 'TS-00016', '1 Year', 1, 50, 'images/p2.jpg'),

('Photon WiFi Router AC1200', 6, 180000, NULL, NULL, 4.4, 289, 'Dual-band wireless router with wide coverage. Fast and reliable internet for homes and small offices.', '["AC1200 dual-band","4 LAN ports","MU-MIMO","Parental controls","Easy app setup"]', 'TS-00017', '2 Years', 1, 95, 'images/hd1.jpg'),
('Nimbus Managed Switch 8-Port', 6, 250000, NULL, NULL, 4.5, 134, 'Enterprise-grade managed switch for small to medium network setups with VLAN support.', '["8 Gigabit ports","VLAN support","QoS","Web management","Rack mountable"]', 'TS-00018', '3 Years', 1, 42, 'images/hd2.jpg');

-- ============================================================
-- 4. COURSES (Digital Skilling + Entrepreneurship)
-- ============================================================
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_type ENUM('digital_skilling', 'entrepreneurship') NOT NULL,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    description TEXT,
    duration VARCHAR(100) DEFAULT '',
    modules INT DEFAULT 0,
    lessons INT DEFAULT 0,
    level VARCHAR(100) DEFAULT 'Beginner',
    image VARCHAR(500) DEFAULT '',
    icon VARCHAR(50) DEFAULT '',
    sku VARCHAR(50) DEFAULT '',
    in_stock TINYINT(1) DEFAULT 1,
    rating DECIMAL(2,1) DEFAULT 0.0,
    reviews INT DEFAULT 0,
    instructor VARCHAR(255) DEFAULT '',
    credit_available TINYINT(1) DEFAULT 1,
    default_apr DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Digital Skilling Courses
INSERT INTO courses (course_type, title, price, description, duration, modules, lessons, level, icon, sku, rating, reviews, instructor, credit_available, default_apr) VALUES
('digital_skilling', 'Basic Computer Literacy', 200000, 'Master essential computer operations, file management, internet safety, and productivity tools to thrive in the digital world.', '6 Weeks', 8, 40, 'Beginner', '🖥️', 'COURSE-BCL-001', 4.8, 156, 'Certified IT Professionals', 1, 0),
('digital_skilling', 'Microsoft Office Essentials', 200000, 'Master Word, Excel, and PowerPoint for business productivity, data management, and professional presentations.', '6 Weeks', 9, 45, 'Beginner to Intermediate', '📝', 'COURSE-MSO-002', 4.9, 203, 'Microsoft Certified Trainers', 1, 0),
('digital_skilling', 'Graphic Design Fundamentals', 200000, 'Learn design principles, color theory, typography, and master tools like Canva and Adobe Express.', '6 Weeks', 8, 40, 'Beginner', '🎨', 'COURSE-GDF-003', 4.7, 142, 'Professional Graphic Designers', 1, 0),
('digital_skilling', 'Web Development Basics', 200000, 'Build responsive websites with HTML, CSS, and JavaScript from scratch.', '8 Weeks', 10, 50, 'Beginner', '🌐', 'COURSE-WDB-004', 4.9, 187, 'Senior Web Developers', 1, 0),
('digital_skilling', 'Cybersecurity Awareness', 200000, 'Protect your data: master passwords, phishing detection, device security, and online safety.', '5 Weeks', 7, 35, 'Beginner', '🔐', 'COURSE-CSA-005', 4.8, 165, 'Cybersecurity Experts', 1, 0),
('digital_skilling', 'PC Maintenance & Networking', 200000, 'Learn hardware basics, device maintenance, troubleshooting, and small office networking.', '6 Weeks', 8, 40, 'Beginner to Intermediate', '🛠️', 'COURSE-PMN-006', 4.8, 134, 'Hardware & Network Specialists', 1, 0);

-- Entrepreneurship Courses
INSERT INTO courses (course_type, title, price, description, duration, modules, lessons, level, icon, sku, rating, reviews, instructor, credit_available, default_apr) VALUES
('entrepreneurship', 'Business Planning Essentials', 250000, 'Learn to create effective business plans, market analysis, and growth strategies for your startup.', '6 Weeks', 8, 40, 'Beginner', '💼', 'COURSE-BPE-007', 4.6, 98, 'Business Consultants', 1, 0),
('entrepreneurship', 'Financial Management', 250000, 'Master budgeting, accounting, bookkeeping, and financial planning for your business success.', '6 Weeks', 8, 40, 'Beginner to Intermediate', '📊', 'COURSE-FMG-008', 4.7, 112, 'Certified Accountants', 1, 0),
('entrepreneurship', 'Digital Marketing Fundamentals', 250000, 'Social media marketing, SEO, content creation, and online advertising strategies for business growth.', '5 Weeks', 7, 35, 'Beginner', '📱', 'COURSE-DMF-009', 4.8, 134, 'Digital Marketing Experts', 1, 0);

-- ============================================================
-- 5. ORDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) DEFAULT '',
    customer_phone VARCHAR(50) DEFAULT '',
    total_now DECIMAL(15,2) DEFAULT 0,
    total_full DECIMAL(15,2) DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    status ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    items_json TEXT,
    schedule_json TEXT,
    payments_made_json TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
