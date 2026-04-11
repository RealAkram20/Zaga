<?php
$admin_page = 'orders';
$page_title = 'Orders';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Orders</h1>
        <button class="admin-btn admin-btn-primary" onclick="openAddOrderModal()">+ Add New Order</button>
      </div>
      <div class="admin-card">
        <div class="admin-search-bar">
          <input type="text" id="orderSearch" placeholder="Search orders...">
          <select id="orderStatusFilter">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>Order #</th><th>Customer</th><th>Email</th><th>Phone</th><th>Date</th><th>Total (UGX)</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="ordersTableBody">
              <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
