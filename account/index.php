<?php
// ============================================================
// Zaga Technologies - Customer Dashboard
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_login();

$user = current_user();
$conn = getDbConnection();

// --- Fetch stats ---

// Total orders
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE customer_email = ?");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Active credit plans — completed orders with credit items that still have unpaid schedule entries
$stmt = $conn->prepare("SELECT id, total_now, total_full, payments_made_json, items_json, schedule_json FROM orders WHERE customer_email = ? AND status = 'completed'");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$creditResult = $stmt->get_result();
$activeCredit = 0;
while ($creditRow = $creditResult->fetch_assoc()) {
    // Check if order has credit items
    $items = json_decode($creditRow['items_json'] ?? '[]', true) ?: [];
    $hasCredit = false;
    foreach ($items as $item) {
        if (isset($item['paymentPlan']) && ($item['paymentPlan']['type'] ?? '') === 'credit') { $hasCredit = true; break; }
    }
    if (!$hasCredit && $creditRow['schedule_json'] && $creditRow['schedule_json'] !== '[]') $hasCredit = true;
    if (!$hasCredit) continue;

    $paidAmount = 0;
    $payments = json_decode($creditRow['payments_made_json'] ?? '[]', true) ?: [];
    foreach ($payments as $p) $paidAmount += floatval($p['amount'] ?? 0);
    $deposit = floatval($creditRow['total_now']);
    $total = floatval($creditRow['total_full']);
    if (($deposit + $paidAmount) < $total) $activeCredit++;
}
$stmt->close();

// Courses enrolled (orders that contain course items)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE customer_email = ? AND items_json LIKE '%course%' AND status != 'cancelled'");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$coursesEnrolled = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Recent orders (last 5)
$stmt = $conn->prepare("SELECT id, order_number, total_now, total_full, payment_method, status, items_json, order_date FROM orders WHERE customer_email = ? ORDER BY order_date DESC LIMIT 5");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$recentOrdersResult = $stmt->get_result();
$recentOrders = [];
while ($row = $recentOrdersResult->fetch_assoc()) {
    $recentOrders[] = $row;
}
$stmt->close();

$conn->close();

// --- Render page ---
$page_title   = 'My Account';
$current_page = 'account';
$sidebar_page = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="dashboard-page-header">
            <div>
                <h1>Welcome, <?php echo safe_output($user['name']); ?>!</h1>
                <p>Here is an overview of your account activity.</p>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-card-light">
                <div class="stat-icon icon-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <div>
                    <h3>Total Orders</h3>
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                </div>
            </div>

            <div class="stat-card-light">
                <div class="stat-icon icon-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                </div>
                <div>
                    <h3>Ongoing Credits</h3>
                    <div class="stat-value"><?php echo $activeCredit; ?></div>
                </div>
            </div>

            <div class="stat-card-light">
                <div class="stat-icon icon-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                </div>
                <div>
                    <h3>Courses Enrolled</h3>
                    <div class="stat-value"><?php echo $coursesEnrolled; ?></div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="data-section">
            <div class="data-section-header">
                <h3>Recent Orders</h3>
                <a href="<?php echo SITE_URL; ?>/account/orders" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="data-section-body">
                <?php if (empty($recentOrders)): ?>
                    <div style="padding: var(--spacing-2xl); text-align: center; color: var(--color-text-muted);">
                        <p>You haven't placed any orders yet.</p>
                        <a href="<?php echo SITE_URL; ?>/shop" class="btn btn-primary btn-sm" style="margin-top: var(--spacing-base);">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo safe_output($order['order_number']); ?></strong></td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <?php
                                    $methods = [
                                        'credit'           => 'Credit Plan',
                                        'cash_on_delivery'  => 'Cash on Delivery',
                                        'mobile_money'     => 'Mobile Money',
                                        'bank_transfer'    => 'Bank Transfer',
                                    ];
                                    echo safe_output($methods[$order['payment_method']] ?? ucwords(str_replace('_', ' ', $order['payment_method'])));
                                    ?>
                                </td>
                                <td>UGX <?php echo number_format($order['total_full'] ?: $order['total_now'], 0); ?></td>
                                <td><span class="status-badge <?php echo safe_output($order['status']); ?>"><?php echo ucfirst(safe_output($order['status'])); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/shop" class="quick-action-card">
                <div class="quick-action-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </div>
                <h4>Browse Shop</h4>
            </a>
            <a href="<?php echo SITE_URL; ?>/courses" class="quick-action-card">
                <div class="quick-action-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                </div>
                <h4>View Courses</h4>
            </a>
            <a href="<?php echo SITE_URL; ?>/account/profile" class="quick-action-card">
                <div class="quick-action-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h4>Edit Profile</h4>
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
