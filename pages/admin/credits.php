<?php
$admin_page = 'credits';
$page_title = 'Credit Management';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';

$conn = getDbConnection();

// Build lookups
$productMap = [];
$res = $conn->query("SELECT id, title, slug, price FROM products");
if ($res) { while ($r = $res->fetch_assoc()) $productMap[intval($r['id'])] = $r; }
$courseMap = [];
$res = $conn->query("SELECT id, title, slug, price FROM courses");
if ($res) { while ($r = $res->fetch_assoc()) $courseMap[intval($r['id'])] = $r; }

// Fetch ALL credit orders (completed = active credit, pending/processing = pending orders)
$result = $conn->query("SELECT o.*, c.name as linked_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.order_date DESC");

$activeCredits = [];
$pendingOrders = [];
$completedCredits = [];
$allDues = []; // All upcoming and overdue payments
$totalOutstanding = 0;
$totalCollected = 0;
$today = date('Y-m-d');

while ($order = $result->fetch_assoc()) {
    $items = json_decode($order['items_json'] ?? '[]', true) ?: [];
    $schedule = json_decode($order['schedule_json'] ?? '[]', true) ?: [];
    $paymentsMade = json_decode($order['payments_made_json'] ?? '[]', true) ?: [];

    $hasCredit = false;
    if (!empty($schedule)) $hasCredit = true;
    foreach ($items as &$item) {
        if (isset($item['paymentPlan']) && ($item['paymentPlan']['type'] ?? '') === 'credit') $hasCredit = true;
        if (empty($item['title']) && empty($item['name'])) {
            if (($item['type'] ?? '') === 'course') {
                $cid = intval($item['itemId'] ?? $item['productId'] ?? 0);
                if (strpos($item['itemId'] ?? '', 'course-') === 0) $cid = intval(str_replace('course-', '', $item['itemId']));
                if (isset($courseMap[$cid])) { $item['title'] = $courseMap[$cid]['title']; $item['price'] = $item['price'] ?? $courseMap[$cid]['price']; }
            } else {
                $pid = intval($item['productId'] ?? 0);
                if (isset($productMap[$pid])) { $item['title'] = $productMap[$pid]['title']; $item['price'] = $item['price'] ?? $productMap[$pid]['price']; }
            }
        }
    }
    unset($item);
    if (!$hasCredit) continue;

    $paidAmount = 0;
    foreach ($paymentsMade as $p) $paidAmount += floatval($p['amount'] ?? 0);
    $depositPaid = floatval($order['total_now']);
    $fullTotal = floatval($order['total_full']);
    $remaining = max(0, $fullTotal - $depositPaid - $paidAmount);
    $progress = $fullTotal > 0 ? min(100, round((($depositPaid + $paidAmount) / $fullTotal) * 100)) : 0;

    $order['_items'] = $items;
    $order['_schedule'] = $schedule;
    $order['_paid'] = $depositPaid + $paidAmount;
    $order['_remaining'] = $remaining;
    $order['_progress'] = $progress;

    // Categorize
    if ($order['status'] === 'pending' || $order['status'] === 'processing') {
        $pendingOrders[] = $order;
    } elseif ($remaining <= 0) {
        $completedCredits[] = $order;
        $totalCollected += $depositPaid + $paidAmount;
    } else {
        $activeCredits[] = $order;
        $totalOutstanding += $remaining;
        $totalCollected += $depositPaid + $paidAmount;

        // Collect individual dues for the upcoming/overdue table
        $orderDate = strtotime($order['order_date']);
        foreach ($schedule as $idx => $entry) {
            $dueDate = $entry['date'] ?? $entry['due_date'] ?? '';
            if (empty($dueDate) && $orderDate) {
                $dueDate = date('Y-m-d', strtotime('+' . ($idx + 1) . ' months', $orderDate));
            }
            $isPaid = (isset($entry['status']) && $entry['status'] === 'paid');
            if (!$isPaid && !empty($dueDate)) {
                $allDues[] = [
                    'order_id' => $order['id'],
                    'order_number' => $order['order_number'],
                    'customer_name' => $order['customer_name'],
                    'customer_email' => $order['customer_email'],
                    'due_date' => $dueDate,
                    'amount' => floatval($entry['amount'] ?? $entry['payment'] ?? 0),
                    'payment_index' => $idx,
                    'is_overdue' => $dueDate < $today,
                ];
            }
        }
    }
}
$conn->close();

// Sort dues by date
usort($allDues, function($a, $b) { return strcmp($a['due_date'], $b['due_date']); });

