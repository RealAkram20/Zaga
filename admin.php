<?php
// Redirect to the new modular admin panel
require_once __DIR__ . '/includes/config.php';
header('Location: ' . SITE_URL . '/admin');
exit;
?>
<?php /* Legacy admin panel below - preserved as backup */ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Zaga Tech Credit</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/png" href="images/zz.png">
    <link rel="apple-touch-icon" href="images/zz.png">
    <style>
        .admin-tabs { display: flex; flex-wrap: wrap; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
        .tab-btn { padding: 12px 20px; border: none; background: none; cursor: pointer; font-size: 14px; font-weight: 500;
            color: #64748b; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; white-space: nowrap; }
        .tab-btn:hover { color: #2563eb; background: #f1f5f9; }
        .tab-btn.active { color: #2563eb; border-bottom-color: #2563eb; font-weight: 600; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .admin-form { max-width: 900px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #334155; }
        .form-group .input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0;
            border-radius: 5px; font-size: 14px; }
        .form-group textarea { resize: vertical; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .admin-table th, .admin-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .admin-table th { background: #f8fafc; font-weight: 600; color: #334155; }
        .admin-table tr:hover { background: #f8fafc; }
        .btn-small { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500; }
        .btn-edit { background: #2563eb; color: #fff; }
        .btn-edit:hover { background: #1d4ed8; }
        .btn-delete { background: #dc3545; color: #fff; }
        .btn-delete:hover { background: #b91c1c; }
        .btn-add-new { background: #2563eb; color: #fff; padding: 10px 20px; border: none; border-radius: 5px;
            cursor: pointer; font-size: 14px; font-weight: 500; margin-bottom: 15px; }
        .btn-add-new:hover { background: #1d4ed8; }
        .admin-search { display: flex; gap: 10px; margin-bottom: 15px; }
        .admin-search .input { flex: 1; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center;
            justify-content: center; z-index: 3000; padding: 20px; }
        .modal-panel { background: #fff; width: 100%; max-width: 820px; border-radius: 8px; overflow: auto;
            box-shadow: 0 10px 30px rgba(2,6,23,0.3); max-height: 90vh; }
        .modal-header { padding: 18px 20px; border-bottom: 1px solid #e6eef8; display: flex;
            justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; font-size: 18px; color: #0f172a; }
        .modal-close { background: none; border: none; font-size: 18px; cursor: pointer; }
        .modal-body { padding: 18px; }
        .modal-body .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .modal-body .form-grid .full-width { grid-column: 1 / -1; }
        .modal-body .form-actions { display: flex; gap: 8px; justify-content: flex-end; grid-column: 1 / -1; padding-top: 8px; }
        .course-type-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-digital { background: #dbeafe; color: #1e40af; }
        .badge-entrepreneur { background: #fef3c7; color: #92400e; }
        .stat-card { background: linear-gradient(135deg, #2563eb, #1e40af); color: #fff; padding: 20px; border-radius: 8px;
            text-align: center; }
        .stat-card h3 { margin: 0 0 8px; font-size: 14px; opacity: 0.9; }
        .stat-card p { margin: 0; font-size: 24px; font-weight: 700; }
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .img-preview { max-width: 80px; max-height: 60px; object-fit: contain; border-radius: 4px; border: 1px solid #e2e8f0; }
        @media (max-width: 768px) {
            .form-row, .modal-body .form-grid { grid-template-columns: 1fr; }
            .admin-tabs { overflow-x: auto; }
            .admin-stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <p><img class="logo" src="images/logo.png"></p>
            </div>
            <button class="menu-toggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <div class="search-bar-container">
                <input type="text" id="searchInput" class="search-bar" placeholder="Search products...">
                <button id="searchBtn" class="search-btn">Search</button>
            </div>
            <div class="nav-links">
                <a href="/Zaga/" class="nav-link">Home</a>
                <a href="/Zaga/shop" class="nav-link">Shop</a>
                <a href="/Zaga/courses" class="nav-link">Courses</a>
                <a href="/Zaga/about" class="nav-link">About Us</a>
                <a href="/Zaga/admin" class="nav-link admin-link active">Admin</a>
                <a href="/Zaga/cart" class="nav-link cart-link">Cart <span id="cartCount">0</span></a>
            </div>
        </div>
    </nav>

    <!-- Admin Content -->
    <div id="adminContent" class="container admin-container" style="display:none; padding: 30px 20px;">
        <div class="admin-header">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h1 style="margin:0;">Admin Panel</h1>
                <button id="adminLogoutBtn" class="btn-small btn-delete" style="padding:10px 20px; font-size:14px;">Logout</button>
            </div>
            <div class="admin-stats" id="adminStats">
                <div class="stat-card"><h3>Total Products</h3><p id="totalProducts">0</p></div>
                <div class="stat-card"><h3>Categories</h3><p id="totalCategories">0</p></div>
                <div class="stat-card"><h3>Digital Courses</h3><p id="totalDigitalCourses">0</p></div>
                <div class="stat-card"><h3>Entrepreneurship</h3><p id="totalEntrepreneurCourses">0</p></div>
                <div class="stat-card"><h3>Total Orders</h3><p id="totalOrders">0</p></div>
                <div class="stat-card"><h3>Revenue</h3><p id="totalRevenue">UGX 0</p></div>
                <div class="stat-card"><h3>Customers</h3><p id="totalCustomers">0</p></div>
                <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#92400e);"><h3>Pending Reviews</h3><p id="pendingReviews">0</p></div>
                <div class="stat-card"><h3>Testimonials</h3><p id="totalTestimonials">0</p></div>
            </div>
        </div>

        <!-- Admin Tabs -->
        <div class="admin-tabs">
            <button class="tab-btn active" data-tab="products">Products</button>
            <button class="tab-btn" data-tab="categories">Categories</button>
            <button class="tab-btn" data-tab="digitalCourses">Digital Skilling Courses</button>
            <button class="tab-btn" data-tab="entreprenCourses">Entrepreneurship Courses</button>
            <button class="tab-btn" data-tab="orders">Orders</button>
            <button class="tab-btn" data-tab="customers">Customers</button>
            <button class="tab-btn" data-tab="reviews">Reviews</button>
            <button class="tab-btn" data-tab="testimonials">Testimonials</button>
        </div>

        <!-- ==================== PRODUCTS TAB ==================== -->
        <div id="productsTab" class="tab-content active">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">All Products</h2>
                <button class="btn-add-new" onclick="openAddProductModal()">+ Add New Product</button>
            </div>
            <div class="admin-search">
                <input type="text" id="productSearch" placeholder="Search products..." class="input" onkeyup="filterProductsTable()">
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Image</th><th>Title</th><th>Category</th><th>Price (UGX)</th><th>Stock</th><th>Rating</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="productsTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== CATEGORIES TAB ==================== -->
        <div id="categoriesTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Product Categories</h2>
                <button class="btn-add-new" onclick="openAddCategoryModal()">+ Add New Category</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Icon</th><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="categoriesTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== DIGITAL COURSES TAB ==================== -->
        <div id="digitalCoursesTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Digital Skilling Courses</h2>
                <button class="btn-add-new" onclick="openAddCourseModal('digital_skilling')">+ Add New Course</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Icon</th><th>Title</th><th>Price (UGX)</th><th>Duration</th><th>Modules</th><th>Level</th><th>Rating</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="digitalCoursesTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== ENTREPRENEURSHIP COURSES TAB ==================== -->
        <div id="entreprenCoursesTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Entrepreneurship Courses</h2>
                <button class="btn-add-new" onclick="openAddCourseModal('entrepreneurship')">+ Add New Course</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Icon</th><th>Title</th><th>Price (UGX)</th><th>Duration</th><th>Modules</th><th>Level</th><th>Rating</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="entreprenCoursesTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== ORDERS TAB (Enhanced) ==================== -->
        <div id="ordersTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Orders Management</h2>
                <button class="btn-add-new" onclick="openAddOrderModal()">+ Add New Order</button>
            </div>
            <div class="admin-search" style="gap:10px;">
                <input type="text" id="orderSearch" placeholder="Search orders..." class="input" onkeyup="filterOrdersTable()">
                <select id="orderStatusFilter" class="input" style="max-width:180px;" onchange="filterOrdersTable()">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Order #</th><th>Customer</th><th>Email</th><th>Phone</th><th>Date</th><th>Total (UGX)</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="ordersTableBody"><tr><td colspan="8" style="text-align:center; color:#94a3b8;">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== CUSTOMERS TAB ==================== -->
        <div id="customersTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Customers</h2>
                <button class="btn-add-new" onclick="openAddCustomerModal()">+ Add New Customer</button>
            </div>
            <div class="admin-search">
                <input type="text" id="customerSearch" placeholder="Search customers..." class="input" onkeyup="filterCustomersTable()">
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="customersTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== REVIEWS TAB ==================== -->
        <div id="reviewsTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Reviews</h2>
                <button class="btn-add-new" onclick="openAddReviewModal()">+ Add New Review</button>
            </div>
            <div class="admin-search" style="gap:10px;">
                <input type="text" id="reviewSearch" placeholder="Search reviews..." class="input" onkeyup="filterReviewsTable()">
                <select id="reviewStatusFilter" class="input" style="max-width:180px;" onchange="filterReviewsTable()">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Item</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="reviewsTableBody"></tbody>
                </table>
            </div>
        </div>

        <!-- ==================== TESTIMONIALS TAB ==================== -->
        <div id="testimonialsTab" class="tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <h2 style="margin:0;">Testimonials</h2>
                <button class="btn-add-new" onclick="openAddTestimonialModal()">+ Add New Testimonial</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>ID</th><th>Image</th><th>Name</th><th>Role</th><th>Content</th><th>Rating</th><th>Status</th><th>Order</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="testimonialsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p><a href="#">How it works</a></p>
                    <p><a href="#">Our Partners</a></p>
                    <p><a href="/Zaga/about#testimonials-heading">Financed success stories</a></p>
                    <p><a href="#">FAQs</a></p>
                </div>
                <div class="footer-section">
                    <h3>Payment Terms</h3>
                    <p><a href="https://wa.me/256700706809" target="_blank">Apply Now</a></p>
                    <p><a href="#">Terms & Conditions</a></p>
                    <p><a href="https://wa.me/256700706809" target="_blank">Delivery Tracking</a></p>
                    <p><a href="#">Privacy Policy</a></p>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Address: Kabaka Kintu House Level 1 Shop no C-03 Kampala Road-Kampaka-Uganda</p>
                    <p>Email: sales2.zagatechnologiesltd@gmail.com</p>
                    <p>Phone: +256 700 706809</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Zaga Technologies Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Admin Auth Overlay -->
    <div id="adminAuthOverlay" style="position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);z-index:4000;">
        <div style="background:#ffffff;padding:30px 28px;border-radius:10px;width:100%;max-width:420px;box-shadow:0 10px 30px rgba(0,0,0,0.25);">
            <h2 style="margin:0 0 18px;font-size:22px;color:#0f172a;">Admin Access</h2>
            <p style="font-size:14px;color:#475569;margin-bottom:16px;">Enter your credentials to proceed.</p>
            <form id="adminLoginForm" style="display:flex;flex-direction:column;gap:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="adminUsername" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#334155;">Username</label>
                    <input type="text" id="adminUsername" class="input" placeholder="Enter username" autocomplete="username" style="width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:5px;font-size:14px;background:#fff;color:#1e293b;" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="adminPassword" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#334155;">Password</label>
                    <div style="position:relative;">
                        <input type="password" id="adminPassword" class="input" placeholder="Enter password" autocomplete="current-password" style="width:100%;padding:12px;padding-right:45px;border:1px solid #cbd5e1;border-radius:5px;font-size:14px;background:#fff;color:#1e293b;" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility(this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:#64748b;padding:4px;" aria-label="Show password">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="padding:12px;font-size:15px;">Login</button>
                <button type="button" id="adminCancelBtn" class="btn-secondary" style="padding:10px;font-size:13px;">Cancel</button>
                <p id="adminError" style="display:none;color:#dc2626;font-size:13px;margin:0;"></p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
    // Custom confirm modal
    function showConfirmDialog(title, message, confirmText, onConfirm) {
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:10000;display:flex;align-items:center;justify-content:center;';
        overlay.innerHTML = '<div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-width:420px;width:90%;padding:28px;text-align:center;">' +
            '<h3 style="font-size:18px;font-weight:700;margin-bottom:8px;color:#1e293b;">' + title + '</h3>' +
            '<p style="font-size:14px;color:#64748b;margin-bottom:24px;line-height:1.5;">' + message + '</p>' +
            '<div style="display:flex;gap:10px;justify-content:center;">' +
                '<button id="cdCancel" style="padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;border:1px solid #d1d5db;background:#fff;color:#374151;">Cancel</button>' +
                '<button id="cdConfirm" style="padding:10px 24px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;border:none;background:#dc2626;color:#fff;">' + (confirmText || 'Delete') + '</button>' +
            '</div></div>';
        document.body.appendChild(overlay);
        overlay.querySelector('#cdCancel').onclick = function() { overlay.remove(); };
        overlay.querySelector('#cdConfirm').onclick = function() { overlay.remove(); onConfirm(); };
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
    }

    // ============================================================
    // ADMIN DASHBOARD - PHP/MySQL Integration
    // ============================================================

    const API = {
        auth: 'api/auth.php',
        products: 'api/products.php',
        categories: 'api/categories.php',
        courses: 'api/courses.php',
        dashboard: 'api/dashboard.php',
        orders: 'api/orders.php',
        customers: 'api/customers.php',
        reviews: 'api/reviews.php',
        testimonials: 'api/testimonials.php'
    };

    let allProducts = [];
    let allCategories = [];
    let allDigitalCourses = [];
    let allEntreprenCourses = [];
    let allOrders = [];
    let allCustomers = [];
    let allReviews = [];
    let allTestimonials = [];

    // --- Password Toggle ---
    function togglePasswordVisibility(btn) {
        const input = btn.parentElement.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
            btn.setAttribute('aria-label', 'Hide password');
        } else {
            input.type = 'password';
            btn.textContent = '👁';
            btn.setAttribute('aria-label', 'Show password');
        }
    }

    // --- Helpers ---
    function formatPriceAdmin(price) {
        return Number(price).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function showError(msg) {
        const err = document.getElementById('adminError');
        err.textContent = msg;
        err.style.display = 'block';
    }
    function hideError() {
        document.getElementById('adminError').style.display = 'none';
    }

    function showAlert(msg, isError = false) {
        if (typeof showToast === 'function') {
            showToast(msg, isError ? 'error' : 'success');
        } else if (typeof showAppConfirm === 'function') {
            showAppConfirm(isError ? 'Error' : 'Notice', msg, 'OK', function(){});
        }
    }

    function removeModal(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    // --- AUTH ---
    async function checkAuth() {
        try {
            const res = await fetch(API.auth + '?action=check');
            const data = await res.json();
            if (data.logged_in) {
                grantAccess();
            } else {
                document.getElementById('adminContent').style.display = 'none';
                document.getElementById('adminAuthOverlay').style.display = 'flex';
            }
        } catch (e) {
            document.getElementById('adminAuthOverlay').style.display = 'flex';
        }
    }

    function grantAccess() {
        document.getElementById('adminAuthOverlay').style.display = 'none';
        document.getElementById('adminContent').style.display = 'block';
        loadDashboard();
    }

    async function loginAdmin(username, password) {
        const fd = new FormData();
        fd.append('action', 'login');
        fd.append('username', username);
        fd.append('password', password);
        const res = await fetch(API.auth, { method: 'POST', body: fd });
        return await res.json();
    }

    async function logoutAdmin() {
        await fetch(API.auth + '?action=logout');
        location.reload();
    }

    // --- DASHBOARD DATA ---
    async function loadDashboard() {
        try {
            const res = await fetch(API.dashboard);
            const data = await res.json();
            if (data.success) {
                const d = data.data;
                document.getElementById('totalProducts').textContent = d.total_products;
                document.getElementById('totalCategories').textContent = d.total_categories;
                document.getElementById('totalDigitalCourses').textContent = d.total_digital_courses;
                document.getElementById('totalEntrepreneurCourses').textContent = d.total_entrepreneurship_courses;
                document.getElementById('totalOrders').textContent = d.total_orders;
                document.getElementById('totalRevenue').textContent = 'UGX ' + formatPriceAdmin(d.total_revenue);
                if (d.total_customers !== undefined) document.getElementById('totalCustomers').textContent = d.total_customers;
                if (d.pending_reviews !== undefined) document.getElementById('pendingReviews').textContent = d.pending_reviews;
                if (d.total_testimonials !== undefined) document.getElementById('totalTestimonials').textContent = d.total_testimonials;
            }
        } catch (e) { console.error('Dashboard load failed', e); }

        loadProducts();
        loadCategories();
        loadDigitalCourses();
        loadEntreprenCourses();
        loadOrders();
        loadCustomers();
        loadReviews();
        loadTestimonials();
    }

    // ============================================================
    // PRODUCTS
    // ============================================================
    async function loadProducts() {
        const res = await fetch(API.products + '?action=list');
        const data = await res.json();
        if (data.success) {
            allProducts = data.data;
            renderProductsTable(allProducts);
        }
    }

    function renderProductsTable(products) {
        const tbody = document.getElementById('productsTableBody');
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#94a3b8;">No products found</td></tr>';
            return;
        }
        tbody.innerHTML = products.map(p => `
            <tr>
                <td>${p.id}</td>
                <td><img src="${escapeHtml(p.image)}" class="img-preview" onerror="this.src='images/product-1.svg'"></td>
                <td>${escapeHtml(p.title)}</td>
                <td>${escapeHtml(p.category)}</td>
                <td>${formatPriceAdmin(p.price)}</td>
                <td>${p.stock}</td>
                <td>${p.rating} ★</td>
                <td>
                    <button onclick="openEditProductModal(${p.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteProduct(${p.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function filterProductsTable() {
        const term = document.getElementById('productSearch').value.toLowerCase();
        const filtered = allProducts.filter(p =>
            p.title.toLowerCase().includes(term) ||
            (p.category || '').toLowerCase().includes(term) ||
            (p.sku || '').toLowerCase().includes(term)
        );
        renderProductsTable(filtered);
    }

    function openAddProductModal() {
        removeModal('product-modal');
        const catOptions = allCategories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        const modal = document.createElement('div');
        modal.id = 'product-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>Add New Product</h3>
                    <button class="modal-close" onclick="removeModal('product-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm" class="form-grid" enctype="multipart/form-data">
                        <label class="form-group"><span>Title *</span><input name="title" class="input" required></label>
                        <label class="form-group"><span>Category *</span>
                            <select name="category_id" class="input" required><option value="">Select</option>${catOptions}</select>
                        </label>
                        <label class="form-group"><span>Price (UGX) *</span><input name="price" type="number" min="0" class="input" required></label>
                        <label class="form-group"><span>Stock *</span><input name="stock" type="number" min="0" class="input" required></label>
                        <label class="form-group"><span>Original Price</span><input name="original_price" type="number" min="0" class="input"></label>
                        <label class="form-group"><span>Discount %</span><input name="discount" type="number" min="0" max="100" class="input"></label>
                        <label class="form-group"><span>Rating (0-5)</span><input name="rating" type="number" min="0" max="5" step="0.1" class="input" value="0"></label>
                        <label class="form-group"><span>Reviews</span><input name="reviews" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group full-width"><span>Description *</span><textarea name="description" class="input" rows="3" required></textarea></label>
                        <label class="form-group full-width"><span>Features (comma separated)</span><input name="features_text" class="input" placeholder="Fast CPU, 16GB RAM, Wi-Fi 6"></label>
                        <label class="form-group"><span>Image Path</span><input name="image" class="input" placeholder="images/l1.jpg"></label>
                        <label class="form-group"><span>Or Upload Image</span><input name="image_file" type="file" accept="image/*" class="input"></label>
                        <label class="form-group"><span>SKU</span><input name="sku" class="input"></label>
                        <label class="form-group"><span>Warranty</span><input name="warranty" class="input" placeholder="2 Years"></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('product-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'add');
            // Convert features text to JSON
            const featText = fd.get('features_text') || '';
            fd.delete('features_text');
            const featArr = featText ? featText.split(',').map(f => f.trim()).filter(f => f) : [];
            fd.append('features', JSON.stringify(featArr));
            fd.append('additional_images', '[]');

            const res = await fetch(API.products, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Product added successfully!');
                removeModal('product-modal');
                loadProducts();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditProductModal(id) {
        const res = await fetch(API.products + '?action=get&id=' + id);
        const data = await res.json();
        if (!data.success) return showAlert('Product not found');
        const p = data.data;

        removeModal('product-modal');
        const catOptions = allCategories.map(c =>
            `<option value="${c.id}" ${c.id == p.category_id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
        ).join('');
        const featuresStr = Array.isArray(p.features) ? p.features.join(', ') : '';

        const modal = document.createElement('div');
        modal.id = 'product-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>Edit Product — ID ${p.id}</h3>
                    <button class="modal-close" onclick="removeModal('product-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" class="form-grid" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="${p.id}">
                        <label class="form-group"><span>Title *</span><input name="title" class="input" value="${escapeHtml(p.title)}" required></label>
                        <label class="form-group"><span>Category *</span>
                            <select name="category_id" class="input" required><option value="">Select</option>${catOptions}</select>
                        </label>
                        <label class="form-group"><span>Price (UGX) *</span><input name="price" type="number" min="0" class="input" value="${p.price}" required></label>
                        <label class="form-group"><span>Stock *</span><input name="stock" type="number" min="0" class="input" value="${p.stock}" required></label>
                        <label class="form-group"><span>Original Price</span><input name="original_price" type="number" min="0" class="input" value="${p.original_price || ''}"></label>
                        <label class="form-group"><span>Discount %</span><input name="discount" type="number" min="0" max="100" class="input" value="${p.discount || ''}"></label>
                        <label class="form-group"><span>Rating (0-5)</span><input name="rating" type="number" min="0" max="5" step="0.1" class="input" value="${p.rating}"></label>
                        <label class="form-group"><span>Reviews</span><input name="reviews" type="number" min="0" class="input" value="${p.reviews}"></label>
                        <label class="form-group full-width"><span>Description *</span><textarea name="description" class="input" rows="3" required>${escapeHtml(p.description)}</textarea></label>
                        <label class="form-group full-width"><span>Features (comma separated)</span><input name="features_text" class="input" value="${escapeHtml(featuresStr)}"></label>
                        <label class="form-group"><span>Image Path</span><input name="image" class="input" value="${escapeHtml(p.image)}"></label>
                        <label class="form-group"><span>Or Upload New Image</span><input name="image_file" type="file" accept="image/*" class="input"></label>
                        <label class="form-group"><span>SKU</span><input name="sku" class="input" value="${escapeHtml(p.sku)}"></label>
                        <label class="form-group"><span>Warranty</span><input name="warranty" class="input" value="${escapeHtml(p.warranty)}"></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('product-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editProductForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const featText = fd.get('features_text') || '';
            fd.delete('features_text');
            const featArr = featText ? featText.split(',').map(f => f.trim()).filter(f => f) : [];
            fd.append('features', JSON.stringify(featArr));
            fd.append('additional_images', JSON.stringify([fd.get('image')]));

            const res = await fetch(API.products, { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showAlert('Product updated successfully!');
                removeModal('product-modal');
                loadProducts();
                loadDashboard();
            } else {
                showAlert(result.message, true);
            }
        });
    }

    function deleteProduct(id) {
        showConfirmDialog('Delete Product', 'Are you sure you want to delete this product?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.products, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadProducts(); loadDashboard(); }
        });
    }

    // ============================================================
    // CATEGORIES
    // ============================================================
    async function loadCategories() {
        const res = await fetch(API.categories + '?action=list');
        const data = await res.json();
        if (data.success) {
            allCategories = data.data;
            renderCategoriesTable(allCategories);
        }
    }

    function renderCategoriesTable(cats) {
        const tbody = document.getElementById('categoriesTableBody');
        if (cats.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94a3b8;">No categories found</td></tr>';
            return;
        }
        tbody.innerHTML = cats.map(c => {
            const productCount = allProducts.filter(p => p.category_id == c.id).length;
            return `
            <tr>
                <td>${c.id}</td>
                <td style="font-size:24px;">${c.icon || '-'}</td>
                <td><strong>${escapeHtml(c.name)}</strong></td>
                <td>${escapeHtml(c.description || '-')}</td>
                <td>${productCount}</td>
                <td>${c.status == 1 ? '<span style="color:#16a34a;font-weight:600;">Active</span>' : '<span style="color:#dc2626;">Inactive</span>'}</td>
                <td>
                    <button onclick="openEditCategoryModal(${c.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteCategory(${c.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>`;
        }).join('');
    }

    function openAddCategoryModal() {
        removeModal('category-modal');
        const modal = document.createElement('div');
        modal.id = 'category-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:500px;">
                <div class="modal-header">
                    <h3>Add New Category</h3>
                    <button class="modal-close" onclick="removeModal('category-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        <div class="form-group"><label>Category Name *</label><input name="name" class="input" required></div>
                        <div class="form-group"><label>Icon (emoji)</label><input name="icon" class="input" placeholder="e.g. 💻"></div>
                        <div class="form-group"><label>Description</label><textarea name="description" class="input" rows="3"></textarea></div>
                        <div class="form-actions" style="display:flex;gap:8px;justify-content:flex-end;padding-top:10px;">
                            <button type="button" class="btn-secondary" onclick="removeModal('category-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'add');
            const res = await fetch(API.categories, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Category added successfully!');
                removeModal('category-modal');
                loadCategories();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditCategoryModal(id) {
        const cat = allCategories.find(c => c.id == id);
        if (!cat) return showAlert('Category not found');

        removeModal('category-modal');
        const modal = document.createElement('div');
        modal.id = 'category-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:500px;">
                <div class="modal-header">
                    <h3>Edit Category — ID ${cat.id}</h3>
                    <button class="modal-close" onclick="removeModal('category-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" name="id" value="${cat.id}">
                        <div class="form-group"><label>Category Name *</label><input name="name" class="input" value="${escapeHtml(cat.name)}" required></div>
                        <div class="form-group"><label>Icon (emoji)</label><input name="icon" class="input" value="${escapeHtml(cat.icon)}"></div>
                        <div class="form-group"><label>Description</label><textarea name="description" class="input" rows="3">${escapeHtml(cat.description || '')}</textarea></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status" class="input">
                                <option value="1" ${cat.status == 1 ? 'selected' : ''}>Active</option>
                                <option value="0" ${cat.status == 0 ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                        <div class="form-actions" style="display:flex;gap:8px;justify-content:flex-end;padding-top:10px;">
                            <button type="button" class="btn-secondary" onclick="removeModal('category-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const res = await fetch(API.categories, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Category updated successfully!');
                removeModal('category-modal');
                loadCategories();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    function deleteCategory(id) {
        showConfirmDialog('Delete Category', 'Are you sure you want to delete this category?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.categories, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadCategories(); loadDashboard(); }
        });
    }

    // ============================================================
    // COURSES (Digital Skilling & Entrepreneurship)
    // ============================================================
    async function loadDigitalCourses() {
        const res = await fetch(API.courses + '?action=list&type=digital_skilling');
        const data = await res.json();
        if (data.success) {
            allDigitalCourses = data.data;
            renderCoursesTable(allDigitalCourses, 'digitalCoursesTableBody');
        }
    }

    async function loadEntreprenCourses() {
        const res = await fetch(API.courses + '?action=list&type=entrepreneurship');
        const data = await res.json();
        if (data.success) {
            allEntreprenCourses = data.data;
            renderCoursesTable(allEntreprenCourses, 'entreprenCoursesTableBody');
        }
    }

    function renderCoursesTable(courses, tbodyId) {
        const tbody = document.getElementById(tbodyId);
        if (courses.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#94a3b8;">No courses found</td></tr>';
            return;
        }
        tbody.innerHTML = courses.map(c => `
            <tr>
                <td>${c.id}</td>
                <td style="font-size:24px;">${c.icon || '-'}</td>
                <td>${escapeHtml(c.title)}</td>
                <td>${formatPriceAdmin(c.price)}</td>
                <td>${escapeHtml(c.duration)}</td>
                <td>${c.modules}</td>
                <td>${escapeHtml(c.level)}</td>
                <td>${c.rating} ★</td>
                <td>
                    <button onclick="openEditCourseModal(${c.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteCourse(${c.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function openAddCourseModal(courseType) {
        removeModal('course-modal');
        const typeLabel = courseType === 'digital_skilling' ? 'Digital Skilling' : 'Entrepreneurship';
        const modal = document.createElement('div');
        modal.id = 'course-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>Add New ${typeLabel} Course</h3>
                    <button class="modal-close" onclick="removeModal('course-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addCourseForm" class="form-grid" enctype="multipart/form-data">
                        <input type="hidden" name="course_type" value="${courseType}">
                        <label class="form-group"><span>Title *</span><input name="title" class="input" required></label>
                        <label class="form-group"><span>Price (UGX) *</span><input name="price" type="number" min="0" class="input" value="${courseType === 'entrepreneurship' ? 250000 : 200000}" required></label>
                        <label class="form-group"><span>Duration</span><input name="duration" class="input" placeholder="6 Weeks"></label>
                        <label class="form-group"><span>Level</span>
                            <select name="level" class="input">
                                <option>Beginner</option>
                                <option>Beginner to Intermediate</option>
                                <option>Intermediate</option>
                                <option>Advanced</option>
                            </select>
                        </label>
                        <label class="form-group"><span>Modules</span><input name="modules" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group"><span>Lessons</span><input name="lessons" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group full-width"><span>Description *</span><textarea name="description" class="input" rows="3" required></textarea></label>
                        <label class="form-group"><span>Icon (emoji)</span><input name="icon" class="input" placeholder="e.g. 🖥️"></label>
                        <label class="form-group"><span>Instructor</span><input name="instructor" class="input"></label>
                        <label class="form-group"><span>Image Path</span><input name="image" class="input" placeholder="images/course.jpg"></label>
                        <label class="form-group"><span>Or Upload Image</span><input name="image_file" type="file" accept="image/*" class="input"></label>
                        <label class="form-group"><span>SKU</span><input name="sku" class="input"></label>
                        <label class="form-group"><span>Rating (0-5)</span><input name="rating" type="number" min="0" max="5" step="0.1" class="input" value="0"></label>
                        <label class="form-group"><span>Reviews</span><input name="reviews" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group"><span>Credit Available</span>
                            <select name="credit_available" class="input"><option value="1">Yes</option><option value="0">No</option></select>
                        </label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('course-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Add Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addCourseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'add');
            const res = await fetch(API.courses, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Course added successfully!');
                removeModal('course-modal');
                if (courseType === 'digital_skilling') loadDigitalCourses(); else loadEntreprenCourses();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditCourseModal(id) {
        const res = await fetch(API.courses + '?action=get&id=' + id);
        const data = await res.json();
        if (!data.success) return showAlert('Course not found');
        const c = data.data;
        const typeLabel = c.course_type === 'digital_skilling' ? 'Digital Skilling' : 'Entrepreneurship';

        removeModal('course-modal');
        const modal = document.createElement('div');
        modal.id = 'course-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>Edit ${typeLabel} Course — ID ${c.id}</h3>
                    <button class="modal-close" onclick="removeModal('course-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editCourseForm" class="form-grid" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="${c.id}">
                        <input type="hidden" name="course_type" value="${c.course_type}">
                        <label class="form-group"><span>Title *</span><input name="title" class="input" value="${escapeHtml(c.title)}" required></label>
                        <label class="form-group"><span>Price (UGX) *</span><input name="price" type="number" min="0" class="input" value="${c.price}" required></label>
                        <label class="form-group"><span>Duration</span><input name="duration" class="input" value="${escapeHtml(c.duration)}"></label>
                        <label class="form-group"><span>Level</span>
                            <select name="level" class="input">
                                <option ${c.level === 'Beginner' ? 'selected' : ''}>Beginner</option>
                                <option ${c.level === 'Beginner to Intermediate' ? 'selected' : ''}>Beginner to Intermediate</option>
                                <option ${c.level === 'Intermediate' ? 'selected' : ''}>Intermediate</option>
                                <option ${c.level === 'Advanced' ? 'selected' : ''}>Advanced</option>
                            </select>
                        </label>
                        <label class="form-group"><span>Modules</span><input name="modules" type="number" min="0" class="input" value="${c.modules}"></label>
                        <label class="form-group"><span>Lessons</span><input name="lessons" type="number" min="0" class="input" value="${c.lessons}"></label>
                        <label class="form-group full-width"><span>Description *</span><textarea name="description" class="input" rows="3" required>${escapeHtml(c.description)}</textarea></label>
                        <label class="form-group"><span>Icon (emoji)</span><input name="icon" class="input" value="${escapeHtml(c.icon)}"></label>
                        <label class="form-group"><span>Instructor</span><input name="instructor" class="input" value="${escapeHtml(c.instructor)}"></label>
                        <label class="form-group"><span>Image Path</span><input name="image" class="input" value="${escapeHtml(c.image)}"></label>
                        <label class="form-group"><span>Or Upload New Image</span><input name="image_file" type="file" accept="image/*" class="input"></label>
                        <label class="form-group"><span>SKU</span><input name="sku" class="input" value="${escapeHtml(c.sku)}"></label>
                        <label class="form-group"><span>Rating (0-5)</span><input name="rating" type="number" min="0" max="5" step="0.1" class="input" value="${c.rating}"></label>
                        <label class="form-group"><span>Reviews</span><input name="reviews" type="number" min="0" class="input" value="${c.reviews}"></label>
                        <label class="form-group"><span>Credit Available</span>
                            <select name="credit_available" class="input">
                                <option value="1" ${c.credit_available == 1 ? 'selected' : ''}>Yes</option>
                                <option value="0" ${c.credit_available == 0 ? 'selected' : ''}>No</option>
                            </select>
                        </label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('course-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editCourseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const res = await fetch(API.courses, { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                showAlert('Course updated successfully!');
                removeModal('course-modal');
                if (c.course_type === 'digital_skilling') loadDigitalCourses(); else loadEntreprenCourses();
                loadDashboard();
            } else {
                showAlert(result.message, true);
            }
        });
    }

    function deleteCourse(id) {
        showConfirmDialog('Delete Course', 'Are you sure you want to delete this course?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.courses, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadDigitalCourses(); loadEntreprenCourses(); loadDashboard(); }
        });
    }

    // ============================================================
    // ORDERS (DB-backed)
    // ============================================================
    async function loadOrders() {
        try {
            const res = await fetch(API.orders + '?action=list');
            const data = await res.json();
            if (data.success) {
                allOrders = data.data;
                renderOrdersTable(allOrders);
            }
        } catch (e) { console.error('Orders load failed', e); }
    }

    function getStatusBadge(status) {
        const styles = {
            pending: 'background:#fef3c7;color:#92400e;',
            processing: 'background:#dbeafe;color:#1e40af;',
            completed: 'background:#dcfce7;color:#166534;',
            cancelled: 'background:#fce7e7;color:#991b1b;'
        };
        return `<span style="${styles[status] || styles.pending}padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">${status}</span>`;
    }

    function renderOrdersTable(orders) {
        const tbody = document.getElementById('ordersTableBody');
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#94a3b8;">No orders found</td></tr>';
            return;
        }
        tbody.innerHTML = orders.map(o => `
            <tr>
                <td><strong>${escapeHtml(o.order_number)}</strong></td>
                <td>${escapeHtml(o.customer_name)}</td>
                <td>${escapeHtml(o.customer_email || '-')}</td>
                <td>${escapeHtml(o.customer_phone || '-')}</td>
                <td>${o.order_date ? new Date(o.order_date).toLocaleDateString() : '-'}</td>
                <td>UGX ${formatPriceAdmin(o.total_now || 0)}</td>
                <td>
                    <select onchange="updateOrderStatus(${o.id}, this.value)" class="input" style="padding:4px 8px;font-size:12px;width:auto;">
                        <option value="pending" ${o.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="processing" ${o.status === 'processing' ? 'selected' : ''}>Processing</option>
                        <option value="completed" ${o.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </td>
                <td style="white-space:nowrap;">
                    <button onclick="openViewOrderModal(${o.id})" class="btn-small btn-edit" style="background:#059669;">View</button>
                    <button onclick="openEditOrderModal(${o.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteOrder(${o.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function filterOrdersTable() {
        const term = document.getElementById('orderSearch').value.toLowerCase();
        const status = document.getElementById('orderStatusFilter').value;
        let filtered = allOrders;
        if (term) {
            filtered = filtered.filter(o =>
                (o.order_number || '').toLowerCase().includes(term) ||
                (o.customer_name || '').toLowerCase().includes(term) ||
                (o.customer_email || '').toLowerCase().includes(term) ||
                (o.customer_phone || '').toLowerCase().includes(term)
            );
        }
        if (status) {
            filtered = filtered.filter(o => o.status === status);
        }
        renderOrdersTable(filtered);
    }

    async function updateOrderStatus(id, newStatus) {
        const fd = new FormData();
        fd.append('action', 'update_status');
        fd.append('id', id);
        fd.append('status', newStatus);
        const res = await fetch(API.orders, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            const order = allOrders.find(o => o.id == id);
            if (order) order.status = newStatus;
        } else {
            showAlert(data.message, true);
            loadOrders();
        }
    }

    function openViewOrderModal(id) {
        const o = allOrders.find(x => x.id == id);
        if (!o) return;
        removeModal('order-view-modal');
        let itemsHtml = '<p style="color:#94a3b8;">No items data</p>';
        try {
            const items = JSON.parse(o.items_json || '[]');
            if (items.length > 0) {
                itemsHtml = '<table style="width:100%;font-size:13px;border-collapse:collapse;"><tr style="background:#f8fafc;"><th style="padding:6px;text-align:left;">Item</th><th style="padding:6px;">Qty</th><th style="padding:6px;">Plan</th></tr>' +
                    items.map(i => `<tr><td style="padding:6px;border-bottom:1px solid #e2e8f0;">Product #${i.productId}</td><td style="padding:6px;border-bottom:1px solid #e2e8f0;text-align:center;">${i.quantity}</td><td style="padding:6px;border-bottom:1px solid #e2e8f0;">${i.paymentPlan ? i.paymentPlan.type + (i.paymentPlan.months ? ' (' + i.paymentPlan.months + 'mo)' : '') : 'Full'}</td></tr>`).join('') + '</table>';
            }
        } catch (e) {}

        const modal = document.createElement('div');
        modal.id = 'order-view-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Order ${escapeHtml(o.order_number)}</h3>
                    <button class="modal-close" onclick="removeModal('order-view-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:15px;">
                        <div><strong>Customer:</strong> ${escapeHtml(o.customer_name)}</div>
                        <div><strong>Email:</strong> ${escapeHtml(o.customer_email || '-')}</div>
                        <div><strong>Phone:</strong> ${escapeHtml(o.customer_phone || '-')}</div>
                        <div><strong>Status:</strong> ${getStatusBadge(o.status)}</div>
                        <div><strong>Payment:</strong> ${escapeHtml(o.payment_method || '-')}</div>
                        <div><strong>Date:</strong> ${o.order_date ? new Date(o.order_date).toLocaleString() : '-'}</div>
                        <div><strong>Paid Now:</strong> UGX ${formatPriceAdmin(o.total_now || 0)}</div>
                        <div><strong>Full Total:</strong> UGX ${formatPriceAdmin(o.total_full || 0)}</div>
                    </div>
                    <h4 style="margin:15px 0 8px;">Items</h4>
                    ${itemsHtml}
                    ${o.admin_notes ? '<h4 style="margin:15px 0 8px;">Admin Notes</h4><p>' + escapeHtml(o.admin_notes) + '</p>' : ''}
                    <div style="display:flex;justify-content:flex-end;padding-top:15px;">
                        <button class="btn-secondary" onclick="removeModal('order-view-modal')">Close</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);
    }

    function openAddOrderModal() {
        removeModal('order-modal');
        const modal = document.createElement('div');
        modal.id = 'order-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>Add New Order</h3>
                    <button class="modal-close" onclick="removeModal('order-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addOrderForm" class="form-grid">
                        <label class="form-group"><span>Customer Name *</span><input name="customer_name" class="input" required></label>
                        <label class="form-group"><span>Email</span><input name="customer_email" type="email" class="input"></label>
                        <label class="form-group"><span>Phone</span><input name="customer_phone" class="input"></label>
                        <label class="form-group"><span>Payment Method</span>
                            <select name="payment_method" class="input">
                                <option value="cash_on_delivery">Cash on Delivery</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </label>
                        <label class="form-group"><span>Amount Paid Now (UGX)</span><input name="total_now" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group"><span>Full Total (UGX)</span><input name="total_full" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group"><span>Status</span>
                            <select name="status" class="input">
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </label>
                        <label class="form-group full-width"><span>Admin Notes</span><textarea name="admin_notes" class="input" rows="3"></textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('order-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Create Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addOrderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'add');
            const res = await fetch(API.orders, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Order created! Number: ' + (data.data ? data.data.order_number : ''));
                removeModal('order-modal');
                loadOrders();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditOrderModal(id) {
        const o = allOrders.find(x => x.id == id);
        if (!o) return showAlert('Order not found');

        removeModal('order-modal');
        const modal = document.createElement('div');
        modal.id = 'order-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel">
                <div class="modal-header">
                    <h3>Edit Order — ${escapeHtml(o.order_number)}</h3>
                    <button class="modal-close" onclick="removeModal('order-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editOrderForm" class="form-grid">
                        <input type="hidden" name="id" value="${o.id}">
                        <label class="form-group"><span>Customer Name *</span><input name="customer_name" class="input" value="${escapeHtml(o.customer_name)}" required></label>
                        <label class="form-group"><span>Email</span><input name="customer_email" type="email" class="input" value="${escapeHtml(o.customer_email || '')}"></label>
                        <label class="form-group"><span>Phone</span><input name="customer_phone" class="input" value="${escapeHtml(o.customer_phone || '')}"></label>
                        <label class="form-group"><span>Payment Method</span>
                            <select name="payment_method" class="input">
                                <option value="cash_on_delivery" ${o.payment_method === 'cash_on_delivery' ? 'selected' : ''}>Cash on Delivery</option>
                                <option value="mobile_money" ${o.payment_method === 'mobile_money' ? 'selected' : ''}>Mobile Money</option>
                                <option value="whatsapp" ${o.payment_method === 'whatsapp' ? 'selected' : ''}>WhatsApp</option>
                                <option value="mobile" ${o.payment_method === 'mobile' ? 'selected' : ''}>Mobile</option>
                                <option value="cod" ${o.payment_method === 'cod' ? 'selected' : ''}>COD</option>
                            </select>
                        </label>
                        <label class="form-group"><span>Amount Paid Now (UGX)</span><input name="total_now" type="number" min="0" class="input" value="${o.total_now || 0}"></label>
                        <label class="form-group"><span>Full Total (UGX)</span><input name="total_full" type="number" min="0" class="input" value="${o.total_full || 0}"></label>
                        <label class="form-group"><span>Status</span>
                            <select name="status" class="input">
                                <option value="pending" ${o.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="processing" ${o.status === 'processing' ? 'selected' : ''}>Processing</option>
                                <option value="completed" ${o.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </label>
                        <label class="form-group full-width"><span>Admin Notes</span><textarea name="admin_notes" class="input" rows="3">${escapeHtml(o.admin_notes || '')}</textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('order-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editOrderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const res = await fetch(API.orders, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Order updated successfully!');
                removeModal('order-modal');
                loadOrders();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    function deleteOrder(id) {
        showConfirmDialog('Delete Order', 'Are you sure you want to delete this order?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.orders, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadOrders(); loadDashboard(); }
        });
    }

    // ============================================================
    // CUSTOMERS
    // ============================================================
    async function loadCustomers() {
        try {
            const res = await fetch(API.customers + '?action=list');
            const data = await res.json();
            if (data.success) {
                allCustomers = data.data;
                renderCustomersTable(allCustomers);
            }
        } catch (e) { console.error('Customers load failed', e); }
    }

    function renderCustomersTable(customers) {
        const tbody = document.getElementById('customersTableBody');
        if (customers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94a3b8;">No customers found</td></tr>';
            return;
        }
        tbody.innerHTML = customers.map(c => `
            <tr>
                <td>${c.id}</td>
                <td><strong>${escapeHtml(c.name)}</strong></td>
                <td>${escapeHtml(c.email)}</td>
                <td>${escapeHtml(c.phone || '-')}</td>
                <td>${c.order_count || 0}</td>
                <td>${c.created_at ? new Date(c.created_at).toLocaleDateString() : '-'}</td>
                <td>
                    <button onclick="openEditCustomerModal(${c.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteCustomer(${c.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function filterCustomersTable() {
        const term = document.getElementById('customerSearch').value.toLowerCase();
        const filtered = allCustomers.filter(c =>
            c.name.toLowerCase().includes(term) ||
            c.email.toLowerCase().includes(term) ||
            (c.phone || '').toLowerCase().includes(term)
        );
        renderCustomersTable(filtered);
    }

    function openAddCustomerModal() {
        removeModal('customer-modal');
        const modal = document.createElement('div');
        modal.id = 'customer-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Add New Customer</h3>
                    <button class="modal-close" onclick="removeModal('customer-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addCustomerForm" class="form-grid">
                        <label class="form-group"><span>Name *</span><input name="name" class="input" required></label>
                        <label class="form-group"><span>Email *</span><input name="email" type="email" class="input" required></label>
                        <label class="form-group"><span>Phone</span><input name="phone" class="input"></label>
                        <label class="form-group"><span>City</span><input name="city" class="input"></label>
                        <label class="form-group"><span>Country</span><input name="country" class="input" value="Uganda"></label>
                        <label class="form-group full-width"><span>Address</span><textarea name="address" class="input" rows="2"></textarea></label>
                        <label class="form-group full-width"><span>Notes</span><textarea name="notes" class="input" rows="2"></textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('customer-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Add Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addCustomerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'add');
            const res = await fetch(API.customers, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Customer added successfully!');
                removeModal('customer-modal');
                loadCustomers();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditCustomerModal(id) {
        const c = allCustomers.find(x => x.id == id);
        if (!c) return showAlert('Customer not found');

        removeModal('customer-modal');
        const modal = document.createElement('div');
        modal.id = 'customer-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Edit Customer — ID ${c.id}</h3>
                    <button class="modal-close" onclick="removeModal('customer-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editCustomerForm" class="form-grid">
                        <input type="hidden" name="id" value="${c.id}">
                        <label class="form-group"><span>Name *</span><input name="name" class="input" value="${escapeHtml(c.name)}" required></label>
                        <label class="form-group"><span>Email *</span><input name="email" type="email" class="input" value="${escapeHtml(c.email)}" required></label>
                        <label class="form-group"><span>Phone</span><input name="phone" class="input" value="${escapeHtml(c.phone || '')}"></label>
                        <label class="form-group"><span>City</span><input name="city" class="input" value="${escapeHtml(c.city || '')}"></label>
                        <label class="form-group"><span>Country</span><input name="country" class="input" value="${escapeHtml(c.country || 'Uganda')}"></label>
                        <label class="form-group full-width"><span>Address</span><textarea name="address" class="input" rows="2">${escapeHtml(c.address || '')}</textarea></label>
                        <label class="form-group full-width"><span>Notes</span><textarea name="notes" class="input" rows="2">${escapeHtml(c.notes || '')}</textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('customer-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editCustomerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const res = await fetch(API.customers, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Customer updated successfully!');
                removeModal('customer-modal');
                loadCustomers();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    function deleteCustomer(id) {
        showConfirmDialog('Delete Customer', 'Are you sure you want to delete this customer?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.customers, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadCustomers(); loadDashboard(); }
        });
    }

    // ============================================================
    // REVIEWS
    // ============================================================
    async function loadReviews() {
        try {
            const res = await fetch(API.reviews + '?action=list_all');
            const data = await res.json();
            if (data.success) {
                allReviews = data.data;
                renderReviewsTable(allReviews);
            }
        } catch (e) { console.error('Reviews load failed', e); }
    }

    function getReviewStatusBadge(status) {
        const styles = {
            pending: 'background:#fef3c7;color:#92400e;',
            approved: 'background:#dcfce7;color:#166534;',
            rejected: 'background:#fce7e7;color:#991b1b;'
        };
        return `<span style="${styles[status] || styles.pending}padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">${status}</span>`;
    }

    function renderReviewsTable(reviews) {
        const tbody = document.getElementById('reviewsTableBody');
        if (reviews.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#94a3b8;">No reviews found</td></tr>';
            return;
        }
        tbody.innerHTML = reviews.map(r => `
            <tr>
                <td>${r.id}</td>
                <td>${escapeHtml(r.item_title || (r.item_type + ' #' + r.item_id))}<br><span style="font-size:11px;color:#94a3b8;">${r.item_type}</span></td>
                <td>${escapeHtml(r.customer_name)}</td>
                <td>${r.rating} ★</td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(r.review_text)}</td>
                <td>${getReviewStatusBadge(r.status)}</td>
                <td>${r.created_at ? new Date(r.created_at).toLocaleDateString() : '-'}</td>
                <td style="white-space:nowrap;">
                    ${r.status !== 'approved' ? `<button onclick="approveReview(${r.id}, 'approved')" class="btn-small" style="background:#059669;color:#fff;margin-bottom:2px;">Approve</button>` : ''}
                    ${r.status !== 'rejected' ? `<button onclick="approveReview(${r.id}, 'rejected')" class="btn-small" style="background:#d97706;color:#fff;margin-bottom:2px;">Reject</button>` : ''}
                    <button onclick="openEditReviewModal(${r.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteReview(${r.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function filterReviewsTable() {
        const term = document.getElementById('reviewSearch').value.toLowerCase();
        const status = document.getElementById('reviewStatusFilter').value;
        let filtered = allReviews;
        if (term) {
            filtered = filtered.filter(r =>
                (r.customer_name || '').toLowerCase().includes(term) ||
                (r.review_text || '').toLowerCase().includes(term) ||
                (r.item_title || '').toLowerCase().includes(term)
            );
        }
        if (status) {
            filtered = filtered.filter(r => r.status === status);
        }
        renderReviewsTable(filtered);
    }

    async function approveReview(id, newStatus) {
        const fd = new FormData();
        fd.append('action', 'approve');
        fd.append('id', id);
        fd.append('status', newStatus);
        const res = await fetch(API.reviews, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            loadReviews();
            loadDashboard();
            loadProducts();
        } else {
            showAlert(data.message, true);
        }
    }

    function openAddReviewModal() {
        removeModal('review-modal');
        // Build product and course options
        const productOpts = allProducts.map(p => `<option value="product-${p.id}">${escapeHtml(p.title)} (Product)</option>`).join('');
        const courseOpts = [...allDigitalCourses, ...allEntreprenCourses].map(c => `<option value="course-${c.id}">${escapeHtml(c.title)} (Course)</option>`).join('');

        const modal = document.createElement('div');
        modal.id = 'review-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Add New Review</h3>
                    <button class="modal-close" onclick="removeModal('review-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addReviewForm" class="form-grid">
                        <label class="form-group full-width"><span>Product / Course *</span>
                            <select name="item_ref" class="input" required>
                                <option value="">Select item...</option>
                                <optgroup label="Products">${productOpts}</optgroup>
                                <optgroup label="Courses">${courseOpts}</optgroup>
                            </select>
                        </label>
                        <label class="form-group"><span>Customer Name *</span><input name="customer_name" class="input" required></label>
                        <label class="form-group"><span>Customer Email</span><input name="customer_email" type="email" class="input"></label>
                        <label class="form-group"><span>Rating (1-5) *</span><input name="rating" type="number" min="1" max="5" step="0.1" class="input" value="5" required></label>
                        <label class="form-group"><span>Status</span>
                            <select name="status" class="input">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </label>
                        <label class="form-group full-width"><span>Review Text *</span><textarea name="review_text" class="input" rows="4" required></textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('review-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Add Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addReviewForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            const ref = fd.get('item_ref').split('-');
            fd.delete('item_ref');
            fd.append('item_type', ref[0]);
            fd.append('item_id', ref[1]);
            fd.append('action', 'add');
            const res = await fetch(API.reviews, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Review added successfully!');
                removeModal('review-modal');
                loadReviews();
                loadDashboard();
                loadProducts();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditReviewModal(id) {
        const r = allReviews.find(x => x.id == id);
        if (!r) return showAlert('Review not found');

        removeModal('review-modal');
        const modal = document.createElement('div');
        modal.id = 'review-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Edit Review — ID ${r.id}</h3>
                    <button class="modal-close" onclick="removeModal('review-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editReviewForm" class="form-grid">
                        <input type="hidden" name="id" value="${r.id}">
                        <label class="form-group"><span>Item</span><input class="input" value="${escapeHtml(r.item_title || r.item_type + ' #' + r.item_id)}" disabled></label>
                        <label class="form-group"><span>Customer Name *</span><input name="customer_name" class="input" value="${escapeHtml(r.customer_name)}" required></label>
                        <label class="form-group"><span>Rating (1-5) *</span><input name="rating" type="number" min="1" max="5" step="0.1" class="input" value="${r.rating}" required></label>
                        <label class="form-group"><span>Status</span>
                            <select name="status" class="input">
                                <option value="pending" ${r.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="approved" ${r.status === 'approved' ? 'selected' : ''}>Approved</option>
                                <option value="rejected" ${r.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        </label>
                        <label class="form-group full-width"><span>Review Text *</span><textarea name="review_text" class="input" rows="4" required>${escapeHtml(r.review_text)}</textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('review-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editReviewForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const res = await fetch(API.reviews, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Review updated successfully!');
                removeModal('review-modal');
                loadReviews();
                loadDashboard();
                loadProducts();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    function deleteReview(id) {
        showConfirmDialog('Delete Review', 'Are you sure you want to delete this review?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.reviews, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadReviews(); loadDashboard(); loadProducts(); }
        });
    }

    // ============================================================
    // TESTIMONIALS
    // ============================================================
    async function loadTestimonials() {
        try {
            const res = await fetch(API.testimonials + '?action=list_all');
            const data = await res.json();
            if (data.success) {
                allTestimonials = data.data;
                renderTestimonialsTable(allTestimonials);
            }
        } catch (e) { console.error('Testimonials load failed', e); }
    }

    function renderTestimonialsTable(items) {
        const tbody = document.getElementById('testimonialsTableBody');
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#94a3b8;">No testimonials found</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(t => `
            <tr>
                <td>${t.id}</td>
                <td>${t.image ? `<img src="${escapeHtml(t.image)}" class="img-preview" onerror="this.src='images/user1.jpg'">` : '-'}</td>
                <td><strong>${escapeHtml(t.name)}</strong></td>
                <td>${escapeHtml(t.role || '-')}</td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(t.content)}</td>
                <td>${t.rating} ★</td>
                <td>${t.status == 1 ? '<span style="color:#16a34a;font-weight:600;">Active</span>' : '<span style="color:#dc2626;">Inactive</span>'}</td>
                <td>${t.display_order}</td>
                <td>
                    <button onclick="openEditTestimonialModal(${t.id})" class="btn-small btn-edit">Edit</button>
                    <button onclick="deleteTestimonial(${t.id})" class="btn-small btn-delete">Delete</button>
                </td>
            </tr>
        `).join('');
    }

    function openAddTestimonialModal() {
        removeModal('testimonial-modal');
        const modal = document.createElement('div');
        modal.id = 'testimonial-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Add New Testimonial</h3>
                    <button class="modal-close" onclick="removeModal('testimonial-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="addTestimonialForm" class="form-grid">
                        <label class="form-group"><span>Name *</span><input name="name" class="input" required></label>
                        <label class="form-group"><span>Role</span><input name="role" class="input" placeholder="e.g. Business owner"></label>
                        <label class="form-group"><span>Image Path</span><input name="image" class="input" placeholder="images/user1.jpg"></label>
                        <label class="form-group"><span>Rating (1-5)</span><input name="rating" type="number" min="1" max="5" class="input" value="5"></label>
                        <label class="form-group"><span>Status</span>
                            <select name="status" class="input"><option value="1">Active</option><option value="0">Inactive</option></select>
                        </label>
                        <label class="form-group"><span>Display Order</span><input name="display_order" type="number" min="0" class="input" value="0"></label>
                        <label class="form-group full-width"><span>Content *</span><textarea name="content" class="input" rows="4" required></textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('testimonial-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Add Testimonial</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('addTestimonialForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'add');
            const res = await fetch(API.testimonials, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Testimonial added successfully!');
                removeModal('testimonial-modal');
                loadTestimonials();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    async function openEditTestimonialModal(id) {
        const t = allTestimonials.find(x => x.id == id);
        if (!t) return showAlert('Testimonial not found');

        removeModal('testimonial-modal');
        const modal = document.createElement('div');
        modal.id = 'testimonial-modal';
        modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-panel" style="max-width:600px;">
                <div class="modal-header">
                    <h3>Edit Testimonial — ID ${t.id}</h3>
                    <button class="modal-close" onclick="removeModal('testimonial-modal')">✕</button>
                </div>
                <div class="modal-body">
                    <form id="editTestimonialForm" class="form-grid">
                        <input type="hidden" name="id" value="${t.id}">
                        <label class="form-group"><span>Name *</span><input name="name" class="input" value="${escapeHtml(t.name)}" required></label>
                        <label class="form-group"><span>Role</span><input name="role" class="input" value="${escapeHtml(t.role || '')}"></label>
                        <label class="form-group"><span>Image Path</span><input name="image" class="input" value="${escapeHtml(t.image || '')}"></label>
                        <label class="form-group"><span>Rating (1-5)</span><input name="rating" type="number" min="1" max="5" class="input" value="${t.rating}"></label>
                        <label class="form-group"><span>Status</span>
                            <select name="status" class="input">
                                <option value="1" ${t.status == 1 ? 'selected' : ''}>Active</option>
                                <option value="0" ${t.status == 0 ? 'selected' : ''}>Inactive</option>
                            </select>
                        </label>
                        <label class="form-group"><span>Display Order</span><input name="display_order" type="number" min="0" class="input" value="${t.display_order}"></label>
                        <label class="form-group full-width"><span>Content *</span><textarea name="content" class="input" rows="4" required>${escapeHtml(t.content)}</textarea></label>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="removeModal('testimonial-modal')">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;
        document.body.appendChild(modal);

        document.getElementById('editTestimonialForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'edit');
            const res = await fetch(API.testimonials, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert('Testimonial updated successfully!');
                removeModal('testimonial-modal');
                loadTestimonials();
                loadDashboard();
            } else {
                showAlert(data.message, true);
            }
        });
    }

    function deleteTestimonial(id) {
        showConfirmDialog('Delete Testimonial', 'Are you sure you want to delete this testimonial?', 'Delete', async function() {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', id);
            const res = await fetch(API.testimonials, { method: 'POST', body: fd });
            const data = await res.json();
            showAlert(data.message);
            if (data.success) { loadTestimonials(); loadDashboard(); }
        });
    }

    // ============================================================
    // TAB SWITCHING
    // ============================================================
    document.querySelectorAll('.tab-btn[data-tab]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            const tabName = this.getAttribute('data-tab');
            document.getElementById(tabName + 'Tab').classList.add('active');
            this.classList.add('active');
        });
    });

    // ============================================================
    // INIT
    // ============================================================
    window.addEventListener('DOMContentLoaded', () => {
        checkAuth();

        document.getElementById('adminLogoutBtn').addEventListener('click', logoutAdmin);

        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideError();
            const username = document.getElementById('adminUsername').value.trim();
            const password = document.getElementById('adminPassword').value;
            const result = await loginAdmin(username, password);
            if (result.success) {
                grantAccess();
            } else {
                showError(result.message);
            }
        });

        document.getElementById('adminCancelBtn').addEventListener('click', () => {
            window.location.href = '/Zaga/';
        });

        if (typeof updateCartCount === 'function') updateCartCount();
    });
    </script>
</body>
</html>
