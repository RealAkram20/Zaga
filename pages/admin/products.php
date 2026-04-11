<?php
$admin_page = 'products';
$page_title = 'Products';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Products</h1>
        <button class="admin-btn admin-btn-primary" onclick="openAddProductModal()">+ Add New Product</button>
      </div>
      <div class="admin-card">
        <div class="admin-search-bar">
          <input type="text" id="productSearch" placeholder="Search products...">
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Image</th><th>Title</th><th>Category</th><th>Price (UGX)</th><th>Stock</th><th>Rating</th><th>Actions</th></tr>
            </thead>
            <tbody id="productsTableBody">
              <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