$overdueDues = array_filter($allDues, function($d) { return $d['is_overdue']; });
$upcomingDues = array_filter($allDues, function($d) { return !$d['is_overdue']; });
?>
    <div class="admin-content-inner">
      <div class="admin-page-header">
        <h1>Credit Management</h1>
        <button class="admin-btn admin-btn-primary" onclick="openCreateCreditModal()" style="display:inline-flex;align-items:center;gap:6px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create Credit Plan</button>
      </div>

      <!-- Stats -->
      <div class="admin-stats-grid" style="margin-bottom:24px;">
        <div class="admin-stat-card border-warning">
          <div class="stat-info"><span class="stat-label">Pending Orders</span><span class="stat-value"><?php echo count($pendingOrders); ?></span></div>
        </div>
        <div class="admin-stat-card border-primary">
          <div class="stat-info"><span class="stat-label">Ongoing</span><span class="stat-value"><?php echo count($activeCredits); ?></span></div>
        </div>
        <div class="admin-stat-card border-danger">
          <div class="stat-info"><span class="stat-label">Outstanding</span><span class="stat-value" style="font-size:18px;">UGX <?php echo number_format($totalOutstanding, 0); ?></span></div>
        </div>
        <div class="admin-stat-card border-success">
          <div class="stat-info"><span class="stat-label">Collected</span><span class="stat-value" style="font-size:18px;">UGX <?php echo number_format($totalCollected, 0); ?></span></div>
        </div>
      </div>

      <!-- Tabs -->
      <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:20px;overflow-x:auto;">
        <button class="credit-tab active" data-tab="active" style="padding:10px 20px;border:none;background:none;font-weight:600;cursor:pointer;border-bottom:2px solid #2563eb;margin-bottom:-2px;color:#2563eb;white-space:nowrap;">Ongoing (<?php echo count($activeCredits); ?>)</button>
        <button class="credit-tab" data-tab="upcoming" style="padding:10px 20px;border:none;background:none;font-weight:600;cursor:pointer;margin-bottom:-2px;color:#64748b;white-space:nowrap;">Upcoming Dues (<?php echo count($upcomingDues); ?>)</button>
        <button class="credit-tab" data-tab="overdue" style="padding:10px 20px;border:none;background:none;font-weight:600;cursor:pointer;margin-bottom:-2px;color:#64748b;white-space:nowrap;">Overdue (<?php echo count($overdueDues); ?>)</button>
        <button class="credit-tab" data-tab="pending" style="padding:10px 20px;border:none;background:none;font-weight:600;cursor:pointer;margin-bottom:-2px;color:#64748b;white-space:nowrap;">Pending Orders (<?php echo count($pendingOrders); ?>)</button>
        <button class="credit-tab" data-tab="completed" style="padding:10px 20px;border:none;background:none;font-weight:600;cursor:pointer;margin-bottom:-2px;color:#64748b;white-space:nowrap;">Completed (<?php echo count($completedCredits); ?>)</button>
      </div>

      <!-- TAB: Upcoming Dues -->
      <div class="credit-tab-content" id="tab-upcoming" style="display:none;">
        <div style="display:flex;gap:8px;margin-bottom:16px;">
          <button class="admin-btn admin-btn-sm admin-btn-primary filter-days active" data-days="all">All</button>
          <button class="admin-btn admin-btn-sm filter-days" data-days="7">7 Days</button>
          <button class="admin-btn admin-btn-sm filter-days" data-days="14">14 Days</button>
          <button class="admin-btn admin-btn-sm filter-days" data-days="31">This Month</button>
        </div>
        <div class="admin-card"><div class="admin-table-wrapper">
          <table class="admin-table" id="upcomingTable">
            <thead><tr><th>Due Date</th><th>Order #</th><th>Customer</th><th>Amount</th><th>Days Left</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($upcomingDues)): ?>
              <tr><td colspan="6" style="text-align:center;padding:20px;color:#9ca3af;">No upcoming payments</td></tr>
              <?php else: foreach ($upcomingDues as $d):
                $daysLeft = (int)((strtotime($d['due_date']) - strtotime($today)) / 86400);
              ?>
              <tr data-days="<?php echo $daysLeft; ?>">
                <td><strong><?php $ts=strtotime($d['due_date']); $mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; echo $mn[(int)date('n',$ts)-1].' '.date('j',$ts).', '.date('Y',$ts); ?></strong></td>
                <td style="color:#2563eb;"><?php echo safe_output($d['order_number']); ?></td>
                <td><?php echo safe_output($d['customer_name']); ?><br><small style="color:#64748b;"><?php echo safe_output($d['customer_email']); ?></small></td>
                <td style="font-weight:600;">UGX <?php echo number_format($d['amount'], 0); ?></td>
                <td><span style="background:<?php echo $daysLeft <= 3 ? '#fef3c7' : '#dbeafe'; ?>;color:<?php echo $daysLeft <= 3 ? '#92400e' : '#1e40af'; ?>;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;"><?php echo $daysLeft; ?> days</span></td>
                <td><button class="admin-btn admin-btn-sm admin-btn-success" onclick="recordPayment(<?php echo $d['order_id']; ?>, '<?php echo safe_output($d['order_number']); ?>', <?php echo $d['amount']; ?>)" style="display:inline-flex;align-items:center;gap:4px;" title="Record Payment"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span class="btn-label">Pay</span></button></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div></div>
      </div>

      <!-- TAB: Overdue -->
      <div class="credit-tab-content" id="tab-overdue" style="display:none;">
        <div class="admin-card"><div class="admin-table-wrapper">
          <table class="admin-table">
            <thead><tr><th>Due Date</th><th>Order #</th><th>Customer</th><th>Amount</th><th>Days Overdue</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($overdueDues)): ?>
              <tr><td colspan="6" style="text-align:center;padding:20px;color:#9ca3af;">No overdue payments</td></tr>
              <?php else: foreach ($overdueDues as $d):
                $daysOverdue = (int)((strtotime($today) - strtotime($d['due_date'])) / 86400);
              ?>
              <tr>
                <td><strong style="color:#dc2626;"><?php $ts=strtotime($d['due_date']); $mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; echo $mn[(int)date('n',$ts)-1].' '.date('j',$ts).', '.date('Y',$ts); ?></strong></td>
                <td style="color:#2563eb;"><?php echo safe_output($d['order_number']); ?></td>
                <td><?php echo safe_output($d['customer_name']); ?><br><small style="color:#64748b;"><?php echo safe_output($d['customer_email']); ?></small></td>
                <td style="font-weight:600;color:#dc2626;">UGX <?php echo number_format($d['amount'], 0); ?></td>
                <td><span style="background:#fef2f2;color:#991b1b;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;"><?php echo $daysOverdue; ?> days late</span></td>
                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <button class="admin-btn admin-btn-sm admin-btn-success" onclick="recordPayment(<?php echo $d['order_id']; ?>, '<?php echo safe_output($d['order_number']); ?>', <?php echo $d['amount']; ?>)" style="display:inline-flex;align-items:center;gap:4px;" title="Record Payment"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span class="btn-label">Pay</span></button>
                  <button class="admin-btn admin-btn-sm admin-btn-info" onclick="rescheduleOverdue(<?php echo $d['order_id']; ?>, '<?php echo safe_output($d['order_number']); ?>', <?php echo $d['payment_index']; ?>)" style="display:inline-flex;align-items:center;gap:4px;" title="Move to next month"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span class="btn-label">Reschedule</span></button>
                  <button class="admin-btn admin-btn-sm admin-btn-warning" onclick="rollOverdue(<?php echo $d['order_id']; ?>, '<?php echo safe_output($d['order_number']); ?>', <?php echo $d['payment_index']; ?>)" style="display:inline-flex;align-items:center;gap:4px;" title="Add to next installment"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg><span class="btn-label">Roll Up</span></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div></div>
      </div>

      <!-- TAB: Ongoing -->
      <div class="credit-tab-content" id="tab-active">
        <div class="admin-card"><div class="admin-table-wrapper">
          <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Paid</th><th>Remaining</th><th>Progress</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($activeCredits)): ?>
              <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No active credit plans</td></tr>
              <?php else: foreach ($activeCredits as $c): ?>
              <tr>
                <td><strong style="color:#2563eb;"><?php echo safe_output($c['order_number']); ?></strong><br><small style="color:#64748b;"><?php echo date('M j, Y', strtotime($c['order_date'])); ?></small></td>
                <td><?php echo safe_output($c['customer_name']); ?></td>
                <td style="font-size:13px;"><?php foreach ($c['_items'] as $item) { if (($item['paymentPlan']['type'] ?? '') === 'credit') echo safe_output($item['title'] ?? 'Item') . '<br>'; } ?></td>
                <td>UGX <?php echo number_format($c['total_full'], 0); ?></td>
                <td style="color:#16a34a;font-weight:600;">UGX <?php echo number_format($c['_paid'], 0); ?></td>
                <td style="color:#dc2626;font-weight:600;">UGX <?php echo number_format($c['_remaining'], 0); ?></td>
                <td>
                  <div style="background:#e2e8f0;border-radius:10px;height:8px;width:80px;overflow:hidden;display:inline-block;vertical-align:middle;">
                    <div style="background:#16a34a;height:100%;width:<?php echo $c['_progress']; ?>%;"></div>
                  </div>
                  <span style="font-size:11px;color:#64748b;"><?php echo $c['_progress']; ?>%</span>
                </td>
                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap;">
                  <button class="admin-btn admin-btn-sm admin-btn-success" onclick="recordPayment(<?php echo $c['id']; ?>, '<?php echo safe_output($c['order_number']); ?>', <?php echo $c['_remaining']; ?>)" style="display:inline-flex;align-items:center;gap:4px;" title="Record Payment"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span class="btn-label">Pay</span></button>
                  <button class="admin-btn admin-btn-sm admin-btn-info" onclick="editDueDates(<?php echo $c['id']; ?>, '<?php echo safe_output($c['order_number']); ?>')" style="display:inline-flex;align-items:center;gap:4px;" title="Edit Due Dates"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><span class="btn-label">Dates</span></button>
                  <button class="admin-btn admin-btn-sm admin-btn-warning" onclick="extendCredit(<?php echo $c['id']; ?>, '<?php echo safe_output($c['order_number']); ?>', <?php echo $c['_remaining']; ?>, <?php echo $c['_paid']; ?>)" style="display:inline-flex;align-items:center;gap:4px;" title="Extend Credit Period"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg><span class="btn-label">Extend</span></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div></div>
      </div>

      <!-- TAB: Pending Orders -->
      <div class="credit-tab-content" id="tab-pending" style="display:none;">
        <div class="admin-card"><div class="admin-table-wrapper">
          <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Items</th><th>Deposit</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($pendingOrders)): ?>
              <tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af;">No pending credit orders</td></tr>
              <?php else: foreach ($pendingOrders as $c): ?>
              <tr>
                <td><strong style="color:#2563eb;"><?php echo safe_output($c['order_number']); ?></strong><br><small style="color:#64748b;"><?php echo date('M j, Y', strtotime($c['order_date'])); ?></small></td>
                <td><?php echo safe_output($c['customer_name']); ?><br><small style="color:#64748b;"><?php echo safe_output($c['customer_email']); ?></small></td>
                <td style="font-size:13px;"><?php foreach ($c['_items'] as $item) { if (($item['paymentPlan']['type'] ?? '') === 'credit') echo safe_output($item['title'] ?? 'Item') . '<br>'; } ?></td>
                <td>UGX <?php echo number_format($c['total_now'], 0); ?></td>
                <td>UGX <?php echo number_format($c['total_full'], 0); ?></td>
                <td><span style="background:#fef3c7;color:#92400e;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><?php echo ucfirst($c['status']); ?></span></td>
                <td><button class="admin-btn admin-btn-sm admin-btn-primary" onclick="approveCredit(<?php echo $c['id']; ?>, '<?php echo safe_output($c['order_number']); ?>')" style="display:inline-flex;align-items:center;gap:4px;" title="Approve & Activate Credit"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span class="btn-label">Approve</span></button></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div></div>
      </div>

      <!-- TAB: Completed -->
      <div class="credit-tab-content" id="tab-completed" style="display:none;">
        <div class="admin-card"><div class="admin-table-wrapper">
          <table class="admin-table">
            <thead><tr><th>Order #</th><th>Customer</th><th>Total Paid</th><th>Date</th></tr></thead>
            <tbody>
              <?php if (empty($completedCredits)): ?>
              <tr><td colspan="4" style="text-align:center;padding:20px;color:#9ca3af;">No completed credits</td></tr>
              <?php else: foreach ($completedCredits as $c): ?>
              <tr>
                <td><strong style="color:#166534;"><?php echo safe_output($c['order_number']); ?></strong></td>
                <td><?php echo safe_output($c['customer_name']); ?></td>
                <td style="color:#166534;font-weight:600;">UGX <?php echo number_format($c['_paid'], 0); ?></td>
                <td><?php echo date('M j, Y', strtotime($c['order_date'])); ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div></div>
      </div>
    </div>

