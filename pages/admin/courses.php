<?php
$admin_page = 'courses';
$page_title = 'Courses';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Courses</h1>
      </div>

      <!-- Digital Skilling Courses -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Digital Skilling Courses</h2>
          <button class="admin-btn admin-btn-primary" onclick="openAddCourseModal('digital_skilling')">+ Add Course</button>
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Icon</th><th>Title</th><th>Price (UGX)</th><th>Duration</th><th>Modules</th><th>Level</th><th>Rating</th><th>Actions</th></tr>
            </thead>
            <tbody id="digitalCoursesTableBody">
              <tr><td colspan="9" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Entrepreneurship Courses -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Entrepreneurship Courses</h2>
          <button class="admin-btn admin-btn-primary" onclick="openAddCourseModal('entrepreneurship')">+ Add Course</button>
        </div>
        <div class="admin-table-wrapper">
          <table class="admin-table">
            <thead>
              <tr><th>ID</th><th>Icon</th><th>Title</th><th>Price (UGX)</th><th>Duration</th><th>Modules</th><th>Level</th><th>Rating</th><th>Actions</th></tr>
            </thead>
            <tbody id="entreprenCoursesTableBody">
              <tr><td colspan="9" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php require_once __DIR__ . '/footer.php'; ?>
