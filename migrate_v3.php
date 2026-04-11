<?php
/**
 * Zaga Technologies - Migration V3
 * Run once: http://localhost/Zaga/migrate_v3.php
 * Adds credit fields to products table
 */

require_once __DIR__ . '/config/database.php';

$results = [];

try {
    $conn = getDbConnection();

    // Add slug to courses table
    $courseCols = [];
    $colResult0 = $conn->query("SHOW COLUMNS FROM courses");
    if ($colResult0) {
        while ($col = $colResult0->fetch_assoc()) {
            $courseCols[] = $col['Field'];
        }
    }
    if (!in_array('slug', $courseCols)) {
        $conn->query("ALTER TABLE courses ADD COLUMN slug VARCHAR(255) DEFAULT NULL AFTER title");
        $rows = $conn->query("SELECT id, title FROM courses WHERE slug IS NULL OR slug = ''");
        if ($rows) {
            while ($row = $rows->fetch_assoc()) {
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($row['title'])), '-'));
                $stmt2 = $conn->prepare("UPDATE courses SET slug = ? WHERE id = ?");
                $stmt2->bind_param('si', $slug, $row['id']);
                $stmt2->execute();
                $stmt2->close();
            }
        }
        $results[] = ['courses.slug column', 'ADDED + GENERATED'];
    } else {
        $results[] = ['courses.slug column', 'SKIP (exists)'];
    }

    // Check if columns already exist before adding
    $columns = ['credit_available', 'default_apr', 'credit_terms_months'];
    $existingColumns = [];
    $colResult = $conn->query("SHOW COLUMNS FROM products");
    while ($col = $colResult->fetch_assoc()) {
        $existingColumns[] = $col['Field'];
    }

    // Add slug to products table
    if (!in_array('slug', $existingColumns)) {
        $conn->query("ALTER TABLE products ADD COLUMN slug VARCHAR(255) DEFAULT NULL AFTER title");
        $rows = $conn->query("SELECT id, title FROM products WHERE slug IS NULL OR slug = ''");
        if ($rows) {
            while ($row = $rows->fetch_assoc()) {
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($row['title'])), '-'));
                $stmt2 = $conn->prepare("UPDATE products SET slug = ? WHERE id = ?");
                $stmt2->bind_param('si', $slug, $row['id']);
                $stmt2->execute();
                $stmt2->close();
            }
        }
        $results[] = ['products.slug column', 'ADDED + GENERATED'];
    } else {
        $results[] = ['products.slug column', 'SKIP (exists)'];
    }

    if (!in_array('credit_available', $existingColumns)) {
        $conn->query("ALTER TABLE products ADD COLUMN credit_available TINYINT(1) DEFAULT 1");
        $results[] = ['credit_available column', 'ADDED'];
    } else {
        $results[] = ['credit_available column', 'SKIP (exists)'];
    }

    if (!in_array('default_apr', $existingColumns)) {
        $conn->query("ALTER TABLE products ADD COLUMN default_apr DECIMAL(5,2) DEFAULT 0.00");
        $results[] = ['default_apr column', 'ADDED'];
    } else {
        $results[] = ['default_apr column', 'SKIP (exists)'];
    }

    if (!in_array('credit_terms_months', $existingColumns)) {
        $conn->query("ALTER TABLE products ADD COLUMN credit_terms_months VARCHAR(50) DEFAULT '3,6'");
        $results[] = ['credit_terms_months column', 'ADDED'];
    } else {
        $results[] = ['credit_terms_months column', 'SKIP (exists)'];
    }

    // Add credit_terms_months to courses table
    $courseTermCheck = [];
    $ctRes = $conn->query("SHOW COLUMNS FROM courses");
    if ($ctRes) { while ($c = $ctRes->fetch_assoc()) $courseTermCheck[] = $c['Field']; }
    if (!in_array('credit_terms_months', $courseTermCheck)) {
        $conn->query("ALTER TABLE courses ADD COLUMN credit_terms_months VARCHAR(50) DEFAULT '3,6' AFTER default_apr");
        $results[] = ['courses.credit_terms_months', 'ADDED'];
    } else {
        $results[] = ['courses.credit_terms_months', 'SKIP (exists)'];
    }

    // Add cart_json to customers for persistent cart
    $custCheckCols = [];
    $colCheck = $conn->query("SHOW COLUMNS FROM customers");
    if ($colCheck) { while ($c = $colCheck->fetch_assoc()) $custCheckCols[] = $c['Field']; }
    if (!in_array('cart_json', $custCheckCols)) {
        $conn->query("ALTER TABLE customers ADD COLUMN cart_json TEXT DEFAULT NULL");
        $results[] = ['customers.cart_json column', 'ADDED'];
    } else {
        $results[] = ['customers.cart_json column', 'SKIP (exists)'];
    }

    // Add email to admin_users
    $adminCols = [];
    $colRes = $conn->query("SHOW COLUMNS FROM admin_users");
    if ($colRes) { while ($c = $colRes->fetch_assoc()) $adminCols[] = $c['Field']; }
    if (!in_array('email', $adminCols)) {
        $conn->query("ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) DEFAULT '' AFTER full_name");
        $conn->query("UPDATE admin_users SET email = 'support@zagatechcredit.com' WHERE email = '' LIMIT 1");
        $results[] = ['admin_users.email column', 'ADDED'];
    } else {
        $results[] = ['admin_users.email column', 'SKIP (exists)'];
    }

    // Create password_reset_tokens table
    $tableCheck = $conn->query("SHOW TABLES LIKE 'password_reset_tokens'");
    if ($tableCheck->num_rows === 0) {
        $conn->query("CREATE TABLE password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email)
        ) ENGINE=InnoDB");
        $results[] = ['password_reset_tokens table', 'CREATED'];
    } else {
        $results[] = ['password_reset_tokens table', 'SKIP (exists)'];
    }

    // Create customers table if it doesn't exist (from V2 migration)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'customers'");
    if ($tableCheck->num_rows === 0) {
        $sql = "CREATE TABLE customers (
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
        ) ENGINE=InnoDB";
        $conn->query($sql);
        $results[] = ['customers table', 'CREATED'];
    } else {
        // Add password_hash column if missing
        $custCols = [];
        $colResult2 = $conn->query("SHOW COLUMNS FROM customers");
        while ($col = $colResult2->fetch_assoc()) {
            $custCols[] = $col['Field'];
        }
        if (!in_array('password_hash', $custCols)) {
            $conn->query("ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER country");
            $results[] = ['customers.password_hash column', 'ADDED'];
        } else {
            $results[] = ['customers.password_hash column', 'SKIP (exists)'];
        }
    }

    // Create reviews table if it doesn't exist (from V2 migration)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'reviews'");
    if ($tableCheck->num_rows === 0) {
        $sql = "CREATE TABLE reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT DEFAULT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) DEFAULT '',
            item_type ENUM('product','course') NOT NULL DEFAULT 'product',
            item_id INT NOT NULL,
            rating DECIMAL(2,1) NOT NULL DEFAULT 5.0,
            review_text TEXT,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB";
        $conn->query($sql);
        $results[] = ['reviews table', 'CREATED'];
    } else {
        $results[] = ['reviews table', 'SKIP (exists)'];
    }

    // Create testimonials table if it doesn't exist (from V2 migration)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'testimonials'");
    if ($tableCheck->num_rows === 0) {
        $sql = "CREATE TABLE testimonials (
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
        // Seed default testimonials
        $conn->query("INSERT INTO testimonials (name, role, image, content, rating, status, display_order) VALUES
            ('Amina N.', 'Small business owner', 'images/user1.jpg', 'The credit option made it possible to upgrade my laptop without draining savings. The deposit was easy and payments were clear.', 5, 1, 1),
            ('David M.', 'Freelancer', 'images/user2.jpg', 'I bought a monitor on a 3-month plan. The monthly payments fit my budget and customer support was very helpful.', 5, 1, 2),
            ('Grace K.', 'Student', 'images/user3.jpg', 'As a student, the deposit and installments meant I could get a tablet for remote classes straight away.', 5, 1, 3),
            ('Samuel L.', 'IT Consultant', 'images/user4.jpg', 'Clear terms, straightforward checkout and helpful reminders paying over six months made upgrading our office PCs achievable.', 5, 1, 4)
        ");
        $results[] = ['testimonials table', 'CREATED + SEEDED'];
    } else {
        $results[] = ['testimonials table', 'SKIP (exists)'];
    }

    // Add customer_id and admin_notes to orders if missing (from V2 migration)
    $orderCols = [];
    $colResult3 = $conn->query("SHOW COLUMNS FROM orders");
    if ($colResult3) {
        while ($col = $colResult3->fetch_assoc()) {
            $orderCols[] = $col['Field'];
        }
        if (!in_array('customer_id', $orderCols)) {
            $conn->query("ALTER TABLE orders ADD COLUMN customer_id INT DEFAULT NULL");
            $results[] = ['orders.customer_id column', 'ADDED'];
        }
        if (!in_array('admin_notes', $orderCols)) {
            $conn->query("ALTER TABLE orders ADD COLUMN admin_notes TEXT DEFAULT NULL");
            $results[] = ['orders.admin_notes column', 'ADDED'];
        }
        if (!in_array('shipping_address', $orderCols)) {
            $conn->query("ALTER TABLE orders ADD COLUMN shipping_address TEXT DEFAULT NULL");
            $results[] = ['orders.shipping_address column', 'ADDED'];
        }
    }

    $conn->close();
} catch (Exception $e) {
    $results[] = ['Error', $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zaga Migration V3</title>
    <style>
        body { font-family: system-ui; padding: 40px; background: #f8fafc; }
        .card { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-weight: 600; }
        .ok { color: #16a34a; font-weight: 600; }
        .skip { color: #d97706; }
        a { color: #2563eb; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Migration V3 - Credit Fields</h1>
        <table>
            <thead><tr><th>Item</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r[0]); ?></td>
                    <td class="<?php echo strpos($r[1], 'SKIP') !== false ? 'skip' : 'ok'; ?>"><?php echo htmlspecialchars($r[1]); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="/Zaga/">Homepage</a> | <a href="/Zaga/admin/">Admin Panel</a></p>
    </div>
</body>
</html>