<script>
// Confirmation modal utility
function showConfirmModal(title, message, confirmText, onConfirm) {
    var body =
        '<div style="text-align:center;padding:10px 0;">' +
            '<div style="width:56px;height:56px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">' +
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>' +
            '</div>' +
            '<p style="color:#374151;font-size:15px;margin:0 0 20px;line-height:1.5;">' + message + '</p>' +
            '<div style="display:flex;gap:10px;justify-content:center;">' +
                '<button onclick="removeModal(\'confirmModal\')" style="padding:10px 24px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;font-size:14px;">Cancel</button>' +
                '<button id="confirmActionBtn" style="padding:10px 24px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;">' + (confirmText || 'Confirm') + '</button>' +
            '</div>' +
        '</div>';
    createModal('confirmModal', title, body, '420px');
    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        removeModal('confirmModal');
        onConfirm();
    });
}

// Tab switching
document.querySelectorAll('.credit-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.credit-tab').forEach(function(t) {
            t.style.borderBottom = 'none';
            t.style.color = '#64748b';
            t.classList.remove('active');
        });
        this.style.borderBottom = '2px solid #2563eb';
        this.style.color = '#2563eb';
        this.classList.add('active');
        document.querySelectorAll('.credit-tab-content').forEach(function(c) { c.style.display = 'none'; });
        document.getElementById('tab-' + this.getAttribute('data-tab')).style.display = 'block';
    });
});

