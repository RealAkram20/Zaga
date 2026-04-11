<?php
// ============================================================
// Zaga Technologies - Customer Order History
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_login();

$user = current_user();
$conn = getDbConnection();

// Build product/course lookup maps for resolving item names in orders
$productMap = [];
$res = $conn->query("SELECT id, title, slug, price FROM products");
if ($res) { while ($r = $res->fetch_assoc()) $productMap[intval($r['id'])] = $r; }
$courseMap = [];
$res = $conn->query("SELECT id, title, slug, price FROM courses");
if ($res) { while ($r = $res->fetch_assoc()) $courseMap[intval($r['id'])] = $r; }

// Fetch all orders for this customer
$stmt = $conn->prepare("SELECT id, order_number, customer_name, customer_email, customer_phone, shipping_address, total_now, total_full, payment_method, status, items_json, schedule_json, payments_made_json, order_date, updated_at FROM orders WHERE customer_email = ? ORDER BY order_date DESC");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$ordersResult = $stmt->get_result();
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();

// --- Render page ---
$page_title   = 'Order History';
$current_page = 'account';
$sidebar_page = 'orders';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="dashboard-page-header">
            <div>
                <h1>Order History</h1>
                <p>View and track all your orders.</p>
            </div>
        </div>

        <!-- Order Tracking Form -->
        <div class="dashboard-form-card" style="margin-bottom: var(--spacing-2xl);">
            <div class="dashboard-form-card-header">
                <h3>Track an Order</h3>
            </div>
            <div class="dashboard-form-card-body">
                <form id="trackOrderForm" class="form-row" style="align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="trackOrderNumber">Order Number</label>
                        <input type="text" id="trackOrderNumber" placeholder="e.g. ORD-1234567890" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="trackIdentifier">Email or Phone</label>
                        <input type="text" id="trackIdentifier" value="<?php echo safe_output($user['email']); ?>" required>
                    </div>
                    <div style="padding-bottom: 2px;">
                        <button type="submit" class="btn btn-primary">Track Order</button>
                    </div>
                </form>
                <div id="trackResult" style="margin-top: var(--spacing-lg); display: none;"></div>
            </div>
        </div>

        <!-- Orders list -->
        <?php if (empty($orders)): ?>
            <div class="data-section">
                <div style="padding: var(--spacing-3xl); text-align: center; color: var(--color-text-muted);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: var(--spacing-base); opacity: 0.5;"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <p style="font-size: var(--font-md); margin-bottom: var(--spacing-base);">No orders found.</p>
                    <a href="<?php echo SITE_URL; ?>/shop" class="btn btn-primary">Start Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order):
                $items = json_decode($order['items_json'] ?? '[]', true) ?: [];
                $schedule = json_decode($order['schedule_json'] ?? '[]', true) ?: [];
                $paymentsMade = json_decode($order['payments_made_json'] ?? '[]', true) ?: [];

                $methods = [
                    'credit'            => 'Credit Plan',
                    'cash_on_delivery'  => 'Cash on Delivery',
                    'mobile_money'      => 'Mobile Money',
                    'bank_transfer'     => 'Bank Transfer',
                ];
                $methodLabel = $methods[$order['payment_method']] ?? ucwords(str_replace('_', ' ', $order['payment_method']));

                // Calculate remaining balance for credit orders
                $totalPaid = 0;
                foreach ($paymentsMade as $payment) {
                    $totalPaid += floatval($payment['amount'] ?? 0);
                }
                $remainingBalance = max(0, floatval($order['total_full']) - $totalPaid);
            ?>
            <div class="order-card">
                <div class="order-card-header">
                    <div>
                        <strong><?php echo safe_output($order['order_number']); ?></strong>
                        <span style="color: var(--color-text-muted); margin-left: var(--spacing-sm);">
                            <?php echo date('M j, Y \a\t g:i A', strtotime($order['order_date'])); ?>
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                        <span class="status-badge <?php echo safe_output($order['status']); ?>">
                            <?php echo ucfirst(safe_output($order['status'])); ?>
                        </span>
                        <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                        <button class="btn btn-sm" style="background: var(--color-danger, #dc3545); color: white; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="cancelOrder('<?php echo safe_output($order['order_number']); ?>', this)">Cancel</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-card-body">
                    <!-- Order items -->
                    <?php if (!empty($items)): ?>
                    <div class="table-responsive">
                    <table class="table" style="margin-bottom: var(--spacing-lg);">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item):
                                $itemType = ($item['type'] ?? 'product') === 'course' ? 'Course' : 'Product';
                                // Resolve name/price from DB if not in JSON
                                $itemName = $item['name'] ?? $item['title'] ?? null;
                                $itemPrice = $item['price'] ?? 0;
                                $itemSlug = '';
                                if (!$itemName) {
                                    if ($itemType === 'Course') {
                                        $cid = intval($item['itemId'] ?? $item['productId'] ?? 0);
                                        // Also try stripping 'course-' prefix
                                        if (!isset($courseMap[$cid]) && strpos($item['itemId'] ?? '', 'course-') === 0) {
                                            $cid = intval(str_replace('course-', '', $item['itemId']));
                                        }
                                        if (isset($courseMap[$cid])) { $itemName = $courseMap[$cid]['title']; $itemPrice = $itemPrice ?: $courseMap[$cid]['price']; $itemSlug = $courseMap[$cid]['slug'] ?? ''; }
                                    } else {
                                        $pid = intval($item['productId'] ?? 0);
                                        if (isset($productMap[$pid])) { $itemName = $productMap[$pid]['title']; $itemPrice = $itemPrice ?: $productMap[$pid]['price']; $itemSlug = $productMap[$pid]['slug'] ?? ''; }
                                    }
                                }
                                $itemName = $itemName ?: 'Item #' . ($item['productId'] ?? $item['itemId'] ?? '?');
                                // Build link
                                $itemLink = '';
                                if ($itemSlug) {
                                    $itemLink = SITE_URL . ($itemType === 'Course' ? '/course/' : '/product/') . $itemSlug;
                                } elseif ($itemType === 'Course') {
                                    $cid2 = intval($item['itemId'] ?? $item['productId'] ?? 0);
                                    if (strpos($item['itemId'] ?? '', 'course-') === 0) $cid2 = intval(str_replace('course-', '', $item['itemId']));
                                    if (isset($courseMap[$cid2]) && !empty($courseMap[$cid2]['slug'])) $itemLink = SITE_URL . '/course/' . $courseMap[$cid2]['slug'];
                                } else {
                                    $pid2 = intval($item['productId'] ?? 0);
                                    if (isset($productMap[$pid2]) && !empty($productMap[$pid2]['slug'])) $itemLink = SITE_URL . '/product/' . $productMap[$pid2]['slug'];
                                }
                            ?>
                            <tr>
                                <td><?php if ($itemLink): ?><a href="<?php echo $itemLink; ?>" style="color:#2563eb;text-decoration:none;font-weight:500;"><?php echo safe_output($itemName); ?></a><?php else: echo safe_output($itemName); endif; ?></td>
                                <td><span style="font-size: 12px; padding: 2px 8px; border-radius: 10px; background: <?php echo $itemType === 'Course' ? '#dbeafe' : '#f0fdf4'; ?>; color: <?php echo $itemType === 'Course' ? '#1d4ed8' : '#166534'; ?>;"><?php echo $itemType; ?></span></td>
                                <td><?php echo intval($item['quantity'] ?? $item['qty'] ?? 1); ?></td>
                                <td>UGX <?php echo number_format(floatval($itemPrice), 0); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <?php endif; ?>

                    <!-- Payment details -->
                    <div class="form-row" style="gap: var(--spacing-2xl);">
                        <div>
                            <p style="font-size: var(--font-sm); color: var(--color-text-muted); margin-bottom: var(--spacing-xs);">Payment Method</p>
                            <p style="font-weight: var(--font-weight-semibold);"><?php echo safe_output($methodLabel); ?></p>
                        </div>
                        <div>
                            <p style="font-size: var(--font-sm); color: var(--color-text-muted); margin-bottom: var(--spacing-xs);">
                                <?php echo $order['status'] === 'completed' ? 'Amount Paid' : 'Amount Paying Now'; ?>
                            </p>
                            <p style="font-weight: var(--font-weight-semibold);">UGX <?php echo number_format($order['total_now'], 0); ?></p>
                        </div>
                        <div>
                            <p style="font-size: var(--font-sm); color: var(--color-text-muted); margin-bottom: var(--spacing-xs);">Full Total</p>
                            <p style="font-weight: var(--font-weight-semibold);">UGX <?php echo number_format($order['total_full'] ?: $order['total_now'], 0); ?></p>
                        </div>
                    </div>

                    <?php if ($order['payment_method'] === 'credit' && $order['status'] !== 'cancelled'): ?>
                    <div style="margin-top: var(--spacing-lg); padding: var(--spacing-base); background: var(--color-warning-light); border-radius: var(--radius-md); border: 1px solid var(--color-warning);">
                        <div class="form-row" style="gap: var(--spacing-2xl);">
                            <div>
                                <p style="font-size: var(--font-sm); color: var(--color-warning-dark); margin-bottom: var(--spacing-xs);">Amount Paid</p>
                                <p style="font-weight: var(--font-weight-bold); color: var(--color-warning-dark);">UGX <?php echo number_format($totalPaid, 0); ?></p>
                            </div>
                            <div>
                                <p style="font-size: var(--font-sm); color: var(--color-warning-dark); margin-bottom: var(--spacing-xs);">Remaining Balance</p>
                                <p style="font-weight: var(--font-weight-bold); color: var(--color-warning-dark);">UGX <?php echo number_format($remainingBalance, 0); ?></p>
                            </div>
                        </div>
                        <?php if ($remainingBalance > 0): ?>
                        <a href="<?php echo SITE_URL; ?>/payment-portal?order=<?php echo urlencode($order['order_number']); ?>" class="btn btn-warning btn-sm" style="margin-top: var(--spacing-base);">
                            Make Payment
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Credit schedule link -->
                    <?php if (!empty($schedule)): ?>
                    <div style="margin-top: var(--spacing-lg);">
                        <a href="<?php echo SITE_URL; ?>/account/credits" style="display:inline-flex;align-items:center;gap:6px;color:var(--color-primary);font-weight:600;font-size:var(--font-sm);text-decoration:none;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            View Payment Schedule in My Credits
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Cancel order
function cancelOrder(orderNumber, btn) {
    showAppConfirm('Cancel Order', 'Are you sure you want to cancel order ' + orderNumber + '?', 'Yes, Cancel', function() {
        btn.disabled = true;
        btn.textContent = 'Cancelling...';

        var fd = new FormData();
        fd.append('action', 'customer_cancel');
        fd.append('order_number', orderNumber);
        fd.append('customer_email', '<?php echo safe_output($user['email']); ?>');

        fetch('<?php echo SITE_URL; ?>/api/orders.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    showToast(data.message || 'Failed to cancel order', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Cancel';
                }
            })
            .catch(function() {
                showToast('Failed to cancel order. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = 'Cancel';
            });
    }, true);
}

