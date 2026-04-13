<?php
// ============================================================
// Zaga Technologies - Core Configuration
// ============================================================

// Prevent double-include
if (defined('ZAGA_CONFIG_LOADED')) return;
define('ZAGA_CONFIG_LOADED', true);

// --- Error reporting (production-safe) ---
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// --- Secure session settings ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    // Enable secure cookie when on HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// --- Include database configuration ---
require_once __DIR__ . '/../config/database.php';

// --- Site constants ---
// APP_BASE must be a PATH prefix only (e.g. "" for root domain, "/subdir"
// for a subdirectory). Users sometimes paste a full URL by mistake, so
// we strip any scheme+host and keep only the path portion. Trailing
// slashes are removed so every caller can safely do SITE_URL . '/route'.
$_appBase = getenv('APP_BASE');
if ($_appBase === false) $_appBase = '';
$_appBase = trim((string)$_appBase);
if (preg_match('~^https?://[^/]+(/.*)?$~i', $_appBase, $m)) {
    $_appBase = $m[1] ?? '';
}
define('SITE_URL', rtrim($_appBase, '/'));
define('SITE_NAME', 'Zaga Technologies');
define('SITE_VERSION', '2.0.0');

// ============================================================
// CSRF Protection
// ============================================================

/**
 * Generate or retrieve the current CSRF token.
 */
function csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Return a hidden input field with the CSRF token.
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

/**
 * Validate a submitted CSRF token against the session token.
 */
function csrf_verify(string $token = null): bool {
    $token = $token ?? ($_POST['_csrf_token'] ?? '');
    if (empty($_SESSION['_csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $token);
}

/**
 * Validate CSRF on admin POST requests. Terminates with JSON error on failure.
 */
function csrf_verify_request(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (!csrf_verify($_POST['_csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
        exit;
    }
}

// ============================================================
// Redirect Helper
// ============================================================

/**
 * Redirect to a given URL and terminate.
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ============================================================
// Flash Message System
// ============================================================

/**
 * Set a flash message to be shown on the next page load.
 *
 * @param string $type    success|error|warning|info
 * @param string $message The message text.
 */
function set_flash(string $type, string $message): void {
    $_SESSION['_flash_messages'][] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieve and clear all flash messages.
 *
 * @return array List of ['type' => ..., 'message' => ...] arrays.
 */
function get_flash(): array {
    $messages = $_SESSION['_flash_messages'] ?? [];
    unset($_SESSION['_flash_messages']);
    return $messages;
}

// ============================================================
// Authentication Helpers
// ============================================================

/**
 * Check if a user is currently logged in.
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin.
 */
function is_admin(): bool {
    return is_logged_in() && !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Return the current user data from session, or null.
 *
 * @return array|null  ['id', 'name', 'email', 'role']
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'customer',
    ];
}

// ============================================================
// Output Helpers
// ============================================================

/**
 * Escape a string for safe HTML output.
 */
function safe_output(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
