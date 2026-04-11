<?php
// ============================================================
// Zaga Technologies - Logout
// ============================================================

require_once __DIR__ . '/../includes/config.php';

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Start a new session for the flash message
session_start();
set_flash('success', 'You have been logged out successfully.');

redirect(SITE_URL . '/');
