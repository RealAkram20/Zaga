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
    email VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
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
    credit_available TINYINT(1) DEFAULT 1,
    default_apr DECIMAL(5,2) DEFAULT 0.00,
    credit_terms_months VARCHAR(50) DEFAULT '3,6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5 products per category (30 total)
INSERT INTO products (title, category_id, price, original_price, discount, rating, reviews, description, features, sku, warranty, in_stock, stock, image) VALUES

-- Laptops (category_id = 1)
('HP Pavilion 15 Laptop', 1, 2590000, 2960000, 13, 4.5, 312, 'The HP Pavilion 15 delivers everyday performance with a vibrant FHD display, long battery life, and a slim design. Ideal for students and professionals.', '["Intel Core i5-1235U processor","16GB DDR4 RAM","512GB NVMe SSD","15.6-inch FHD IPS display","Backlit keyboard & Wi-Fi 6"]', 'HP-PAV15-001', '1 Year', 1, 25, 'images/l1.jpg'),
('Lenovo IdeaPad 5 Pro 14"', 1, 3150000, NULL, NULL, 4.7, 489, 'A premium thin-and-light laptop with a stunning 2.8K OLED display and AMD Ryzen 5 power. Perfect for creative work and on-the-go productivity.', '["AMD Ryzen 5 7530U","16GB LPDDR4X RAM","512GB SSD","14-inch 2.8K OLED display","Thunderbolt 4 port"]', 'LN-IP5P-002', '2 Years', 1, 18, 'images/l4.jpg'),
('Dell Inspiron 15 3000', 1, 1960000, NULL, NULL, 4.2, 205, 'Reliable everyday laptop built for home and office use. Sturdy build, fast boot times, and a comfortable full-size keyboard.', '["Intel Core i3-1215U processor","8GB DDR4 RAM","256GB SSD","15.6-inch HD display","Windows 11 Home"]', 'DL-INS15-003', '1 Year', 1, 40, 'images/l7.jpg'),
('Apple MacBook Air M2', 1, 5180000, NULL, NULL, 4.9, 1024, 'Supercharged by the Apple M2 chip, the MacBook Air features an incredibly thin design, Liquid Retina display, and all-day battery life.', '["Apple M2 chip (8-core CPU)","8GB unified memory","256GB SSD storage","13.6-inch Liquid Retina display","MagSafe charging & Touch ID"]', 'AP-MBA-M2-004', '1 Year', 1, 12, 'images/l9.jpg'),
('Asus VivoBook 14 X1404', 1, 2220000, 2480000, 11, 4.3, 178, 'Compact and powerful, the Asus VivoBook 14 combines portability with solid performance — great for students on a budget.', '["Intel Core i5-1235U","8GB DDR4 RAM","512GB NVMe SSD","14-inch FHD display","Fingerprint sensor & USB-C"]', 'AS-VB14-005', '1 Year', 1, 30, 'images/l12.jpg'),

-- Desktops (category_id = 2)
('Dell OptiPlex 7090 Desktop', 2, 3700000, NULL, NULL, 4.6, 267, 'Enterprise-grade compact desktop for office and business use. Quiet, efficient, and built to last with Dell''s legendary reliability.', '["Intel Core i7-10700 (8-core)","16GB DDR4 RAM","512GB SSD + 1TB HDD","Intel UHD 630 graphics","USB-C, HDMI, DisplayPort"]', 'DL-OPT7090-006', '3 Years', 1, 15, 'images/d1.jpg'),
('HP Pavilion TG01 Gaming Desktop', 2, 4440000, 4810000, 8, 4.5, 349, 'Power through demanding games and creative tasks with the HP Pavilion TG01. Features a discrete GPU and fast DDR5 memory for smooth performance.', '["AMD Ryzen 5 7600X","16GB DDR5 RAM","1TB NVMe SSD","NVIDIA GeForce RTX 3060","USB 3.2 Gen 2 & Wi-Fi 6"]', 'HP-PVTG01-007', '1 Year', 1, 8, 'images/d3.jpg'),
('Lenovo IdeaCentre 5i Tower', 2, 2960000, NULL, NULL, 4.3, 191, 'Versatile mid-tower desktop for everyday computing, multitasking, and light gaming. Easy to upgrade with multiple drive bays.', '["Intel Core i5-13400 processor","8GB DDR5 RAM","512GB SSD","Intel UHD 730 graphics","4x USB-A & USB-C front ports"]', 'LN-IC5I-008', '1 Year', 1, 20, 'images/d6.jpg'),
('Apple Mac Mini M2 (2023)', 2, 4070000, NULL, NULL, 4.8, 532, 'Incredibly compact yet astonishingly capable. The Mac Mini M2 fits in the palm of your hand and powers through pro-level workloads silently.', '["Apple M2 chip (8-core CPU/10-core GPU)","8GB unified memory","256GB SSD","HDMI 2.0 & two Thunderbolt 4 ports","Wi-Fi 6E & Bluetooth 5.3"]', 'AP-MMINI-M2-009', '1 Year', 1, 10, 'images/d8.jpg'),
('Asus ROG Strix G15 Gaming Desktop', 2, 8510000, 9250000, 8, 4.7, 423, 'The ultimate gaming fortress. ROG Strix G15 packs flagship-tier components into a bold chassis with stunning RGB lighting and liquid cooling.', '["Intel Core i9-13900K","32GB DDR5 RAM","2TB NVMe SSD","NVIDIA GeForce RTX 4080","ROG ARGB liquid cooling system"]', 'AS-ROGSTX-010', '2 Years', 1, 5, 'images/d11.jpg'),

