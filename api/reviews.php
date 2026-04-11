<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$action = $_REQUEST['action'] ?? 'list';

// Public actions
if ($action === 'list') {
    $conn = getDbConnection();

    // List approved reviews for a product or course
    $itemType = trim($_GET['item_type'] ?? 'product');
    $itemId = intval($_GET['item_id'] ?? 0);

    $stmt = $conn->prepare("SELECT id, customer_name, rating, review_text, created_at FROM reviews WHERE item_type = ? AND item_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $stmt->bind_param('si', $itemType, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'data' => $reviews]);
    exit;
}

// Public review submission is disabled - reviews are admin-managed only
if ($action === 'submit') {
    echo json_encode(['success' => false, 'message' => 'Public review submission is disabled. Reviews are managed by admin.']);
    exit;
}

// Admin-only actions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

// Helper to recalculate ratings for a product/course
function recalcRating($conn, $itemType, $itemId) {
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE item_type = ? AND item_id = ? AND status = 'approved'");
    $stmt->bind_param('si', $itemType, $itemId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $avgRating = round($row['avg_rating'] ?? 0, 1);
    $reviewCount = intval($row['review_count']);

    // Whitelist table name to prevent SQL injection
    $allowedTables = ['course' => 'courses', 'product' => 'products'];
    $table = $allowedTables[$itemType] ?? null;
    if (!$table) return;
    $stmt2 = $conn->prepare("UPDATE `$table` SET rating = ?, reviews = ? WHERE id = ?");
    $stmt2->bind_param('dii', $avgRating, $reviewCount, $itemId);
    $stmt2->execute();
    $stmt2->close();
}

switch ($action) {
    case 'list_all':
        $status = trim($_GET['status'] ?? '');
        $search = trim($_GET['search'] ?? '');

        $sql = "SELECT r.*, CASE WHEN r.item_type = 'product' THEN (SELECT title FROM products WHERE id = r.item_id) ELSE (SELECT title FROM courses WHERE id = r.item_id) END as item_title FROM reviews r";
        $conditions = [];
        $params = [];
        $types = '';

        if ($status !== '') {
            $conditions[] = "r.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        if ($search !== '') {
            $like = '%' . $search . '%';
            $conditions[] = "(r.customer_name LIKE ? OR r.review_text LIKE ?)";
            $params = array_merge($params, [$like, $like]);
            $types .= 'ss';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        $sql .= " ORDER BY r.created_at DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $reviews]);
        break;

    case 'add':
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $itemType = trim($_POST['item_type'] ?? 'product');
        $itemId = intval($_POST['item_id'] ?? 0);
        $rating = floatval($_POST['rating'] ?? 5);
        $reviewText = trim($_POST['review_text'] ?? '');
        $status = trim($_POST['status'] ?? 'approved');

        if (empty($customerName) || empty($reviewText) || $itemId === 0) {
            echo json_encode(['success' => false, 'message' => 'Name, review text, and item are required']);
            break;
        }

        $rating = max(1, min(5, $rating));

        // Link to customer if exists
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
        }

        $stmt = $conn->prepare("INSERT INTO reviews (customer_id, customer_name, customer_email, item_type, item_id, rating, review_text, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssidss', $customerId, $customerName, $customerEmail, $itemType, $itemId, $rating, $reviewText, $status);

        if ($stmt->execute()) {
            if ($status === 'approved') {
                recalcRating($conn, $itemType, $itemId);
            }
            echo json_encode(['success' => true, 'message' => 'Review added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add review: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? '');
        $rating = floatval($_POST['rating'] ?? 5);
        $reviewText = trim($_POST['review_text'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');

        $rating = max(1, min(5, $rating));

        // Get old item info for recalc
        $stmt = $conn->prepare("SELECT item_type, item_id FROM reviews WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE reviews SET customer_name = ?, rating = ?, review_text = ?, status = ? WHERE id = ?");
        $stmt->bind_param('sdssi', $customerName, $rating, $reviewText, $status, $id);

        if ($stmt->execute()) {
            if ($old) {
                recalcRating($conn, $old['item_type'], $old['item_id']);
            }
            echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review']);
        }
        $stmt->close();
        break;

    case 'approve':
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'approved');

        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            break;
        }

        // Get item info for recalc
        $stmt = $conn->prepare("SELECT item_type, item_id FROM reviews WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute()) {
            if ($review) {
                recalcRating($conn, $review['item_type'], $review['item_id']);
            }
            echo json_encode(['success' => true, 'message' => 'Review status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review status']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        // Get item info for recalc before deleting
        $stmt = $conn->prepare("SELECT item_type, item_id FROM reviews WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            if ($review) {
                recalcRating($conn, $review['item_type'], $review['item_id']);
            }
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