// Day filter for upcoming dues
document.querySelectorAll('.filter-days').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-days').forEach(function(b) { b.classList.remove('admin-btn-primary'); });
        this.classList.add('admin-btn-primary');
        var days = this.getAttribute('data-days');
        document.querySelectorAll('#upcomingTable tbody tr').forEach(function(row) {
            if (days === 'all') { row.style.display = ''; return; }
            var rowDays = parseInt(row.getAttribute('data-days'));
            row.style.display = (rowDays <= parseInt(days)) ? '' : 'none';
        });
    });
});

function approveCredit(orderId, orderNumber) {
    showConfirmModal(
        'Approve Credit Plan',
        'Approve and activate credit for <strong>' + orderNumber + '</strong>?<br><br>This will mark the order as completed and start the credit payment schedule from today.',
        'Approve & Activate',
        function() {
            var fd = new FormData();
            fd.append('action', 'update_status');
            fd.append('id', orderId);
            fd.append('status', 'completed');
            fetch(API.orders, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        showToast('Credit activated for ' + orderNumber);
                        setTimeout(function() { location.reload(); }, 1000);
                    } else {
                        showToast(data.message || 'Failed', 'error');
                    }
                })
                .catch(function(err) { showToast('Error: ' + err.message, 'error'); });
        }
    );
}

