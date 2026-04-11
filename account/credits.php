<?php
// ============================================================
// Zaga Technologies - Customer Credit Dashboard
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_login();

$user = current_user();
$conn = getDbConnection();

// Fetch all credit orders for this customer
$stmt = $conn->prepare("SELECT id, order_number, customer_name, total_now, total_full, payment_method, status, items_json, schedule_json, payments_made_json, order_date, updated_at FROM orders WHERE customer_email = ? AND status != 'cancelled' ORDER BY order_date DESC");
$stmt->bind_param('s', $user['email']);
$stmt->execute();
$result = $stmt->get_result();

$activeCredits = [];
$completedCredits = [];
$totalOutstanding = 0;
$totalPaid = 0;

// Build product/course maps for name resolution
$productMap = [];
$res2 = $conn->query("SELECT id, title, slug, price, image FROM products");
if ($res2) { while ($r = $res2->fetch_assoc()) $productMap[intval($r['id'])] = $r; }
$courseMap = [];
$res2 = $conn->query("SELECT id, title, slug, price, icon FROM courses");
if ($res2) { while ($r = $res2->fetch_assoc()) $courseMap[intval($r['id'])] = $r; }

while ($order = $result->fetch_assoc()) {
    $items = json_decode($order['items_json'] ?? '[]', true) ?: [];
    $schedule = json_decode($order['schedule_json'] ?? '[]', true) ?: [];
    $paymentsMade = json_decode($order['payments_made_json'] ?? '[]', true) ?: [];

    // Check if this order has credit items or a payment schedule
    $hasCredit = false;
    foreach ($items as $item) {
        if (isset($item['paymentPlan']) && ($item['paymentPlan']['type'] ?? '') === 'credit') {
            $hasCredit = true;
            break;
        }
    }
    if (!$hasCredit && !empty($schedule)) $hasCredit = true;
    if (!$hasCredit) continue;

    // Resolve item names
    foreach ($items as &$item) {
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

    $paidAmount = 0;
    foreach ($paymentsMade as $p) {
        $paidAmount += floatval($p['amount'] ?? 0);
    }
    // Include deposit (total_now) as paid
    $depositPaid = floatval($order['total_now']);
    $fullTotal = floatval($order['total_full']);
    $remaining = max(0, $fullTotal - $depositPaid - $paidAmount);

    $order['_items'] = $items;
    $order['_schedule'] = $schedule;
    $order['_payments'] = $paymentsMade;
    $order['_paid'] = $depositPaid + $paidAmount;
    $order['_remaining'] = $remaining;
    $order['_progress'] = $fullTotal > 0 ? min(100, round((($depositPaid + $paidAmount) / $fullTotal) * 100)) : 0;

    // Count schedule payments completed
    $scheduleCompleted = 0;
    $scheduleTotal = count($schedule);
    foreach ($schedule as $s) {
        if (($s['status'] ?? '') === 'paid' || ($s['paid'] ?? false)) $scheduleCompleted++;
    }
    $order['_scheduleCompleted'] = $scheduleCompleted;
    $order['_scheduleTotal'] = $scheduleTotal;

    if ($remaining <= 0) {
        // All payments done — truly completed
        $completedCredits[] = $order;
    } else {
        // Still has unpaid balance — ongoing
        $activeCredits[] = $order;
        $totalOutstanding += $remaining;
    }
    $totalPaid += $depositPaid + $paidAmount;
}
$stmt->close();
$conn->close();

$page_title = 'My Credits';
$current_page = 'account';
$sidebar_page = 'credits';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="dashboard-page-header">
            <div>
                <h1>My Credit Plans</h1>
                <p>Track your credit purchases and payment progress.</p>
            </div>
        </div>

        <!-- Credit Stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:32px;">
            <div style="background:white;padding:16px;border-radius:8px;border-left:4px solid #2563eb;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <p style="font-size:12px;color:#64748b;margin:0 0 4px;">Ongoing</p>
                <p style="font-size:22px;font-weight:700;color:#1e293b;margin:0;"><?php echo count($activeCredits); ?></p>
            </div>
            <div style="background:white;padding:16px;border-radius:8px;border-left:4px solid #dc2626;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <p style="font-size:12px;color:#64748b;margin:0 0 4px;">Outstanding</p>
                <p style="font-size:16px;font-weight:700;color:#dc2626;margin:0;">UGX <?php echo number_format($totalOutstanding, 0); ?></p>
            </div>
            <div style="background:white;padding:16px;border-radius:8px;border-left:4px solid #16a34a;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <p style="font-size:12px;color:#64748b;margin:0 0 4px;">Total Paid</p>
                <p style="font-size:16px;font-weight:700;color:#16a34a;margin:0;">UGX <?php echo number_format($totalPaid, 0); ?></p>
            </div>
            <div style="background:white;padding:16px;border-radius:8px;border-left:4px solid #7c3aed;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <p style="font-size:12px;color:#64748b;margin:0 0 4px;">Completed</p>
                <p style="font-size:22px;font-weight:700;color:#7c3aed;margin:0;"><?php echo count($completedCredits); ?></p>
            </div>
        </div>

        <!-- Ongoing Credits -->
        <?php if (!empty($activeCredits)): ?>
        <h2 style="font-size:18px;margin-bottom:16px;color:#1e293b;">Ongoing Credit Plans</h2>
        <?php foreach ($activeCredits as $credit): ?>
        <div style="background:white;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
                <div>
                    <strong style="color:#2563eb;font-size:16px;"><?php echo safe_output($credit['order_number']); ?></strong>
                    <span style="color:#64748b;font-size:13px;margin-left:8px;"><?php echo date('M j, Y', strtotime($credit['order_date'])); ?></span>
                </div>
                <span style="background:#dbeafe;color:#1e40af;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                    Ongoing
                </span>
            </div>

            <!-- Items -->
            <div style="margin-bottom:16px;">
                <?php foreach ($credit['_items'] as $item):
                    if (($item['paymentPlan']['type'] ?? '') !== 'credit') continue;
                    $name = $item['title'] ?? $item['name'] ?? 'Item';
                ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px;">
                    <span><?php echo safe_output($name); ?> x<?php echo intval($item['quantity'] ?? 1); ?></span>
                    <span>UGX <?php echo number_format(floatval($item['price'] ?? 0) * intval($item['quantity'] ?? 1), 0); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Progress Bar -->
            <div style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                    <span style="color:#16a34a;font-weight:600;">Paid: UGX <?php echo number_format($credit['_paid'], 0); ?></span>
                    <span style="color:#dc2626;font-weight:600;">Remaining: UGX <?php echo number_format($credit['_remaining'], 0); ?></span>
                </div>
                <div style="background:#e2e8f0;border-radius:10px;height:10px;overflow:hidden;">
                    <div style="background:linear-gradient(90deg,#16a34a,#22c55e);height:100%;border-radius:10px;width:<?php echo $credit['_progress']; ?>%;transition:width 0.5s;"></div>
                </div>
                <p style="text-align:center;font-size:12px;color:#64748b;margin-top:4px;"><?php echo $credit['_progress']; ?>% Complete</p>
            </div>

            <!-- Payment Schedule -->
            <?php if (!empty($credit['_schedule'])): ?>
            <details style="margin-top:8px;">
                <summary style="cursor:pointer;font-weight:600;color:#2563eb;font-size:14px;">Payment Schedule (<?php echo $credit['_scheduleCompleted']; ?>/<?php echo $credit['_scheduleTotal']; ?> paid)</summary>
                <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;margin-top:8px;font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;">#</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;">Due Date</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;">Amount</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orderDate = strtotime($credit['order_date']);
                        $todayStr = date('Y-m-d');
                        foreach ($credit['_schedule'] as $idx => $entry):
                            $dueDate = $entry['date'] ?? $entry['due_date'] ?? $entry['dueDate'] ?? '';
                            if (empty($dueDate) && $orderDate) {
                                $dueDate = date('Y-m-d', strtotime('+' . ($idx + 1) . ' months', $orderDate));
                            }
                            $isPaid = (isset($entry['status']) && $entry['status'] === 'paid') || (!empty($entry['paid']) && $entry['paid'] === true);
                            $isOverdue = !$isPaid && !empty($dueDate) && $dueDate < $todayStr;
                            $partialPaid = floatval($entry['paid_amount'] ?? 0);
                            // Format date explicitly
                            $dueDateFormatted = '-';
                            if (!empty($dueDate)) {
                                $ts = strtotime($dueDate);
                                if ($ts) {
                                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                    $dueDateFormatted = $months[(int)date('n', $ts) - 1] . ' ' . date('j', $ts) . ', ' . date('Y', $ts);
                                }
                            }
                        ?>
                        <tr style="<?php echo $isOverdue ? 'background:#fef2f2;' : ''; ?>">
                            <td style="padding:8px;border-bottom:1px solid #f1f5f9;"><?php echo $idx + 1; ?></td>
                            <td style="padding:8px;border-bottom:1px solid #f1f5f9;<?php echo $isOverdue ? 'color:#dc2626;font-weight:600;' : ''; ?>"><?php echo $dueDateFormatted; ?></td>
                            <td style="padding:8px;border-bottom:1px solid #f1f5f9;">UGX <?php echo number_format(floatval($entry['amount'] ?? $entry['payment'] ?? 0), 0); ?><?php if ($partialPaid > 0 && !$isPaid): ?><br><small style="color:#16a34a;">Paid: UGX <?php echo number_format($partialPaid, 0); ?></small><?php endif; ?></td>
                            <td style="padding:8px;border-bottom:1px solid #f1f5f9;">
                                <?php if ($isPaid): ?>
                                    <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;">Paid</span>
                                <?php elseif ($isOverdue): ?>
                                    <span style="background:#fef2f2;color:#991b1b;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;">Overdue</span>
                                <?php else: ?>
                                    <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;">Upcoming</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </details>
            <?php endif; ?>

            <!-- Actions -->
            <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;">
                <a href="https://wa.me/256700706809?text=<?php echo urlencode('Hi, I want to make a payment for order ' . $credit['order_number']); ?>" target="_blank" style="display:inline-block;padding:8px 20px;background:#25d366;color:white;border-radius:5px;text-decoration:none;font-weight:600;font-size:13px;">Make Payment via WhatsApp</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Completed Credits -->
        <?php if (!empty($completedCredits)): ?>
        <h2 style="font-size:18px;margin:24px 0 16px;color:#1e293b;">Completed Credit Plans</h2>
        <?php foreach ($completedCredits as $credit): ?>
        <div style="background:white;border:1px solid #dcfce7;border-radius:8px;padding:16px;margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong style="color:#166534;"><?php echo safe_output($credit['order_number']); ?></strong>
                    <span style="color:#64748b;font-size:13px;margin-left:8px;"><?php echo date('M j, Y', strtotime($credit['order_date'])); ?></span>
                </div>
                <span style="background:#dcfce7;color:#166534;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">Completed</span>
            </div>
            <p style="font-size:14px;color:#64748b;margin:8px 0 0;">Total Paid: <strong style="color:#166534;">UGX <?php echo number_format($credit['_paid'], 0); ?></strong></p>

            <?php if (!empty($credit['_schedule'])): ?>
            <details style="margin-top:12px;">
                <summary style="cursor:pointer;font-weight:600;color:#166534;font-size:14px;">View Payment History (<?php echo count($credit['_schedule']); ?> payments)</summary>
                <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;margin-top:8px;font-size:13px;">
                    <thead>
                        <tr style="background:#f0fdf4;">
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #dcfce7;">#</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #dcfce7;">Date</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #dcfce7;">Amount</th>
                            <th style="padding:8px;text-align:left;border-bottom:1px solid #dcfce7;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $compOrderDate = strtotime($credit['order_date']);
                        foreach ($credit['_schedule'] as $idx => $entry):
                            $dueDate = $entry['date'] ?? $entry['due_date'] ?? '';
                            if (empty($dueDate) && $compOrderDate) {
                                $dueDate = date('Y-m-d', strtotime('+' . ($idx + 1) . ' months', $compOrderDate));
                            }
                            $compDateFormatted = '-';
                            if (!empty($dueDate)) {
                                $ts = strtotime($dueDate);
                                if ($ts) {
                                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                    $compDateFormatted = $months[(int)date('n', $ts) - 1] . ' ' . date('j', $ts) . ', ' . date('Y', $ts);
                                }
                            }
                            $entryStatus = $entry['status'] ?? 'pending';
                        ?>
                        <tr>
                            <td style="padding:8px;border-bottom:1px solid #f0fdf4;"><?php echo $idx + 1; ?></td>
                            <td style="padding:8px;border-bottom:1px solid #f0fdf4;"><?php echo $compDateFormatted; ?></td>
                            <td style="padding:8px;border-bottom:1px solid #f0fdf4;">UGX <?php echo number_format(floatval($entry['amount'] ?? $entry['payment'] ?? 0), 0); ?></td>
                            <td style="padding:8px;border-bottom:1px solid #f0fdf4;">
                                <?php if ($entryStatus === 'paid'): ?>
                                <span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;">Paid</span>
                                <?php else: ?>
                                <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;"><?php echo ucfirst($entryStatus); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </details>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($activeCredits) && empty($completedCredits)): ?>
        <div style="text-align:center;padding:60px 20px;color:#64748b;">
            <p style="font-size:16px;margin-bottom:12px;">No credit plans found.</p>
            <a href="<?php echo SITE_URL; ?>/shop" class="btn btn-primary">Start Shopping</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
