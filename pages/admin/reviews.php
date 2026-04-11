<?php
$admin_page = 'reviews';
$page_title = 'Reviews';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Reviews</h1>
        <button class="admin-btn admin-btn-primary" onclick="openAddReviewModal()">+ Add New Review</button>
      </div>
      <div class="admin-card">
        <div class="admin-search-bar">
          <input type="text" id="reviewSearch" placeholder="Search reviews...">
          <select id="reviewStatusFilter">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Item</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody id="reviewsTableBody">
              <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