async function editDueDates(orderId, orderNumber) {
    try {
        var res = await fetch(API.orders + '?action=get&id=' + orderId);
        var json = await res.json();
        if (!json.success) { showToast('Order not found', 'error'); return; }
        var schedule = [];
        try { schedule = JSON.parse(json.data.schedule_json || '[]'); } catch(e) {}

        var orderDate = json.data.order_date;
        var rows = schedule.map(function(entry, idx) {
            var dueDate = entry.date || entry.due_date || '';
            if (!dueDate && orderDate) {
                var d = new Date(orderDate);
                d.setMonth(d.getMonth() + idx + 1);
                dueDate = d.toISOString().split('T')[0];
            }
            var isPaid = (entry.status === 'paid');
            return '<tr>' +
                '<td style="padding:8px;">' + (idx + 1) + '</td>' +
                '<td style="padding:8px;"><input type="date" name="date_' + idx + '" value="' + dueDate + '" ' + (isPaid ? 'disabled' : '') + ' style="padding:6px;border:1px solid #d1d5db;border-radius:4px;"></td>' +
                '<td style="padding:8px;">UGX ' + formatPrice(entry.amount || entry.payment || 0) + '</td>' +
                '<td style="padding:8px;">' + (isPaid ? '<span style="color:#16a34a;font-weight:600;">Paid</span>' : '<span style="color:#92400e;">Pending</span>') + '</td>' +
            '</tr>';
        }).join('');

        var body =
            '<form id="editDatesForm">' +
            '<input type="hidden" name="order_id" value="' + orderId + '" />' +
            '<table style="width:100%;border-collapse:collapse;">' +
                '<thead><tr style="background:#f9fafb;"><th style="padding:8px;text-align:left;">#</th><th style="padding:8px;text-align:left;">Due Date</th><th style="padding:8px;text-align:left;">Amount</th><th style="padding:8px;text-align:left;">Status</th></tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
            '</table>' +
            '<div style="text-align:right;margin-top:16px;">' +
                '<button type="button" onclick="removeModal(\'datesModal\')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:8px;">Cancel</button>' +
                '<button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Save Dates</button>' +
            '</div></form>';

        createModal('datesModal', 'Edit Due Dates - ' + orderNumber, body);

        document.getElementById('editDatesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            // Update schedule dates
            for (var i = 0; i < schedule.length; i++) {
                var input = this.querySelector('[name="date_' + i + '"]');
                if (input && !input.disabled) {
                    schedule[i].date = input.value;
                }
            }
            var fd = new FormData();
            fd.append('action', 'update_schedule');
            fd.append('id', orderId);
            fd.append('schedule_json', JSON.stringify(schedule));
            var res2 = await fetch(API.orders, { method: 'POST', body: fd });
            var json2 = await res2.json();
            if (json2.success) {
                showToast('Due dates updated');
                removeModal('datesModal');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast(json2.message || 'Failed', 'error');
            }
        });
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

async function recordPayment(orderId, orderNumber, totalRemaining) {
    var inputStyle = 'width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;';
    var today = new Date().toISOString().split('T')[0];

    // Fetch order data
    try {
        var res = await fetch(API.orders + '?action=get&id=' + orderId);
        var json = await res.json();
        if (!json.success) { showToast('Order not found', 'error'); return; }
        var order = json.data;
        var schedule = [];
        try { schedule = JSON.parse(order.schedule_json || '[]'); } catch(e) {}
        var payments = [];
        try { payments = JSON.parse(order.payments_made_json || '[]'); } catch(e) {}

        // Normalize schedule entries
        var orderDate = order.order_date;
        schedule = schedule.map(function(s, idx) {
            var dueDate = s.date || s.due_date || s.dueDate || '';
            if (!dueDate && orderDate) {
                var d = new Date(orderDate);
                d.setMonth(d.getMonth() + idx + 1);
                dueDate = d.toISOString().split('T')[0];
                s.date = dueDate;
            }
            s.amount = s.amount || s.payment || 0;
            s.status = s.status || (s.paid ? 'paid' : 'pending');
            s.paid_amount = s.paid_amount || 0; // track partial
            return s;
        });

        // Separate overdue, upcoming, and paid
        var unpaid = schedule.filter(function(s) { return s.status !== 'paid'; });
        var overdue = unpaid.filter(function(s) { return s.date < today; });
        var upcoming = unpaid.filter(function(s) { return s.date >= today; });

        // Build installment rows
        var installmentRows = '';
        unpaid.forEach(function(s, idx) {
            var isOverdue = s.date < today;
            var dueLabel = s.date ? new Date(s.date + 'T00:00:00').toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}) : 'No date';
            var remaining = s.amount - (s.paid_amount || 0);
            var statusTag = isOverdue
                ? '<span style="background:#fef2f2;color:#991b1b;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Overdue</span>'
                : '<span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Upcoming</span>';

            installmentRows += '<div class="installment-row" data-idx="' + schedule.indexOf(s) + '" data-amount="' + remaining + '" style="display:flex;align-items:center;gap:12px;padding:10px;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:8px;cursor:pointer;' + (isOverdue ? 'background:#fef2f2;' : '') + '" onclick="selectInstallment(this)">' +
                '<input type="radio" name="installment" value="' + schedule.indexOf(s) + '" data-full="' + remaining + '" style="flex-shrink:0;">' +
                '<div style="flex:1;">' +
                    '<div style="display:flex;justify-content:space-between;">' +
                        '<strong>' + dueLabel + '</strong>' +
                        statusTag +
                    '</div>' +
                    '<div style="font-size:13px;color:#64748b;margin-top:2px;">Due: UGX ' + Number(s.amount).toLocaleString() + (s.paid_amount > 0 ? ' (UGX ' + Number(s.paid_amount).toLocaleString() + ' already paid)' : '') + '</div>' +
                    '<div style="font-weight:600;color:#1e293b;">Remaining: UGX ' + Number(remaining).toLocaleString() + '</div>' +
                '</div>' +
            '</div>';
        });

        if (!installmentRows) {
            installmentRows = '<p style="color:#16a34a;font-weight:600;text-align:center;padding:20px;">All installments are paid!</p>';
        }

        var body =
            '<form id="recordPaymentForm">' +
                '<input type="hidden" name="order_id" value="' + orderId + '" />' +
                '<p style="margin-bottom:4px;font-size:14px;">Total remaining: <strong style="color:#dc2626;">UGX ' + Number(totalRemaining).toLocaleString() + '</strong></p>' +
                (overdue.length > 0 ? '<p style="color:#991b1b;font-size:13px;margin-bottom:12px;">' + overdue.length + ' overdue payment(s)</p>' : '') +
                '<div style="margin-bottom:12px;"><label style="display:block;font-weight:600;margin-bottom:8px;">Select Installment</label>' + installmentRows + '</div>' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">' +
                    '<div><label style="display:block;font-weight:600;margin-bottom:4px;">Amount (UGX)</label><input type="number" name="amount" id="payAmountInput" required min="1" style="' + inputStyle + '" placeholder="Select installment above" /></div>' +
                    '<div><label style="display:block;font-weight:600;margin-bottom:4px;">Payment Method</label><select name="method" style="' + inputStyle + '"><option value="mobile_money">Mobile Money</option><option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option></select></div>' +
                '</div>' +
                '<div style="margin-top:12px;"><label style="display:block;font-weight:600;margin-bottom:4px;">Notes</label><input type="text" name="notes" placeholder="e.g. MTN ref 12345" style="' + inputStyle + '" /></div>' +
                '<div style="display:flex;gap:8px;margin-top:8px;">' +
                    '<button type="button" class="admin-btn admin-btn-sm" onclick="document.getElementById(\'payAmountInput\').value=document.querySelector(\'input[name=installment]:checked\')?.getAttribute(\'data-full\')||\'\'">Full Amount</button>' +
                    '<button type="button" class="admin-btn admin-btn-sm" onclick="var f=Number(document.querySelector(\'input[name=installment]:checked\')?.getAttribute(\'data-full\')||0);document.getElementById(\'payAmountInput\').value=Math.ceil(f/2/500)*500">Half</button>' +
                '</div>' +
                '<div style="text-align:right;margin-top:16px;">' +
                    '<button type="button" onclick="removeModal(\'paymentModal\')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:8px;">Cancel</button>' +
                    '<button type="submit" style="padding:8px 20px;background:#059669;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Record Payment</button>' +
                '</div>' +
            '</form>';

        createModal('paymentModal', 'Record Payment - ' + orderNumber, body, '600px');

        // Click handler for installment rows
        window.selectInstallment = function(row) {
            row.querySelector('input[type=radio]').checked = true;
            var full = Number(row.getAttribute('data-amount'));
            document.getElementById('payAmountInput').value = full;
            // Highlight selected
            document.querySelectorAll('.installment-row').forEach(function(r) { r.style.borderColor = '#e2e8f0'; });
            row.style.borderColor = '#2563eb';
        };

        document.getElementById('recordPaymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var fd = new FormData(this);
            var amount = Number(fd.get('amount'));
            var selectedIdx = fd.get('installment');

            if (!selectedIdx && selectedIdx !== '0') {
                showToast('Please select an installment', 'error');
                return;
            }
            selectedIdx = Number(selectedIdx);

            // Update schedule — mark as paid or partially paid
            var entry = schedule[selectedIdx];
            var entryRemaining = entry.amount - (entry.paid_amount || 0);

            if (amount >= entryRemaining) {
                // Fully paid
                entry.status = 'paid';
                entry.paid_amount = entry.amount;
            } else {
                // Partial payment — reduce remaining but keep pending
                entry.paid_amount = (entry.paid_amount || 0) + amount;
            }

            // Record in payments_made
            payments.push({
                date: new Date().toISOString().split('T')[0],
                amount: amount,
                method: fd.get('method'),
                notes: fd.get('notes'),
                installment_index: selectedIdx
            });

            // Save both schedule and payments
            var updateFd = new FormData();
            updateFd.append('action', 'record_payment');
            updateFd.append('id', orderId);
            updateFd.append('payments_json', JSON.stringify(payments));
            updateFd.append('schedule_json', JSON.stringify(schedule));
            updateFd.append('amount', amount);

            try {
                var res2 = await fetch(API.orders, { method: 'POST', body: updateFd });
                var json2 = await res2.json();
                if (json2.success) {
                    showToast('Payment of UGX ' + amount.toLocaleString() + ' recorded!');
                    removeModal('paymentModal');
                    setTimeout(function() { location.reload(); }, 1000);
                } else { showToast(json2.message || 'Failed', 'error'); }
            } catch (err) { showToast('Error: ' + err.message, 'error'); }
        });
    } catch (err) {
        showToast('Error loading order: ' + err.message, 'error');
    }
}

