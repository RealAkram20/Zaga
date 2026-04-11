<?php
/**
 * Zaga Technologies - Migration V2
 * Run once: http://localhost/Zaga/migrate_v2.php
 * Creates: customers, reviews, testimonials tables
 * Alters: orders table (adds customer_id, admin_notes)
 */

require_once __DIR__ . '/config/database.php';

$results = [];

try {
    $conn = getDbConnection();

    // 1. Create customers table
    $sql = "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(50) DEFAULT '',
        address TEXT,
        city VARCHAR(100) DEFAULT '',
        country VARCHAR(100) DEFAULT 'Uganda',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $conn->query($sql);
    $results[] = ['Customers table', 'OK'];

    // 2. Create reviews table
    $sql = "CREATE TABLE IF NOT EXISTS reviews (
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
    ) ENGINE=InnoDB";
    $conn->query($sql);
    $results[] = ['Reviews table', 'OK'];

    // 3. Create testimonials table
    $sql = "CREATE TABLE IF NOT EXISTS testimonials (
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
    ) ENGINE=InnoDB";
    $conn->query($sql);
    $results[] = ['Testimonials table', 'OK'];

    // 4. Seed testimonials (only if empty)
    $check = $conn->query("SELECT COUNT(*) as cnt FROM testimonials");
    $count = $check->fetch_assoc()['cnt'];
    if ($count == 0) {
        $sql = "INSERT INTO testimonials (name, role, image, content, rating, status, display_order) VALUES
            ('Amina N.', 'Small business owner', 'images/user1.jpg', 'The credit option made it possible to upgrade my laptop without draining savings. The deposit was easy and payments were clear and they had excellent service.', 5, 1, 1),
            ('David M.', 'Freelancer', 'images/user2.jpg', 'I bought a monitor on a 3-month plan. The monthly payments fit my budget and customer support was very helpful with schedule questions.', 5, 1, 2),
            ('Grace K.', 'Student', 'images/user3.jpg', 'As a student, the deposit and installments meant I could get a tablet for remote classes straight away. The schedule was transparent and easy to follow.', 5, 1, 3),
            ('Samuel L.', 'IT Consultant', 'images/user4.jpg', 'Clear terms, straightforward checkout and helpful reminders paying over six months made upgrading our office PCs achievable.', 5, 1, 4)";
        $conn->query($sql);
        $results[] = ['Testimonials seed data', 'OK - 4 testimonials inserted'];
    } else {
        $results[] = ['Testimonials seed data', 'SKIPPED - already has ' . $count . ' records'];
    }

    // 5. Alter customers table - add password_hash column
    $colCheck = $conn->query("SHOW COLUMNS FROM customers LIKE 'password_hash'");
    if ($colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER country");
        $results[] = ['Customers: password_hash column', 'OK - added'];
    } else {
        $results[] = ['Customers: password_hash column', 'SKIPPED - already exists'];
    }

    // 6. Alter orders table - add customer_id column
    $colCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_id'");
    if ($colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN customer_id INT DEFAULT NULL AFTER id");
        $conn->query("ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL");
        $results[] = ['Orders: customer_id column', 'OK - added'];
    } else {
        $results[] = ['Orders: customer_id column', 'SKIPPED - already exists'];
    }

    // 6. Alter orders table - add admin_notes column
    $colCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'admin_notes'");
    if ($colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN admin_notes TEXT AFTER payments_made_json");
        $results[] = ['Orders: admin_notes column', 'OK - added'];
    } else {
        $results[] = ['Orders: admin_notes column', 'SKIPPED - already exists'];
    }

    // 7. Alter orders table - add shipping_address column
    $colCheck = $conn->query("SHOW COLUMNS FROM orders LIKE 'shipping_address'");
    if ($colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN shipping_address TEXT AFTER customer_phone");
        $results[] = ['Orders: shipping_address column', 'OK - added'];
    } else {
        $results[] = ['Orders: shipping_address column', 'SKIPPED - already exists'];
    }

    $conn->close();
} catch (Exception $e) {
    $results[] = ['ERROR', $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zaga - Migration V2</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; background: #f8fafc; }
        h1 { color: #1e40af; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #2563eb; color: white; }
        .ok { color: #16a34a; font-weight: 600; }
        .skip { color: #d97706; font-weight: 600; }
        .err { color: #dc2626; font-weight: 600; }
        .links { margin-top: 30px; }
        .links a { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        .links a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <h1>Zaga Technologies - Migration V2</h1>
    <p>Database migration for customers, reviews, testimonials, and order enhancements.</p>
    <table>
        <tr><th>Component</th><th>Status</th></tr>
        <?php foreach ($results as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r[0]) ?></td>
            <td class="<?= strpos($r[1], 'OK') === 0 ? 'ok' : (strpos($r[1], 'SKIP') === 0 ? 'skip' : 'err') ?>">
                <?= htmlspecialchars($r[1]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <div class="links">
        <a href="/Zaga/">Homepage</a>
        <a href="/Zaga/admin">Admin Panel</a>
    </div>
</body>
</html>
