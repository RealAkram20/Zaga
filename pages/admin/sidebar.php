
<!-- Sidebar overlay for mobile -->
<div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">
            <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="Zaga Logo">
            <div>
                <span class="admin-sidebar-brand-text">Zaga</span>
                <span class="admin-sidebar-brand-sub">Admin Panel</span>
            </div>
        </div>

        <nav class="admin-sidebar-nav">
            <div class="admin-sidebar-section-title">Main</div>

            <a href="<?php echo SITE_URL; ?>/admin" class="admin-sidebar-item <?php echo $admin_page === 'dashboard' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>
                </span>
                Dashboard
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/products" class="admin-sidebar-item <?php echo $admin_page === 'products' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </span>
                Products
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/categories" class="admin-sidebar-item <?php echo $admin_page === 'categories' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                </span>
                Categories
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/courses" class="admin-sidebar-item <?php echo $admin_page === 'courses' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                </span>
                Courses
            </a>

            <div class="admin-sidebar-section-title">Commerce</div>

            <a href="<?php echo SITE_URL; ?>/admin/orders" class="admin-sidebar-item <?php echo $admin_page === 'orders' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                </span>
                Orders
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/credits" class="admin-sidebar-item <?php echo $admin_page === 'credits' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </span>
                Credits
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/customers" class="admin-sidebar-item <?php echo $admin_page === 'customers' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </span>
                Customers
            </a>

            <div class="admin-sidebar-section-title">Content</div>

            <a href="<?php echo SITE_URL; ?>/admin/reviews" class="admin-sidebar-item <?php echo $admin_page === 'reviews' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </span>
                Reviews
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/testimonials" class="admin-sidebar-item <?php echo $admin_page === 'testimonials' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                </span>
                Testimonials
            </a>

            <a href="<?php echo SITE_URL; ?>/admin/gallery" class="admin-sidebar-item <?php echo $admin_page === 'gallery' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                </span>
                Gallery
            </a>

            <div class="admin-sidebar-section-title">Settings</div>

            <a href="<?php echo SITE_URL; ?>/admin/profile" class="admin-sidebar-item <?php echo $admin_page === 'profile' ? 'active' : ''; ?>">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                My Profile
            </a>

            <div class="admin-sidebar-divider"></div>

            <a href="<?php echo SITE_URL; ?>/" class="admin-sidebar-item" target="_blank">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                </span>
                Back to Site
            </a>

            <a href="#" class="admin-sidebar-item" id="adminLogoutLink">
                <span class="admin-sidebar-icon">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main content area -->
    <div class="admin-content">
        <!-- Top bar -->
        <div class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="admin-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <h1 class="admin-topbar-title"><?php echo safe_output($page_title_short ?? ucfirst($admin_page)); ?></h1>
            </div>
            <div class="admin-topbar-right">
                <div class="admin-topbar-user">
                    <span><?php echo safe_output($admin_name); ?></span>
                    <div class="admin-topbar-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                </div>
            </div>
        </div>