async function extendCredit(orderId, orderNumber, remaining, alreadyPaid) {
    var inputStyle = 'width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;';

    // Fetch current order data
    try {
        var res = await fetch(API.orders + '?action=get&id=' + orderId);
        var json = await res.json();
        if (!json.success) { showToast('Order not found', 'error'); return; }
        var order = json.data;
        var schedule = [];
        try { schedule = JSON.parse(order.schedule_json || '[]'); } catch(e) {}

        var body =
            '<form id="extendCreditForm">' +
                '<p style="margin-bottom:12px;">Extend credit period for <strong>' + orderNumber + '</strong></p>' +
                '<div style="background:#f0f9ff;padding:12px;border-radius:6px;margin-bottom:16px;font-size:14px;">' +
                    '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Already Paid:</span><strong style="color:#16a34a;">UGX ' + Number(alreadyPaid).toLocaleString() + '</strong></div>' +
                    '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Remaining Balance:</span><strong style="color:#dc2626;">UGX ' + Number(remaining).toLocaleString() + '</strong></div>' +
                    '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Current Installments:</span><strong>' + schedule.length + '</strong></div>' +
                '</div>' +
                '<div style="margin-bottom:12px;"><label style="display:block;font-weight:600;margin-bottom:4px;">Extend by (additional months)</label>' +
                    '<select name="extra_months" id="extendMonthsSelect" style="' + inputStyle + '">' +
                        '<option value="1">1 Month</option><option value="2">2 Months</option><option value="3" selected>3 Months</option><option value="4">4 Months</option><option value="6">6 Months</option>' +
                    '</select></div>' +
                '<div id="extendPreview" style="background:#f8fafc;padding:12px;border-radius:6px;margin-bottom:12px;font-size:14px;"></div>' +
                '<div style="text-align:right;margin-top:16px;">' +
                    '<button type="button" onclick="removeModal(\'extendModal\')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:8px;">Cancel</button>' +
                    '<button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Extend Credit</button>' +
                '</div>' +
            '</form>';

        createModal('extendModal', 'Extend Credit Period - ' + orderNumber, body);

        var extMonths = document.getElementById('extendMonthsSelect');
        var extPreview = document.getElementById('extendPreview');

        function updateExtendPreview() {
            var extra = Number(extMonths.value);
            // Remaining balance split across new total months (existing unpaid + extra)
            var unpaidEntries = schedule.filter(function(s) { return s.status !== 'paid'; });
            var newTotalMonths = unpaidEntries.length + extra;
            var rawMonthly = remaining / newTotalMonths;
            var monthly = Math.ceil(rawMonthly / 500) * 500;
            extPreview.innerHTML =
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>New total installments:</span><strong>' + newTotalMonths + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>New monthly payment:</span><strong>~UGX ' + Number(monthly).toLocaleString() + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;color:#64748b;font-size:13px;"><span>Last payment may vary to cover exact balance</span></div>';
        }
        updateExtendPreview();
        extMonths.addEventListener('change', updateExtendPreview);

        document.getElementById('extendCreditForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var extra = Number(extMonths.value);

            // Rebuild schedule: keep paid entries, recalculate unpaid
            var paidEntries = schedule.filter(function(s) { return s.status === 'paid'; });
            var unpaidCount = schedule.filter(function(s) { return s.status !== 'paid'; }).length;
            var newTotalMonths = unpaidCount + extra;
            var rawMonthly = remaining / newTotalMonths;
            var monthly = Math.ceil(rawMonthly / 500) * 500;

            var newSchedule = paidEntries.slice(); // keep paid
            var lastPaidDate = paidEntries.length > 0 ? new Date(paidEntries[paidEntries.length - 1].date) : new Date();
            var bal = remaining;

            for (var i = 0; i < newTotalMonths; i++) {
                var due = new Date(lastPaidDate);
                due.setMonth(due.getMonth() + i + 1);
                var payment = (i === newTotalMonths - 1) ? bal : Math.min(monthly, bal);
                bal = Math.max(0, bal - payment);
                newSchedule.push({
                    date: due.toISOString().split('T')[0],
                    amount: payment,
                    status: 'pending'
                });
            }

            var fd = new FormData();
            fd.append('action', 'update_schedule');
            fd.append('id', orderId);
            fd.append('schedule_json', JSON.stringify(newSchedule));

            try {
                var res2 = await fetch(API.orders, { method: 'POST', body: fd });
                var json2 = await res2.json();
                if (json2.success) {
                    showToast('Credit extended by ' + extra + ' months');
                    removeModal('extendModal');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showToast(json2.message || 'Failed', 'error');
                }
            } catch (err) {
                showToast('Error: ' + err.message, 'error');
            }
        });
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
}

