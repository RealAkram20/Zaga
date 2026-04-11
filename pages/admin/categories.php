<?php
$admin_page = 'categories';
$page_title = 'Categories';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Categories</h1>
        <button class="admin-btn admin-btn-primary" onclick="openAddCategoryModal()">+ Add New Category</button>
      </div>
      <div class="admin-card">
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Icon</th><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="categoriesTableBody">
              <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