-- Smartphones & Tablets (category_id = 3)
('Apple iPad 10th Generation', 3, 1850000, NULL, NULL, 4.8, 876, 'Redesigned with a vibrant 10.9-inch Liquid Retina display, A14 Bionic chip, and USB-C — perfect for creativity and entertainment.', '["Apple A14 Bionic chip","64GB storage","10.9-inch Liquid Retina display","12MP front & rear cameras","USB-C, Wi-Fi 6 & optional 5G"]', 'AP-IPAD10-011', '1 Year', 1, 35, 'images/t1.jpg'),
('Samsung Galaxy Tab S9 FE', 3, 1480000, 1665000, 11, 4.5, 512, 'Samsung Galaxy Tab S9 FE brings the premium Galaxy Tab S9 experience at an accessible price with IP68 resistance and included S Pen.', '["Exynos 1380 processor","6GB RAM / 128GB storage","10.9-inch TFT LCD display","S Pen included","IP68 water & dust resistant"]', 'SM-GTABS9FE-012', '1 Year', 1, 28, 'images/t3.jpg'),
('Lenovo Tab P12 Pro', 3, 2220000, NULL, NULL, 4.4, 203, 'Professional-grade tablet with a stunning 12.6-inch AMOLED display, quad speakers, and a powerful Snapdragon chip for productive pros.', '["Snapdragon 870 5G","8GB RAM / 256GB storage","12.6-inch AMOLED 2K display","Quad JBL speakers","Precision Pen 3 included"]', 'LN-TABP12-013', '1 Year', 1, 14, 'images/t6.jpg'),
('Microsoft Surface Pro 9', 3, 4810000, 5180000, 7, 4.6, 318, 'The most flexible laptop/tablet hybrid. Surface Pro 9 with 12th Gen Intel Core, a high-refresh 120Hz display, and Thunderbolt 4 for professionals.', '["Intel Core i5-1235U","8GB LPDDR5 RAM","256GB SSD","13-inch PixelSense 120Hz display","Thunderbolt 4 & Surface Connect"]', 'MS-SURPRO9-014', '1 Year', 1, 9, 'images/t8.jpg'),
('Amazon Fire HD 10 Tablet', 3, 555000, NULL, NULL, 4.1, 1105, 'Great value tablet for streaming, reading, and browsing. The Fire HD 10 features a large Full HD display and 12-hour battery life.', '["Octa-core 2.0GHz processor","3GB RAM / 32GB storage","10.1-inch Full HD display","12-hour battery life","Alexa built-in & USB-C"]', 'AZ-FIREHD10-015', '1 Year', 1, 50, 'images/t10.jpg'),