async function openCreateCreditModal() {
    var inputStyle = 'width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;';
    var customerOpts = '<option value="">-- Select Customer --</option>';
    var productOpts = '<option value="">-- Select Product/Course --</option>';
    try {
        var custRes = await fetch(API.customers + '?action=list');
        var custJson = await custRes.json();
        if (custJson.success && custJson.data) {
            customerOpts += custJson.data.map(function(c) {
                return '<option value="' + c.id + '" data-name="' + escapeHtml(c.name) + '" data-email="' + escapeHtml(c.email) + '" data-phone="' + escapeHtml(c.phone || '') + '">' + escapeHtml(c.name) + ' (' + escapeHtml(c.email) + ')</option>';
            }).join('');
        }
        var prodRes = await fetch(API.products + '?action=list');
        var prodJson = await prodRes.json();
        if (prodJson.success && prodJson.data) {
            prodJson.data.filter(function(p) { return p.credit_available == 1 && Number(p.default_apr || 0) > 0; }).forEach(function(p) {
                productOpts += '<option value="product-' + p.id + '" data-price="' + p.price + '" data-apr="' + (p.default_apr || 0) + '" data-terms="' + (p.credit_terms_months || '3,6') + '" data-title="' + escapeHtml(p.title) + '">Product: ' + escapeHtml(p.title) + ' (UGX ' + formatPrice(p.price) + ')</option>';
            });
        }
        var courseRes = await fetch(API.courses + '?action=list');
        var courseJson = await courseRes.json();
        if (courseJson.success && courseJson.data) {
            courseJson.data.filter(function(c) { return c.credit_available == 1 && Number(c.default_apr || 0) > 0; }).forEach(function(c) {
                productOpts += '<option value="course-' + c.id + '" data-price="' + c.price + '" data-apr="' + (c.default_apr || 0) + '" data-terms="' + (c.credit_terms_months || '3,6') + '" data-title="' + escapeHtml(c.title) + '">Course: ' + escapeHtml(c.title) + ' (UGX ' + formatPrice(c.price) + ')</option>';
            });
        }
    } catch (e) { console.error('Failed to load data', e); }

    var body =
        '<form id="createCreditForm">' +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">' +
                '<div style="grid-column:1/-1;"><label style="display:block;font-weight:600;margin-bottom:4px;">Customer *</label><select name="customer_id" required style="' + inputStyle + '" id="creditCustomerSelect">' + customerOpts + '</select></div>' +
                '<div style="grid-column:1/-1;"><label style="display:block;font-weight:600;margin-bottom:4px;">Product/Course *</label><select name="item" required style="' + inputStyle + '" id="creditItemSelect">' + productOpts + '</select></div>' +
                '<div><label style="display:block;font-weight:600;margin-bottom:4px;">Quantity</label><input type="number" name="quantity" value="1" min="1" style="' + inputStyle + '" id="creditQty" /></div>' +
                '<div><label style="display:block;font-weight:600;margin-bottom:4px;">Credit Period</label><select name="months" style="' + inputStyle + '" id="creditMonthsSelect"><option value="">Select item first</option></select></div>' +
            '</div>' +
            '<fieldset style="border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:16px;">' +
                '<legend style="font-weight:600;color:#1e40af;padding:0 8px;">Down Payment</legend>' +
                '<div style="display:flex;gap:16px;align-items:center;margin-bottom:12px;">' +
                    '<label style="cursor:pointer;"><input type="radio" name="deposit_type" value="apr" checked id="depositApr"> Default (from APR %)</label>' +
                    '<label style="cursor:pointer;"><input type="radio" name="deposit_type" value="custom" id="depositCustom"> Custom Amount</label>' +
                    '<label style="cursor:pointer;"><input type="radio" name="deposit_type" value="none" id="depositNone"> No Down Payment</label>' +
                '</div>' +
                '<div id="depositInfo" style="font-size:13px;color:#64748b;margin-bottom:8px;">Deposit: 20% of total (calculated from product APR)</div>' +
                '<div id="customDepositField" style="display:none;"><label style="display:block;font-weight:600;margin-bottom:4px;">Down Payment Amount (UGX)</label><input type="number" name="custom_deposit" min="0" style="' + inputStyle + '" id="customDepositInput" placeholder="Enter deposit amount" /></div>' +
            '</fieldset>' +
            '<div id="creditPreview" style="margin-top:16px;padding:14px;background:#f0f9ff;border-radius:6px;border:1px solid #bfdbfe;display:none;"></div>' +
            '<div style="text-align:right;margin-top:18px;">' +
                '<button type="button" onclick="removeModal(\'createCreditModal\')" style="padding:8px 20px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-right:10px;">Cancel</button>' +
                '<button type="submit" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">Create Credit Plan</button>' +
            '</div>' +
        '</form>';

    createModal('createCreditModal', 'Create Credit Plan for Customer', body, '650px');

    var itemSelect = document.getElementById('creditItemSelect');
    var qtyInput = document.getElementById('creditQty');
    var monthsSelect = document.getElementById('creditMonthsSelect');
    var preview = document.getElementById('creditPreview');
    var depositInfo = document.getElementById('depositInfo');
    var customField = document.getElementById('customDepositField');
    var customInput = document.getElementById('customDepositInput');

    function updateTermsDropdown() {
        var opt = itemSelect.options[itemSelect.selectedIndex];
        if (!opt || !opt.value) return;
        var terms = (opt.getAttribute('data-terms') || '3,6').split(',').map(function(t) { return parseInt(t.trim()); }).filter(function(t) { return t > 0; });
        if (terms.length === 0) terms = [3, 6];
        var current = Number(monthsSelect.value);
        monthsSelect.innerHTML = terms.map(function(m) {
            return '<option value="' + m + '"' + (m === current ? ' selected' : '') + '>' + m + ' Months</option>';
        }).join('');
    }

    function getDeposit(total) {
        var type = document.querySelector('input[name="deposit_type"]:checked').value;
        if (type === 'none') return 0;
        if (type === 'custom') return Number(customInput.value) || 0;
        return roundTo500(total * 0.2); // default APR-based 20%
    }

    function updatePreview() {
        var opt = itemSelect.options[itemSelect.selectedIndex];
        if (!opt || !opt.value) { preview.style.display = 'none'; return; }
        updateTermsDropdown();
        var price = Number(opt.getAttribute('data-price') || 0);
        var apr = Number(opt.getAttribute('data-apr') || 0);
        var qty = Number(qtyInput.value) || 1;
        var months = Number(monthsSelect.value);
        if (!months) { preview.style.display = 'none'; return; }
        var total = price * qty;
        var deposit = getDeposit(total);
        var c = computeCredit(total, months, apr, deposit);

        var depositType = document.querySelector('input[name="deposit_type"]:checked').value;
        customField.style.display = depositType === 'custom' ? 'block' : 'none';
        if (depositType === 'none') depositInfo.textContent = 'No down payment — full amount distributed across ' + months + ' months';
        else if (depositType === 'custom') depositInfo.textContent = 'Custom deposit amount';
        else depositInfo.textContent = 'Deposit: 20% of UGX ' + formatPrice(total) + ' = UGX ' + formatPrice(c.deposit);

        preview.style.display = 'block';
        preview.innerHTML =
            '<strong style="color:#1e40af;">Credit Summary</strong>' +
            '<div style="margin-top:8px;font-size:14px;">' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Item Total:</span><strong>UGX ' + formatPrice(total) + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Down Payment:</span><strong>UGX ' + formatPrice(c.deposit) + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>To Finance:</span><strong>UGX ' + formatPrice(c.remaining) + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Monthly Payment:</span><strong>UGX ' + formatPrice(c.monthly) + ' x ' + c.months + '</strong></div>' +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>APR:</span><strong>' + apr + '%</strong></div>' +
                (c.totalInterest > 0 ? '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>Total Interest:</span><strong>UGX ' + formatPrice(c.totalInterest) + '</strong></div>' : '') +
                '<div style="display:flex;justify-content:space-between;padding:4px 0;border-top:1px solid #bfdbfe;margin-top:4px;"><span>Total Payable:</span><strong style="color:#1e40af;">UGX ' + formatPrice(c.deposit + c.remaining + c.totalInterest) + '</strong></div>' +
            '</div>';
    }

    itemSelect.addEventListener('change', updatePreview);
    qtyInput.addEventListener('input', updatePreview);
    monthsSelect.addEventListener('change', updatePreview);
    document.querySelectorAll('input[name="deposit_type"]').forEach(function(r) { r.addEventListener('change', updatePreview); });
    if (customInput) customInput.addEventListener('input', updatePreview);

    document.getElementById('createCreditForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        var custOpt = document.getElementById('creditCustomerSelect').options[document.getElementById('creditCustomerSelect').selectedIndex];
        var itemOpt = itemSelect.options[itemSelect.selectedIndex];
        if (!custOpt.value || !itemOpt.value) { showToast('Select customer and item', 'error'); return; }

        var price = Number(itemOpt.getAttribute('data-price'));
        var apr = Number(itemOpt.getAttribute('data-apr') || 0);
        var title = itemOpt.getAttribute('data-title');
        var qty = Number(qtyInput.value) || 1;
        var months = Number(monthsSelect.value);
        var total = price * qty;
        var deposit = getDeposit(total);
        var c = computeCredit(total, months, apr, deposit);

        // Build schedule with dates and smart rounding
        var schedule = [];
        var now = new Date();
        var bal = c.remaining;
        for (var m = 0; m < months; m++) {
            var due = new Date(now);
            due.setMonth(due.getMonth() + m + 1);
            var pmt = (m === months - 1) ? bal : Math.min(c.monthly, bal);
            bal = Math.max(0, bal - pmt);
            schedule.push({ date: due.toISOString().split('T')[0], amount: pmt, status: 'pending', paid_amount: 0 });
        }

        var isProduct = itemOpt.value.startsWith('product-');
        var itemId = parseInt(itemOpt.value.replace('product-', '').replace('course-', ''));

        var depositLabel = deposit > 0 ? 'Deposit: UGX ' + formatPrice(deposit) + '. ' : 'No deposit. ';

        var fd = new FormData();
        fd.append('action', 'add');
        fd.append('customer_name', custOpt.getAttribute('data-name'));
        fd.append('customer_email', custOpt.getAttribute('data-email'));
        fd.append('customer_phone', custOpt.getAttribute('data-phone'));
        fd.append('total_now', deposit.toFixed(2));
        fd.append('total_full', (total + c.totalInterest).toFixed(2));
        fd.append('payment_method', 'credit');
        fd.append('status', 'completed');
        fd.append('admin_notes', depositLabel + months + ' months at ' + apr + '% APR. Created by admin.');
        fd.append('items_json', JSON.stringify([{
            type: isProduct ? 'product' : 'course',
            productId: isProduct ? itemId : undefined,
            itemId: isProduct ? undefined : 'course-' + itemId,
            title: title, name: title, price: price, quantity: qty,
            paymentPlan: { type: 'credit', months: months, interestRate: apr }
        }]));
        fd.append('schedule_json', JSON.stringify(schedule));

        try {
            var res = await fetch(API.orders, { method: 'POST', body: fd });
            var json = await res.json();
            if (json.success) {
                showToast('Credit plan created for ' + custOpt.getAttribute('data-name'));
                removeModal('createCreditModal');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast(json.message || 'Failed to create', 'error');
            }
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
    });
}
// Reschedule overdue: move this installment to next month from today
function rescheduleOverdue(orderId, orderNumber, paymentIndex) {
    showConfirmModal('Reschedule Payment', 'Move overdue payment for <strong>' + orderNumber + '</strong> to next month from today?', 'Reschedule', async function() {
    try {
        var res = await fetch(API.orders + '?action=get&id=' + orderId);
        var json = await res.json();
        if (!json.success) { showToast('Order not found', 'error'); return; }
        var schedule = JSON.parse(json.data.schedule_json || '[]');

        // Move this overdue entry to 1 month from today
        var nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        schedule[paymentIndex].date = nextMonth.toISOString().split('T')[0];

        // Re-sort by date
        var paid = schedule.filter(function(s) { return s.status === 'paid'; });
        var unpaid = schedule.filter(function(s) { return s.status !== 'paid'; });
        unpaid.sort(function(a, b) { return a.date.localeCompare(b.date); });
        schedule = paid.concat(unpaid);

        var fd = new FormData();
        fd.append('action', 'update_schedule');
        fd.append('id', orderId);
        fd.append('schedule_json', JSON.stringify(schedule));
        var res2 = await fetch(API.orders, { method: 'POST', body: fd });
        var json2 = await res2.json();
        if (json2.success) { showToast('Rescheduled to ' + nextMonth.toLocaleDateString()); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(json2.message || 'Failed', 'error'); }
    } catch (err) { showToast('Error: ' + err.message, 'error'); }
    });
}

