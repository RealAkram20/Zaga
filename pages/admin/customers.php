<?php
$admin_page = 'customers';
$page_title = 'Customers';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Customers</h1>
        <button class="admin-btn admin-btn-primary" onclick="openAddCustomerModal()">+ Add New Customer</button>
      </div>
      <div class="admin-card">
        <div class="admin-search-bar">
          <input type="text" id="customerSearch" placeholder="Search customers...">
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody id="customersTableBody">
              <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