-- Accessories (category_id = 4)
('Sony WH-1000XM5 Headphones', 4, 1295000, 1480000, 13, 4.9, 2310, 'Industry-leading noise cancellation meets exceptional sound quality. The WH-1000XM5 headphones are the gold standard for wireless audio.', '["30-hour battery life","Industry-best noise cancellation","Speak-to-Chat auto-pauses music","Hi-Res Audio certified","Multipoint Bluetooth connection"]', 'SN-WH1000XM5-016', '1 Year', 1, 45, 'images/h1.jpg'),
('Anker 65W USB-C GaN Charger', 4, 185000, NULL, NULL, 4.7, 889, 'Charge your laptop, phone, and tablet simultaneously with Anker\'s compact 65W 3-port GaN charger — foldable plug and travel-ready design.', '["65W total output","3 ports: 2x USB-C + 1x USB-A","GaN II technology (runs cooler)","Foldable plug","Compatible with MacBook, iPad, Android"]', 'AN-65WGAN-017', '1 Year', 1, 100, 'images/h3.jpg'),
('Belkin 7-in-1 USB-C Hub', 4, 278000, 315000, 12, 4.4, 456, 'Expand your laptop connectivity with Belkin\'s compact 7-in-1 hub featuring 4K HDMI, SD card readers, and USB-A/C ports.', '["4K HDMI output","100W Power Delivery pass-through","SD & microSD card slots","2x USB-A 3.0 + 1x USB-C data","Compact bus-powered design"]', 'BK-7IN1HUB-018', '2 Years', 1, 60, 'images/h4.jpg'),
('Anker PowerCore 26800 Power Bank', 4, 370000, NULL, NULL, 4.6, 1200, 'Never run out of power with Anker\'s 26800mAh high-capacity power bank. Charge three devices simultaneously and keep going for days.', '["26800mAh capacity","Charges 3 devices at once","Dual USB-A + USB-C ports","30W fast charging output","LED battery indicator"]', 'AN-PC26800-019', '1 Year', 1, 75, 'images/h5.jpg'),
('Laptop Sleeve Bag 15.6"', 4, 92500, NULL, NULL, 4.2, 340, 'Premium water-resistant neoprene sleeve with accessory pockets. Fits most 14-15.6 inch laptops with a snug, scratch-free interior lining.', '["Fits 14-15.6 inch laptops","Water-resistant neoprene material","Scratch-free interior","Front pocket for accessories","Slim & lightweight design"]', 'GN-SLVBAG-020', '6 Months', 1, 200, 'images/h7.jpg'),

-- Printers (category_id = 5)
('HP LaserJet Pro M404dn', 5, 1110000, NULL, NULL, 4.6, 387, 'Fast, reliable mono laser printer built for busy offices. Prints up to 40 pages per minute with automatic two-sided printing.', '["40 ppm mono printing","Automatic duplex printing","Wired network & USB","250-sheet paper tray","JetIntelligence toner technology"]', 'HP-LJM404-021', '1 Year', 1, 22, 'images/p1.jpg'),
('Epson EcoTank L3250 Color Printer', 5, 740000, 850000, 13, 4.5, 621, 'Print thousands of pages at a fraction of the cost with Epson\'s revolutionary ink tank system. Wireless printing from any device.', '["Color inkjet, scan & copy","Ink tank — ultra-low cost per page","Wi-Fi & Wi-Fi Direct","Mobile printing (Epson Smart Panel)","Up to 4500 mono / 7500 color pages per set"]', 'EP-ECTL3250-022', '1 Year', 1, 38, 'images/p2.jpg'),
('Canon PIXMA G3420 Multifunction', 5, 680000, NULL, NULL, 4.3, 298, 'All-in-one print, scan, copy with Canon\'s MegaTank ink system. Wireless connectivity for home and small office use.', '["Print, Scan, Copy","MegaTank refillable ink","Wi-Fi & cloud printing","A4 color inkjet","Compact design"]', 'CN-PIXG3420-023', '1 Year', 1, 45, 'images/p3.jpg'),
('Brother HL-L2350DW Mono Laser', 5, 555000, 610000, 9, 4.4, 512, 'Compact wireless mono laser printer ideal for home offices. Fast printing with automatic duplex and easy wireless setup.', '["Up to 32 ppm printing","Automatic duplex","Wi-Fi & Wi-Fi Direct","250-sheet paper capacity","Toner-save mode"]', 'BR-HLL2350-024', '1 Year', 1, 55, 'images/p4.jpg'),
('HP DeskJet 2720e All-in-One', 5, 278000, NULL, NULL, 4.0, 834, 'Affordable all-in-one printer for occasional home use. Print, scan, and copy wirelessly with HP+ smart features included.', '["Print, Scan, Copy","Wi-Fi & Bluetooth","HP+ enabled (free 6 months Instant Ink)","Compact & lightweight","USB connectivity"]', 'HP-DJ2720-025', '1 Year', 1, 80, 'images/p5.jpg'),

