<?php session_start(); ?>
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
            </div>
        </div>

        <!-- Admin Tabs -->
        <div class="admin-tabs">
            <button class="tab-btn active" data-tab="products">Products</button>
            <button class="tab-btn" data-tab="categories">Categories</button>
            <button class="tab-btn" data-tab="digitalCourses">Digital Skilling Courses</button>
            <button class="tab-btn" data-tab="entreprenCourses">Entrepreneurship Courses</button>
            <button class="tab-btn" data-tab="orders">Orders</button>
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

        <!-- ==================== ORDERS TAB ==================== -->
        <div id="ordersTab" class="tab-content">
            <h2>Recent Orders</h2>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr><th>Order #</th><th>Customer</th><th>Date</th><th>Total (UGX)</th><th>Status</th></tr>
                    </thead>
                    <tbody id="ordersTableBody"><tr><td colspan="5" style="text-align:center; color:#94a3b8;">No orders yet</td></tr></tbody>
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
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#334155;">Username</label>
                    <input type="text" id="adminUsername" class="input" placeholder="admin" value="admin" style="width:100%;padding:12px;" required>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#334155;">Password</label>
                    <input type="password" id="adminPassword" class="input" placeholder="Enter password" style="width:100%;padding:12px;" required>
                </div>
                <button type="submit" class="btn-primary" style="padding:12px;font-size:15px;">Login</button>
                <button type="button" id="adminCancelBtn" class="btn-secondary" style="padding:10px;font-size:13px;">Cancel</button>
                <p id="adminError" style="display:none;color:#dc2626;font-size:13px;margin:0;"></p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
    // ============================================================
    // ADMIN DASHBOARD - PHP/MySQL Integration
    // ============================================================

    const API = {
        auth: 'api/auth.php',
        products: 'api/products.php',
        categories: 'api/categories.php',
        courses: 'api/courses.php',
        dashboard: 'api/dashboard.php'
    };

    let allProducts = [];
    let allCategories = [];
    let allDigitalCourses = [];
    let allEntreprenCourses = [];

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
        alert(msg);
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
            }
        } catch (e) { console.error('Dashboard load failed', e); }

        loadProducts();
        loadCategories();
        loadDigitalCourses();
        loadEntreprenCourses();
        loadOrders();
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

    async function deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.products, { method: 'POST', body: fd });
        const data = await res.json();
        showAlert(data.message);
        if (data.success) { loadProducts(); loadDashboard(); }
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

    async function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.categories, { method: 'POST', body: fd });
        const data = await res.json();
        showAlert(data.message);
        if (data.success) { loadCategories(); loadDashboard(); }
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

    async function deleteCourse(id) {
        if (!confirm('Are you sure you want to delete this course?')) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        const res = await fetch(API.courses, { method: 'POST', body: fd });
        const data = await res.json();
        showAlert(data.message);
        if (data.success) { loadDigitalCourses(); loadEntreprenCourses(); loadDashboard(); }
    }

    // ============================================================
    // ORDERS
    // ============================================================
    async function loadOrders() {
        // Orders still from localStorage for now (future: migrate to DB)
        const orders = JSON.parse(localStorage.getItem('orders') || '[]');
        const tbody = document.getElementById('ordersTableBody');
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;">No orders yet</td></tr>';
            return;
        }
        tbody.innerHTML = orders.map(o => `
            <tr>
                <td>${escapeHtml(o.orderNumber)}</td>
                <td>${escapeHtml(o.customer)}</td>
                <td>${escapeHtml(o.date)}</td>
                <td>${o.totalNow ? 'UGX ' + formatPriceAdmin(o.totalNow) : (o.total || '-')}</td>
                <td><span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">Completed</span></td>
            </tr>
        `).join('');
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
