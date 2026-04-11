<?php
// ============================================================
// Zaga Technologies - My Courses
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_login();

$user = current_user();
$conn = getDbConnection();

// Fetch orders that contain course items
$stmt = $conn->prepare("SELECT id, order_number, items_json, status, order_date FROM orders WHERE customer_email = ? AND items_json LIKE '%course%' AND status != 'cancelled' ORDER BY order_date DESC");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$ordersResult = $stmt->get_result();

$courses = [];
while ($row = $ordersResult->fetch_assoc()) {
    $items = json_decode($row['items_json'] ?? '[]', true) ?: [];
    foreach ($items as $item) {
        $type = $item['type'] ?? $item['item_type'] ?? '';
        // Include items that look like courses
        if (stripos($type, 'course') !== false || stripos($item['name'] ?? $item['title'] ?? '', 'course') !== false) {
            $courses[] = [
                'name'         => $item['name'] ?? $item['title'] ?? 'Course',
                'order_number' => $row['order_number'],
                'order_status' => $row['status'],
                'order_date'   => $row['order_date'],
                'price'        => $item['price'] ?? 0,
            ];
        }
    }
}
$stmt->close();
$conn->close();

// --- Render page ---
$page_title   = 'My Courses';
$current_page = 'account';
$sidebar_page = 'courses';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="dashboard-page-header">
            <div>
                <h1>My Courses</h1>
                <p>Courses you have enrolled in through your orders.</p>
            </div>
            <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-primary btn-sm">Browse Courses</a>
        </div>

        <?php if (empty($courses)): ?>
            <div class="data-section">
                <div style="padding: var(--spacing-3xl); text-align: center; color: var(--color-text-muted);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: var(--spacing-base); opacity: 0.5;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    <p style="font-size: var(--font-md); margin-bottom: var(--spacing-base);">You haven't enrolled in any courses yet.</p>
                    <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-primary">Browse Courses</a>
                </div>
            </div>
        <?php else: ?>
            <div class="data-section">
                <div class="data-section-header">
                    <h3>Enrolled Courses (<?php echo count($courses); ?>)</h3>
                </div>
                <div class="data-section-body">
                    <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Order #</th>
                                <th>Enrolled On</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><strong><?php echo safe_output($course['name']); ?></strong></td>
                                <td><?php echo safe_output($course['order_number']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($course['order_date'])); ?></td>
                                <td>UGX <?php echo number_format(floatval($course['price']), 0); ?></td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'completed'  => ['Active', 'badge-success'],
                                        'processing' => ['Processing', 'badge-info'],
                                        'pending'    => ['Pending', 'badge-warning'],
                                    ];
                                    $s = $statusMap[$course['order_status']] ?? ['Pending', 'badge-warning'];
                                    ?>
                                    <span class="badge <?php echo $s[1]; ?>"><?php echo $s[0]; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
