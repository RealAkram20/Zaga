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
    // PHP 8.1+ makes mysqli throw exceptions by default on connect failure.
    // Switch to manual error handling so our callers can inspect connect_error.
    mysqli_report(MYSQLI_REPORT_OFF);

    // Connect directly to the named database. On shared hosting (cPanel) the
    // MySQL user is scoped to a single pre-created DB and has NO privilege
    // to CREATE DATABASE — attempting to do so produces
    //   "Access denied for user '...' to database '...'"
    // which masks the real issue. So we never try to create; the DB must
    // already exist (created in cPanel → MySQL Databases).
    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    } catch (Throwable $e) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
    }
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
