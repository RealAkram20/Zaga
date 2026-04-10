<?php
/**
 * Zaga Technologies - Database Setup Script
 * Run this file once to create the database and seed initial data.
 * URL: http://localhost/Zaga/setup.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'zaga_db';

echo "<html><head><title>Zaga DB Setup</title>
<style>body{font-family:'Segoe UI',sans-serif;max-width:700px;margin:40px auto;padding:20px;background:#f8fafc;}
h1{color:#0f172a;}h2{color:#2563eb;margin-top:25px;}
.ok{color:#16a34a;font-weight:600;}.err{color:#dc2626;font-weight:600;}
.box{background:#fff;padding:20px;border-radius:8px;border:1px solid #e2e8f0;margin:15px 0;}
a.btn{display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;margin-top:20px;font-weight:600;}
a.btn:hover{background:#1d4ed8;}
</style></head><body>";
echo "<h1>Zaga Technologies - Database Setup</h1>";

// Step 1: Connect to MySQL
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    echo "<p class='err'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    echo "<p>Make sure XAMPP MySQL is running.</p>";
    echo "</body></html>";
    exit;
}
echo "<div class='box'><p class='ok'>Connected to MySQL server.</p>";

// Step 2: Create database
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
echo "<p class='ok'>Database '$dbName' created/verified.</p>";
$conn->select_db($dbName);

// Step 3: Read and execute SQL file
$sqlFile = __DIR__ . '/database/zaga_db.sql';
if (!file_exists($sqlFile)) {
    echo "<p class='err'>SQL file not found at: $sqlFile</p></div></body></html>";
    exit;
}

$sql = file_get_contents($sqlFile);
// Remove the CREATE DATABASE and USE lines since we already selected it
$sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
$sql = preg_replace('/USE\s+\w+\s*;/i', '', $sql);

// Execute multi-query
$conn->multi_query($sql);

$errors = [];
$queryCount = 0;
do {
    if ($result = $conn->store_result()) {
        $result->free();
    }
    $queryCount++;
    if ($conn->errno) {
        // Ignore "table already exists" and "duplicate entry" errors
        if ($conn->errno !== 1062 && $conn->errno !== 1050) {
            $errors[] = "Query $queryCount: " . $conn->error;
        }
    }
} while ($conn->more_results() && $conn->next_result());

if (empty($errors)) {
    echo "<p class='ok'>All tables created and seeded successfully ($queryCount queries executed).</p>";
} else {
    echo "<p class='ok'>Tables created with some notes:</p><ul>";
    foreach ($errors as $e) {
        echo "<li class='err'>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul>";
}
echo "</div>";

// Step 4: Hash the admin password properly
echo "<h2>Admin Account Setup</h2><div class='box'>";
$hash = password_hash('ZagaAdmin2025!', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
if ($stmt) {
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $stmt->close();
    echo "<p class='ok'>Admin password hashed securely.</p>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>ZagaAdmin2025!</strong></p>";
} else {
    // admin_users may not exist yet if multi_query hasn't flushed
    echo "<p>Admin password will be set on first login.</p>";
}
echo "</div>";

// Step 5: Verify tables
echo "<h2>Database Tables</h2><div class='box'>";
$tables = $conn->query("SHOW TABLES");
if ($tables) {
    echo "<ul>";
    while ($row = $tables->fetch_row()) {
        $count = $conn->query("SELECT COUNT(*) FROM `{$row[0]}`")->fetch_row()[0];
        echo "<li><strong>{$row[0]}</strong> — $count rows</li>";
    }
    echo "</ul>";
}
echo "</div>";

$conn->close();

echo "<h2>Setup Complete!</h2>";
echo "<p>You can now use the system:</p>";
echo "<a class='btn' href='index.html'>Go to Homepage</a> ";
echo "<a class='btn' href='admin.php' style='background:#16a34a;'>Go to Admin Panel</a>";
echo "<p style='margin-top:20px;color:#94a3b8;font-size:13px;'>You can delete this setup.php file after setup is complete.</p>";
echo "</body></html>";
