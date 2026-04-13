<?php
/**
 * One-time admin password reset.
 * DELETE THIS FILE immediately after use.
 */
require_once __DIR__ . '/includes/config.php';

$newPassword = 'ZagaAdmin2025!';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$conn = getDbConnection();
$stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = 'admin'");
$stmt->bind_param('s', $hash);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "<p style='font-family:sans-serif;color:green;font-size:18px;'>
        ✓ Admin password reset to <strong>ZagaAdmin2025!</strong><br><br>
        <strong style='color:red;'>DELETE this file now via cPanel File Manager.</strong>
    </p>";
} else {
    echo "<p style='font-family:sans-serif;color:red;'>Failed — admin user not found. Run setup.php first.</p>";
}

$stmt->close();
$conn->close();