// Order tracking
document.getElementById('trackOrderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var orderNumber = document.getElementById('trackOrderNumber').value.trim();
    var identifier = document.getElementById('trackIdentifier').value.trim();
    var resultDiv = document.getElementById('trackResult');

    if (!orderNumber || !identifier) {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="auth-alert auth-alert-error">Please fill in both fields.</div>';
        return;
    }

    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p style="color: var(--color-text-muted);">Looking up order...</p>';

    fetch('<?php echo SITE_URL; ?>/api/orders.php?action=track&order_number=' + encodeURIComponent(orderNumber) + '&identifier=' + encodeURIComponent(identifier))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var o = data.data;
                var statusClass = o.status || 'pending';
                resultDiv.innerHTML =
                    '<div style="padding: var(--spacing-lg); border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg-white);">' +
                    '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-base);">' +
                    '<strong>' + (o.order_number || '') + '</strong>' +
                    '<span class="status-badge ' + statusClass + '">' + (statusClass.charAt(0).toUpperCase() + statusClass.slice(1)) + '</span>' +
                    '</div>' +
                    '<p style="font-size: var(--font-sm); color: var(--color-text-light);">Placed on ' + (o.order_date || '') + '</p>' +
                    '<p style="margin-top: var(--spacing-sm);">Total: <strong>UGX ' + Number(o.total_full || o.total_now || 0).toLocaleString() + '</strong></p>' +
                    '</div>';
            } else {
                resultDiv.innerHTML = '<div class="auth-alert auth-alert-error">' + (data.message || 'Order not found.') + '</div>';
            }
        })
        .catch(function() {
            resultDiv.innerHTML = '<div class="auth-alert auth-alert-error">Failed to look up order. Please try again.</div>';
        });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
