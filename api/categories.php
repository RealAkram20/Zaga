<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$action = $_REQUEST['action'] ?? 'list';

// Public actions (no auth needed)
if ($action === 'list') {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $conn->close();
    echo json_encode(['success' => true, 'data' => $categories]);
    exit;
}

// Admin-only actions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

switch ($action) {
    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if ($category) {
            echo json_encode(['success' => true, 'data' => $category]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
        }
        break;

    case 'add':
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO categories (name, icon, description) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $icon, $description);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            break;
        }

        $stmt = $conn->prepare("UPDATE categories SET name = ?, icon = ?, description = ?, status = ? WHERE id = ?");
        $stmt->bind_param('sssii', $name, $icon, $description, $status, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update category: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        // Check if category has products
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM products WHERE category_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => "Cannot delete: $count product(s) are using this category. Reassign them first."]);
            break;
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
