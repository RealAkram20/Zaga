<?php
$admin_page = 'dashboard';
$page_title = 'Dashboard';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">

      <div class="admin-page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo safe_output($_SESSION['admin_name'] ?? 'Admin'); ?>!</p>
      </div>

      <!-- Stats Grid -->
      <div class="admin-stats-grid">
        <div class="admin-stat-card border-primary">
          <div class="stat-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
          <div class="stat-info"><span class="stat-label">Products</span><span class="stat-value" id="statProducts">0</span></div>
        </div>
        <div class="admin-stat-card border-success">
          <div class="stat-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div>
          <div class="stat-info"><span class="stat-label">Digital Courses</span><span class="stat-value" id="statDigitalCourses">0</span></div>
        </div>
        <div class="admin-stat-card border-warning">
          <div class="stat-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
          <div class="stat-info"><span class="stat-label">Entrepreneurship</span><span class="stat-value" id="statEntreprenCourses">0</span></div>
        </div>
        <div class="admin-stat-card border-primary">
          <div class="stat-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
          <div class="stat-info"><span class="stat-label">Orders</span><span class="stat-value" id="statOrders">0</span></div>
        </div>
        <div class="admin-stat-card border-success">
          <div class="stat-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
          <div class="stat-info"><span class="stat-label">Revenue</span><span class="stat-value" id="statRevenue">UGX 0</span></div>
        </div>
        <div class="admin-stat-card border-info">
          <div class="stat-icon"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
          <div class="stat-info"><span class="stat-label">Customers</span><span class="stat-value" id="statCustomers">0</span></div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Recent Orders</h2>
          <a href="<?php echo SITE_URL; ?>/admin/orders" class="admin-btn admin-btn-sm">View All</a>
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total (UGX)</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="recentOrdersBody">
              <tr><td colspan="6" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Quick Actions</h2>
        </div>
        <div class="admin-quick-actions">
          <a href="<?php echo SITE_URL; ?>/admin/products" class="admin-btn admin-btn-primary">Manage Products</a>
          <a href="<?php echo SITE_URL; ?>/admin/categories" class="admin-btn admin-btn-info">Manage Categories</a>
          <a href="<?php echo SITE_URL; ?>/admin/courses" class="admin-btn admin-btn-success">Manage Courses</a>
          <a href="<?php echo SITE_URL; ?>/admin/orders" class="admin-btn admin-btn-primary">Manage Orders</a>
          <a href="<?php echo SITE_URL; ?>/admin/customers" class="admin-btn admin-btn-info">Manage Customers</a>
          <a href="<?php echo SITE_URL; ?>/admin/reviews" class="admin-btn admin-btn-warning">Manage Reviews</a>
          <a href="<?php echo SITE_URL; ?>/admin/testimonials" class="admin-btn admin-btn-primary">Manage Testimonials</a>
        </div>
      </div>

    </div>

<script>
// Fallback: if stats don't load within 3 seconds, show debug info
setTimeout(function() {
    var el = document.getElementById('statProducts');
    if (el && el.textContent === '0') {
        fetch('<?php echo SITE_URL; ?>/api/dashboard.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    document.getElementById('statProducts').textContent = data.data.total_products;
                    document.getElementById('statDigitalCourses').textContent = data.data.total_digital_courses;
                    document.getElementById('statEntreprenCourses').textContent = data.data.total_entrepreneurship_courses;
                    document.getElementById('statOrders').textContent = data.data.total_orders;
                    document.getElementById('statRevenue').textContent = 'UGX ' + Number(data.data.total_revenue).toLocaleString();
                    document.getElementById('statCustomers').textContent = data.data.total_customers;
                }
            })
            .catch(function(err) { console.error('Dashboard fallback error:', err); });
    }
}, 3000);
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
