<?php
// ============================================================
// Zaga Technologies - Account Dashboard Sidebar
// Reusable sidebar include for all dashboard pages
// ============================================================

$user = current_user();
$sidebar_page = $sidebar_page ?? '';
?>
<!-- Mobile bottom tab bar -->
<nav class="dashboard-mobile-nav">
    <a href="<?php echo SITE_URL; ?>/account" class="dashboard-mobile-nav-item<?php echo $sidebar_page === 'dashboard' ? ' active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Home</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/account/orders" class="dashboard-mobile-nav-item<?php echo $sidebar_page === 'orders' ? ' active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <span>Orders</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/account/credits" class="dashboard-mobile-nav-item<?php echo $sidebar_page === 'credits' ? ' active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <span>Credits</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/account/courses" class="dashboard-mobile-nav-item<?php echo $sidebar_page === 'courses' ? ' active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
        <span>Courses</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/account/profile" class="dashboard-mobile-nav-item<?php echo $sidebar_page === 'profile' ? ' active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Profile</span>
    </a>
</nav>

<aside class="dashboard-sidebar">
    <div class="dashboard-sidebar-header">
        <h3><?php echo safe_output($user['name']); ?></h3>
        <p><?php echo safe_output($user['email']); ?></p>
    </div>

    <nav class="dashboard-nav">
        <div class="dashboard-nav-section-title">Account</div>

        <a href="<?php echo SITE_URL; ?>/account" class="dashboard-nav-item<?php echo $sidebar_page === 'dashboard' ? ' active' : ''; ?>">
            <span class="dashboard-nav-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </span>
            Dashboard
        </a>

        <a href="<?php echo SITE_URL; ?>/account/orders" class="dashboard-nav-item<?php echo $sidebar_page === 'orders' ? ' active' : ''; ?>">
            <span class="dashboard-nav-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </span>
            Order History
        </a>

        <a href="<?php echo SITE_URL; ?>/account/credits" class="dashboard-nav-item<?php echo $sidebar_page === 'credits' ? ' active' : ''; ?>">
            <span class="dashboard-nav-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </span>
            My Credits
        </a>

        <a href="<?php echo SITE_URL; ?>/account/courses" class="dashboard-nav-item<?php echo $sidebar_page === 'courses' ? ' active' : ''; ?>">
            <span class="dashboard-nav-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            </span>
            My Courses
        </a>

        <div class="dashboard-nav-divider"></div>
        <div class="dashboard-nav-section-title">Settings</div>

        <a href="<?php echo SITE_URL; ?>/account/profile" class="dashboard-nav-item<?php echo $sidebar_page === 'profile' ? ' active' : ''; ?>">
            <span class="dashboard-nav-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            Profile Settings
        </a>

        <div class="dashboard-nav-divider"></div>

        <a href="<?php echo SITE_URL; ?>/account/logout" class="dashboard-nav-item" style="color: var(--color-danger);">
            <span class="dashboard-nav-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </span>
            Logout
        </a>
    </nav>
</aside>
