<?php
// ============================================================
// Zaga Technologies - Site Header & Navbar
// ============================================================

require_once __DIR__ . '/config.php';

// Page title - set by including page before this file
$page_title  = isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME;
$current_page = $current_page ?? '';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Zaga Technologies - Buy tech on credit with flexible payment plans. Laptops, desktops, tablets and digital skills courses in Kampala, Uganda.">
    <meta name="theme-color" content="#1e40af">
    <title><?php echo safe_output($page_title); ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/zz.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/app.css">
</head>
<body>

<?php
// --- Flash messages ---
$flash_messages = get_flash();
if (!empty($flash_messages)): ?>
<div class="flash-container" id="flashContainer">
    <?php foreach ($flash_messages as $msg): ?>
    <div class="flash-message flash-<?php echo safe_output($msg['type']); ?>">
        <span><?php echo safe_output($msg['message']); ?></span>
        <button type="button" class="flash-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <!-- Logo -->
        <a href="<?php echo SITE_URL; ?>/" class="nav-logo">
            <img src="<?php echo SITE_URL; ?>/images/logo.png" alt="<?php echo safe_output(SITE_NAME); ?>" class="logo-img">
        </a>

        <!-- Search bar -->
        <div class="nav-search">
            <form action="<?php echo SITE_URL; ?>/shop" method="GET" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Search products, courses..." autocomplete="off">
                <button type="submit" class="search-btn" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </form>
        </div>

        <!-- Navigation links -->
        <div class="nav-links" id="navLinks">
            <a href="<?php echo SITE_URL; ?>/" class="nav-link<?php echo $current_page === 'home' ? ' active' : ''; ?>">Home</a>
            <a href="<?php echo SITE_URL; ?>/shop" class="nav-link<?php echo $current_page === 'shop' ? ' active' : ''; ?>">Shop</a>
            <a href="<?php echo SITE_URL; ?>/courses" class="nav-link<?php echo $current_page === 'courses' ? ' active' : ''; ?>">Courses</a>
            <a href="<?php echo SITE_URL; ?>/about" class="nav-link<?php echo $current_page === 'about' ? ' active' : ''; ?>">About Us</a>

            <!-- User area -->
            <div class="nav-user-area">
                <?php if ($user): ?>
                <div class="user-dropdown">
                    <a href="<?php echo SITE_URL; ?>/account" class="user-dropdown-toggle" id="userDropdownToggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="user-name">My Account</span>
                        <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <a href="<?php echo SITE_URL; ?>/account" class="dropdown-item">Dashboard</a>
                        <a href="<?php echo SITE_URL; ?>/account/orders" class="dropdown-item">My Orders</a>
                        <a href="<?php echo SITE_URL; ?>/account/credits" class="dropdown-item">My Credits</a>
                        <?php if (is_admin()): ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/admin" class="dropdown-item dropdown-item-admin">Admin Dashboard</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo SITE_URL; ?>/account/logout" class="dropdown-item dropdown-item-logout">Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/account/login" class="btn btn-outline btn-sm">Login</a>
                <a href="<?php echo SITE_URL; ?>/account/register" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cart (always visible) -->
        <a href="<?php echo SITE_URL; ?>/cart" class="nav-cart-btn<?php echo $current_page === 'cart' ? ' active' : ''; ?>" aria-label="Shopping Cart">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            <span class="cart-badge" id="cartCount">0</span>
        </a>

        <!-- Mobile-only: search icon + hamburger -->
        <div class="mobile-actions">
            <button class="search-toggle" id="searchToggle" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
</nav>

<!-- Search popup (activated by mobile search icon) -->
<div class="search-popup" id="searchPopup" style="display:none">
    <div class="search-popup-backdrop" id="searchPopupBackdrop"></div>
    <div class="search-popup-content">
        <form action="<?php echo SITE_URL; ?>/shop" method="GET" class="search-popup-form">
            <input type="text" name="q" class="search-popup-input" placeholder="Search products, courses..." autocomplete="off" id="searchPopupInput">
            <button type="submit" class="search-popup-btn" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
            <button type="button" class="search-popup-close" id="searchPopupClose" aria-label="Close search">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </form>
    </div>
</div>
<script>
(function(){
    var btn = document.getElementById('searchToggle');
    var popup = document.getElementById('searchPopup');
    var input = document.getElementById('searchPopupInput');
    if (!btn || !popup) return;
    btn.addEventListener('click', function() {
        popup.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        if (input) setTimeout(function(){ input.focus(); }, 100);
    });
    document.getElementById('searchPopupClose').addEventListener('click', function() {
        popup.style.display = 'none';
        document.body.style.overflow = '';
    });
    document.getElementById('searchPopupBackdrop').addEventListener('click', function() {
        popup.style.display = 'none';
        document.body.style.overflow = '';
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && popup.style.display === 'flex') {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
})();
</script>

<!-- Main content wrapper -->
<main class="main-content">
