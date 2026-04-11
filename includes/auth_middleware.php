<?php
// ============================================================
// Zaga Technologies - Authentication Middleware
// ============================================================

require_once __DIR__ . '/config.php';

/**
 * Require the user to be logged in.
 * Redirects to login page with a return URL if not authenticated.
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to access this page.');
        $return_url = $_SERVER['REQUEST_URI'] ?? '';
        $login_url  = SITE_URL . '/account/login';
        if (!empty($return_url)) {
            $login_url .= '?redirect=' . urlencode($return_url);
        }
        redirect($login_url);
    }
}

/**
 * Require the user to be an admin.
 * Redirects to login if not authenticated, or to home if not admin.
 */
function require_admin(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to access the admin area.');
        redirect(SITE_URL . '/account/login');
    }
    if (!is_admin()) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect(SITE_URL . '/');
    }
}

/**
 * Require the user to be a guest (not logged in).
 * Used for login/register pages to prevent double-login.
 */
function require_guest(): void {
    if (is_logged_in()) {
        redirect(SITE_URL . '/');
    }
}