// Roll overdue: merge this overdue amount into the next upcoming installment
function rollOverdue(orderId, orderNumber, paymentIndex) {
    showConfirmModal('Roll Up Payment', 'Roll overdue amount into the next upcoming installment for <strong>' + orderNumber + '</strong>?', 'Roll Up', async function() {
    try {
        var res = await fetch(API.orders + '?action=get&id=' + orderId);
        var json = await res.json();
        if (!json.success) { showToast('Order not found', 'error'); return; }
        var schedule = JSON.parse(json.data.schedule_json || '[]');
        var today = new Date().toISOString().split('T')[0];

        var overdueEntry = schedule[paymentIndex];
        var overdueAmount = overdueEntry.amount - (overdueEntry.paid_amount || 0);

        // Find next upcoming (not overdue, not paid)
        var nextUpcoming = null;
        for (var i = 0; i < schedule.length; i++) {
            if (schedule[i].status !== 'paid' && (schedule[i].date || '') >= today && i !== paymentIndex) {
                nextUpcoming = i;
                break;
            }
        }

        if (nextUpcoming === null) {
            // No upcoming — add a new installment 1 month from today
            var newDate = new Date();
            newDate.setMonth(newDate.getMonth() + 1);
            schedule.push({ date: newDate.toISOString().split('T')[0], amount: overdueAmount, status: 'pending', paid_amount: 0 });
        } else {
            // Add overdue amount to the next upcoming installment
            schedule[nextUpcoming].amount = (schedule[nextUpcoming].amount || 0) + overdueAmount;
        }

        // Mark the overdue as paid (rolled)
        overdueEntry.status = 'paid';
        overdueEntry.paid_amount = overdueEntry.amount;
        overdueEntry.notes = 'Rolled into next installment';

        var fd = new FormData();
        fd.append('action', 'update_schedule');
        fd.append('id', orderId);
        fd.append('schedule_json', JSON.stringify(schedule));
        var res2 = await fetch(API.orders, { method: 'POST', body: fd });
        var json2 = await res2.json();
        if (json2.success) { showToast('Overdue rolled into next installment'); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(json2.message || 'Failed', 'error'); }
    } catch (err) { showToast('Error: ' + err.message, 'error'); }
    });
}
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
