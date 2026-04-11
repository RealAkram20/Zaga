<?php
$admin_page = 'testimonials';
$page_title = 'Testimonials';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Testimonials</h1>
        <button class="admin-btn admin-btn-primary" onclick="openAddTestimonialModal()">+ Add New Testimonial</button>
      </div>
      <div class="admin-card">
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Image</th><th>Name</th><th>Role</th><th>Content</th><th>Rating</th><th>Status</th><th>Order</th><th>Actions</th></tr>
            </thead>
            <tbody id="testimonialsTableBody">
              <tr><td colspan="9" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
