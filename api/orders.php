<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/notifications.php';

$action = $_REQUEST['action'] ?? 'list';

// Public actions (no auth needed)
if ($action === 'create' || $action === 'track' || $action === 'customer_cancel' || $action === 'customer_orders') {
    $conn = getDbConnection();

    if ($action === 'create') {
        // Create order from checkout - public endpoint
        $orderNumber = trim($_POST['order_number'] ?? '');
        if (empty($orderNumber)) {
            $orderNumber = 'ORD-' . time() . '-' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        }
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $totalNow = floatval($_POST['total_now'] ?? 0);
        $totalFull = floatval($_POST['total_full'] ?? 0);
        $paymentMethod = trim($_POST['payment_method'] ?? 'cash_on_delivery');
        $itemsJson = trim($_POST['items_json'] ?? '[]');
        $scheduleJson = trim($_POST['schedule_json'] ?? '[]');

        if (empty($customerName) || empty($customerEmail)) {
            echo json_encode(['success' => false, 'message' => 'Customer name and email are required']);
            $conn->close();
            exit;
        }

        // Find or create customer
        $customerId = null;
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param('s', $customerEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $customerId = $row['id'];
        }
        $stmt->close();

        if (!$customerId) {
            $stmt = $conn->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $customerName, $customerEmail, $customerPhone);
            $stmt->execute();
            $customerId = $conn->insert_id;
            $stmt->close();
        }

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_number, customer_name, customer_email, customer_phone, shipping_address, total_now, total_full, payment_method, status, items_json, schedule_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->bind_param('isssssddsss', $customerId, $orderNumber, $customerName, $customerEmail, $customerPhone, $shippingAddress, $totalNow, $totalFull, $paymentMethod, $itemsJson, $scheduleJson);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order created', 'order_number' => $orderNumber, 'data' => ['order_number' => $orderNumber, 'order_id' => $conn->insert_id]]);
            // Send notification emails
            try { notify_new_order($orderNumber, $customerName, $customerEmail, $totalNow, $paymentMethod); } catch (Exception $e) {}
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $conn->error]);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($action === 'track') {
        // Public order tracking - lookup by order_number + email or phone
        $orderNumber = trim($_GET['order_number'] ?? '');
        $identifier = trim($_GET['identifier'] ?? '');

        if (empty($orderNumber) || empty($identifier)) {
            echo json_encode(['success' => false, 'message' => 'Order number and email/phone are required']);
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("SELECT id, order_number, customer_name, customer_email, customer_phone, total_now, total_full, payment_method, status, items_json, schedule_json, payments_made_json, order_date, updated_at FROM orders WHERE order_number = ? AND (customer_email = ? OR customer_phone = ?)");
        $stmt->bind_param('sss', $orderNumber, $identifier, $identifier);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order) {
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Order not found. Please check your order number and email/phone.']);
        }
        $conn->close();
        exit;
    }

    if ($action === 'customer_cancel') {
        $orderNumber = trim($_POST['order_number'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');

        if (empty($orderNumber) || empty($customerEmail)) {
            echo json_encode(['success' => false, 'message' => 'Order number and email are required']);
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("SELECT id, status FROM orders WHERE order_number = ? AND customer_email = ?");
        $stmt->bind_param('ss', $orderNumber, $customerEmail);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            $conn->close();
            exit;
        }

        if ($order['status'] === 'completed') {
            echo json_encode(['success' => false, 'message' => 'Completed orders cannot be cancelled']);
            $conn->close();
            exit;
        }

        if ($order['status'] === 'cancelled') {
            echo json_encode(['success' => false, 'message' => 'Order is already cancelled']);
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $order['id']);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($action === 'customer_orders') {
        // Fetch orders for a customer - session required (no raw email param)
        $customerEmail = '';
        if (isset($_SESSION['user_email'])) {
            $customerEmail = $_SESSION['user_email'];
        } elseif (isset($_SESSION['customer_email'])) {
            $customerEmail = $_SESSION['customer_email'];
        }

        if (empty($customerEmail)) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("SELECT id, order_number, customer_name, customer_email, customer_phone, total_now, total_full, payment_method, status, items_json, schedule_json, payments_made_json, order_date, updated_at FROM orders WHERE customer_email = ? ORDER BY order_date DESC");
        $stmt->bind_param('s', $customerEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'data' => $orders]);
        exit;
    }
}

// Admin-only actions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

switch ($action) {
    case 'list':
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $sql = "SELECT o.*, c.name as linked_customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id";
        $conditions = [];
        $params = [];
        $types = '';

        if ($search !== '') {
            $like = '%' . $search . '%';
            $conditions[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_phone LIKE ?)";
            $params = array_merge($params, [$like, $like, $like, $like]);
            $types .= 'ssss';
        }
        if ($status !== '') {
            $conditions[] = "o.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY o.order_date DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $orders]);
        break;

    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT o.*, c.name as linked_customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order) {
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
        }
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');
        $paymentMethod = trim($_POST['payment_method'] ?? '');
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $totalNow = floatval($_POST['total_now'] ?? 0);
        $totalFull = floatval($_POST['total_full'] ?? 0);
        $customerId = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;

        if (empty($customerName)) {
            echo json_encode(['success' => false, 'message' => 'Customer name is required']);
            break;
        }

        // Get old status and schedule to detect changes
        $stmt = $conn->prepare("SELECT status, order_number, schedule_json FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $oldOrder = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $oldStatus = $oldOrder['status'] ?? '';
        $orderNum = $oldOrder['order_number'] ?? '';

        $stmt = $conn->prepare("UPDATE orders SET customer_id = ?, customer_name = ?, customer_email = ?, customer_phone = ?, status = ?, payment_method = ?, admin_notes = ?, total_now = ?, total_full = ? WHERE id = ?");
        $stmt->bind_param('issssssddi', $customerId, $customerName, $customerEmail, $customerPhone, $status, $paymentMethod, $adminNotes, $totalNow, $totalFull, $id);

        if ($stmt->execute()) {
            // When status changes to completed, activate credit — set schedule dates from now
            if ($status === 'completed' && $oldStatus !== 'completed') {
                $schedule = json_decode($oldOrder['schedule_json'] ?? '[]', true) ?: [];
                if (!empty($schedule)) {
                    $now = new DateTime();
                    foreach ($schedule as $idx => &$entry) {
                        $due = clone $now;
                        $due->modify('+' . ($idx + 1) . ' months');
                        $entry['date'] = $due->format('Y-m-d');
                        if (!isset($entry['status'])) $entry['status'] = 'pending';
                    }
                    unset($entry);
                    $newSchedule = json_encode($schedule);
                    $stmt2 = $conn->prepare("UPDATE orders SET schedule_json = ? WHERE id = ?");
                    $stmt2->bind_param('si', $newSchedule, $id);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }

            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
            if ($oldStatus !== $status && !empty($customerEmail)) {
                try { notify_order_status($orderNum, $customerName, $customerEmail, $status); } catch (Exception $e) {}
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'update_status':
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            break;
        }

        // Fetch order details for notification and schedule update
        $stmt = $conn->prepare("SELECT order_number, customer_name, customer_email, status as old_status, schedule_json, items_json FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $orderInfo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute()) {
            // When order is marked "completed", credit starts — regenerate schedule dates from NOW
            if ($status === 'completed' && $orderInfo['old_status'] !== 'completed') {
                $schedule = json_decode($orderInfo['schedule_json'] ?? '[]', true) ?: [];
                if (!empty($schedule)) {
                    $now = new DateTime();
                    foreach ($schedule as $idx => &$entry) {
                        $due = clone $now;
                        $due->modify('+' . ($idx + 1) . ' months');
                        $entry['date'] = $due->format('Y-m-d');
                        if (!isset($entry['status'])) $entry['status'] = 'pending';
                    }
                    unset($entry);
                    $newSchedule = json_encode($schedule);
                    $stmt2 = $conn->prepare("UPDATE orders SET schedule_json = ? WHERE id = ?");
                    $stmt2->bind_param('si', $newSchedule, $id);
                    $stmt2->execute();
                    $stmt2->close();
                }
            }

            echo json_encode(['success' => true, 'message' => 'Order status updated']);
            if ($orderInfo && !empty($orderInfo['customer_email'])) {
                try { notify_order_status($orderInfo['order_number'], $orderInfo['customer_name'], $orderInfo['customer_email'], $status); } catch (Exception $e) {}
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        $stmt->close();
        break;

    case 'record_payment':
        $id = intval($_POST['id'] ?? 0);
        $paymentsJson = trim($_POST['payments_json'] ?? '[]');
        $scheduleJson = trim($_POST['schedule_json'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);

        // Capture previous payments so we can tell what was just added
        // (needed for the receipt email, since callers pass the full
        // updated payments array, not a delta).
        $stmtPrev = $conn->prepare("SELECT payments_made_json FROM orders WHERE id = ?");
        $stmtPrev->bind_param('i', $id);
        $stmtPrev->execute();
        $prevRow = $stmtPrev->get_result()->fetch_assoc();
        $stmtPrev->close();
        $prevPayments = json_decode($prevRow['payments_made_json'] ?? '[]', true) ?: [];
        $prevTotal = 0;
        foreach ($prevPayments as $p) $prevTotal += floatval($p['amount'] ?? 0);

        // Update payments_made_json and optionally schedule_json
        if (!empty($scheduleJson)) {
            $stmt = $conn->prepare("UPDATE orders SET payments_made_json = ?, schedule_json = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('ssi', $paymentsJson, $scheduleJson, $id);
        } else {
            $stmt = $conn->prepare("UPDATE orders SET payments_made_json = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('si', $paymentsJson, $id);
        }

        if ($stmt->execute()) {
            // Check if fully paid — auto-complete
            $stmt2 = $conn->prepare("SELECT total_now, total_full, order_number, customer_name, customer_email FROM orders WHERE id = ?");
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $orderData = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();

            $payments = json_decode($paymentsJson, true) ?: [];
            $totalPayments = 0;
            foreach ($payments as $p) $totalPayments += floatval($p['amount'] ?? 0);
            $deposit = floatval($orderData['total_now'] ?? 0);
            $fullTotal = floatval($orderData['total_full'] ?? 0);

            // Delta actually recorded in this call. Prefer the explicit
            // 'amount' the caller passed; fall back to the delta derived
            // from the payments array when it's missing or zero.
            $deltaPaid = $amount > 0 ? $amount : max(0, $totalPayments - $prevTotal);
            $totalPaidIncludingDeposit = $deposit + $totalPayments;

            $completed = false;
            if (($deposit + $totalPayments) >= $fullTotal) {
                $stmtComplete = $conn->prepare("UPDATE orders SET status = 'completed', updated_at = NOW() WHERE id = ?");
                $stmtComplete->bind_param('i', $id);
                $stmtComplete->execute();
                $stmtComplete->close();
                $completed = true;
                if (!empty($orderData['customer_email'])) {
                    try { notify_order_status($orderData['order_number'], $orderData['customer_name'], $orderData['customer_email'], 'completed'); } catch (Exception $e) {}
                }
            }

            // Always send a payment receipt to the customer — partial or
            // final — so they have a paper trail for every installment.
            if ($deltaPaid > 0 && !empty($orderData['customer_email'])) {
                try {
                    notify_payment_received(
                        $orderData['order_number'],
                        $orderData['customer_name'],
                        $orderData['customer_email'],
                        $deltaPaid,
                        $totalPaidIncludingDeposit,
                        $fullTotal
                    );
                } catch (Exception $e) {}
            }

            echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record payment']);
        }
        $stmt->close();
        break;

    case 'update_schedule':
        $id = intval($_POST['id'] ?? 0);
        $scheduleJson = trim($_POST['schedule_json'] ?? '[]');
        $stmt = $conn->prepare("UPDATE orders SET schedule_json = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('si', $scheduleJson, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Schedule updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update schedule']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        // Protect active credit orders from deletion
        $stmt = $conn->prepare("SELECT status, payment_method, schedule_json FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $delOrder = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($delOrder && $delOrder['payment_method'] === 'credit' && in_array($delOrder['status'], ['pending', 'processing', 'completed'])) {
            $delSchedule = json_decode($delOrder['schedule_json'] ?? '[]', true) ?: [];
            $hasUnpaid = false;
            foreach ($delSchedule as $s) {
                if (($s['status'] ?? 'pending') !== 'paid') { $hasUnpaid = true; break; }
            }
            if ($hasUnpaid || $delOrder['status'] !== 'cancelled') {
                echo json_encode(['success' => false, 'message' => 'Cannot delete an active credit order. Cancel it first.']);
                break;
            }
        }

        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete order']);
        }
        $stmt->close();
        break;

    case 'add':
        // Admin manually creates an order
        $orderNumber = 'ORD-' . time() . '-' . rand(100, 999);
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $totalNow = floatval($_POST['total_now'] ?? 0);
        $totalFull = floatval($_POST['total_full'] ?? 0);
        $paymentMethod = trim($_POST['payment_method'] ?? 'cash_on_delivery');
        $status = trim($_POST['status'] ?? 'pending');
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $itemsJson = trim($_POST['items_json'] ?? '[]');

        if (empty($customerName)) {
            echo json_encode(['success' => false, 'message' => 'Customer name is required']);
            break;
        }

        // Find or create customer
        $customerId = null;
        if (!empty($customerEmail)) {
            $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->bind_param('s', $customerEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $customerId = $row['id'];
            }
            $stmt->close();

            if (!$customerId) {
                $stmt = $conn->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $customerName, $customerEmail, $customerPhone);
                $stmt->execute();
                $customerId = $conn->insert_id;
                $stmt->close();
            }
        }

        $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_number, customer_name, customer_email, customer_phone, total_now, total_full, payment_method, status, admin_notes, items_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssddssss', $customerId, $orderNumber, $customerName, $customerEmail, $customerPhone, $totalNow, $totalFull, $paymentMethod, $status, $adminNotes, $itemsJson);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order created successfully', 'data' => ['order_number' => $orderNumber, 'id' => $conn->insert_id]]);
            // Email both admin and customer so an admin-recorded order still
            // produces the same paper trail as a public checkout.
            if (!empty($customerEmail)) {
                try { notify_new_order($orderNumber, $customerName, $customerEmail, $totalNow, $paymentMethod); } catch (Exception $e) {}
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $conn->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
