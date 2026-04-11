<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$action = $_REQUEST['action'] ?? 'list';

// Public actions
if ($action === 'list') {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY display_order ASC, created_at ASC");
    $testimonials = [];
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
    $conn->close();
    echo json_encode(['success' => true, 'data' => $testimonials]);
    exit;
}

// Admin-only actions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

switch ($action) {
    case 'list_all':
        $result = $conn->query("SELECT * FROM testimonials ORDER BY display_order ASC, created_at ASC");
        $testimonials = [];
        while ($row = $result->fetch_assoc()) {
            $testimonials[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $testimonials]);
        break;

    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $testimonial = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($testimonial) {
            echo json_encode(['success' => true, 'data' => $testimonial]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Testimonial not found']);
        }
        break;

    case 'add':
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating = intval($_POST['rating'] ?? 5);
        $status = intval($_POST['status'] ?? 1);
        $displayOrder = intval($_POST['display_order'] ?? 0);

        // Handle image file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($mime, $allowed) && $_FILES['image_file']['size'] <= 5 * 1024 * 1024) {
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $ext = 'jpg';
                $filename = 'testimonial_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = __DIR__ . '/../uploads/' . $filename;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    $image = 'uploads/' . $filename;
                }
            }
        }

        if (empty($name) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Name and content are required']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO testimonials (name, role, image, content, rating, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssiis', $name, $role, $image, $content, $rating, $status, $displayOrder);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Testimonial added successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add testimonial: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating = intval($_POST['rating'] ?? 5);
        $status = intval($_POST['status'] ?? 1);
        $displayOrder = intval($_POST['display_order'] ?? 0);

        // Handle image file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($mime, $allowed) && $_FILES['image_file']['size'] <= 5 * 1024 * 1024) {
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $ext = 'jpg';
                $filename = 'testimonial_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $dest = __DIR__ . '/../uploads/' . $filename;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest)) {
                    $image = 'uploads/' . $filename;
                }
            }
        }

        if (empty($name) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Name and content are required']);
            break;
        }

        $stmt = $conn->prepare("UPDATE testimonials SET name = ?, role = ?, image = ?, content = ?, rating = ?, status = ?, display_order = ? WHERE id = ?");
        $stmt->bind_param('ssssiiii', $name, $role, $image, $content, $rating, $status, $displayOrder, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Testimonial updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update testimonial']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Testimonial deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete testimonial']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