-- Networking Hardware (category_id = 6)
('TP-Link Archer AX73 Wi-Fi 6 Router', 6, 370000, NULL, NULL, 4.7, 612, 'Next-generation Wi-Fi 6 router with blazing AX5400 speeds. Handles dozens of connected devices with ease for large homes.', '["AX5400 dual-band Wi-Fi 6","6 high-gain antennas","OFDMA & MU-MIMO","HomeShield security","Easy Tether app setup"]', 'TP-AX73-026', '2 Years', 1, 48, 'images/hd1.jpg'),
('Netgear GS308 8-Port Gigabit Switch', 6, 185000, NULL, NULL, 4.6, 1423, 'Plug-and-play unmanaged Gigabit switch for expanding your wired network. Silent fanless design and solid metal build.', '["8 Gigabit Ethernet ports","Plug & play — no configuration","Fanless silent operation","Metal housing","Auto-sensing ports"]', 'NG-GS308-027', '3 Years', 1, 90, 'images/hd2.jpg'),
('TP-Link TL-SG1016D 16-Port Switch', 6, 278000, 315000, 12, 4.5, 289, 'Rack-mountable 16-port Gigabit switch for office and SMB network expansion. Zero-configuration setup with robust metal chassis.', '["16 Gigabit RJ45 ports","Rack-mountable (1U)","Plug & play","IEEE 802.1p QoS","19-inch rack ears included"]', 'TP-SG1016D-028', '3 Years', 1, 35, 'images/hd3.jpg'),
('Ubiquiti UniFi AP AC Lite', 6, 463000, NULL, NULL, 4.8, 876, 'Enterprise-grade wireless access point delivering fast 802.11ac coverage. Managed via the free UniFi controller software.', '["802.11ac dual-band","Up to 250 concurrent users","Passive PoE powered","UniFi controller software (free)","Sleek wall/ceiling mount"]', 'UB-UAPLITE-029', '1 Year', 1, 20, 'images/hd4.jpg'),
('D-Link DAP-1620 Range Extender', 6, 148000, 167000, 11, 4.2, 445, 'Boost your Wi-Fi signal to eliminate dead zones. The DAP-1620 connects to your existing router and extends coverage up to 300m2.', '["AC1200 dual-band extender","WPS one-button setup","Ethernet port for wired device","Smart signal indicator","Compact wall-plug design"]', 'DL-DAP1620-030', '1 Year', 1, 65, 'images/hd5.jpg');

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
    credit_terms_months VARCHAR(50) DEFAULT '3,6',
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
-- 5. CUSTOMERS
-- ============================================================
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '' UNIQUE,
    phone VARCHAR(50) DEFAULT '',
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT '',
    country VARCHAR(100) DEFAULT 'Uganda',
    password_hash VARCHAR(255) DEFAULT NULL,
    cart_json TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 6. ORDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT DEFAULT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) DEFAULT '',
    customer_phone VARCHAR(50) DEFAULT '',
    shipping_address TEXT DEFAULT NULL,
    total_now DECIMAL(15,2) DEFAULT 0,
    total_full DECIMAL(15,2) DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
    status ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    items_json TEXT,
    admin_notes TEXT DEFAULT NULL,
    schedule_json TEXT,
    payments_made_json TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 7. REVIEWS
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
-- 8. TESTIMONIALS
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

INSERT INTO testimonials (name, role, image, content, rating, status, display_order) VALUES
('Amina N.', 'Small business owner', 'images/user1.jpg', 'The credit option made it possible to upgrade my laptop without draining savings. The deposit was easy and payments were clear and they had excellent service.', 5, 1, 1),
('David M.', 'Freelancer', 'images/user2.jpg', 'I bought a monitor on a 3-month plan. The monthly payments fit my budget and customer support was very helpful with schedule questions.', 5, 1, 2),
('Grace K.', 'Student', 'images/user3.jpg', 'As a student, the deposit and installments meant I could get a tablet for remote classes straight away. The schedule was transparent and easy to follow.', 5, 1, 3),
('Samuel L.', 'IT Consultant', 'images/user4.jpg', 'Clear terms, straightforward checkout and helpful reminders paying over six months made upgrading our office PCs achievable.', 5, 1, 4);
