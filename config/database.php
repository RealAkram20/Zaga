<?php
// ============================================================
// Zaga Technologies - Database Configuration (MySQLi)
// ============================================================

// Load .env file if it exists (production should use real env vars)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        if (!getenv($key)) {
            putenv("$key=$val");
        }
    }
}

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'zaga_db');

// Admin credentials
define('ADMIN_DEFAULT_PASSWORD', getenv('ADMIN_DEFAULT_PASSWORD') ?: 'ZagaAdmin2025!');

function getDbConnection() {
    // First connect without selecting a database to check/create it
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }

    // Auto-create database if it doesn't exist
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // Now select it
    if (!$conn->select_db(DB_NAME)) {
        die(json_encode(['success' => false, 'message' => 'Could not select database. Please run setup.php first.']));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
