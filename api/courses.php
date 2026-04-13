<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$action = $_REQUEST['action'] ?? 'list';
$type = $_REQUEST['type'] ?? ''; // 'digital_skilling' or 'entrepreneurship'

// Public actions
if ($action === 'list') {
    $conn = getDbConnection();

    if (!empty($type)) {
        $stmt = $conn->prepare("SELECT * FROM courses WHERE course_type = ? ORDER BY id ASC");
        $stmt->bind_param('s', $type);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query("SELECT * FROM courses ORDER BY course_type, id ASC");
    }

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $row['price'] = floatval($row['price']);
        $row['rating'] = floatval($row['rating']);
        $row['id'] = intval($row['id']);
        $row['modules'] = intval($row['modules']);
        $row['lessons'] = intval($row['lessons']);
        $row['reviews'] = intval($row['reviews']);
        $row['in_stock'] = intval($row['in_stock']);
        $row['credit_available'] = intval($row['credit_available']);
        $row['default_apr'] = floatval($row['default_apr']);
        $courses[] = $row;
    }

    if (isset($stmt)) $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'data' => $courses]);
    exit;
}

if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($course) {
        $course['price'] = floatval($course['price']);
        $course['rating'] = floatval($course['rating']);
        $course['id'] = intval($course['id']);
        echo json_encode(['success' => true, 'data' => $course]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
    }
    exit;
}

// Admin-only actions
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
csrf_verify_request();

$conn = getDbConnection();

switch ($action) {
    case 'add':
        $course_type = trim($_POST['course_type'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $modules = intval($_POST['modules'] ?? 0);
        $lessons = intval($_POST['lessons'] ?? 0);
        $level = trim($_POST['level'] ?? 'Beginner');
        $icon = trim($_POST['icon'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $rating = floatval($_POST['rating'] ?? 0);
        $reviews = intval($_POST['reviews'] ?? 0);
        $instructor = trim($_POST['instructor'] ?? '');
        $credit_available = intval($_POST['credit_available'] ?? 1);
        $default_apr = floatval($_POST['default_apr'] ?? 0);
        $credit_terms_months = trim($_POST['credit_terms_months'] ?? '3,6');

        if (empty($title) || empty($course_type)) {
            echo json_encode(['success' => false, 'message' => 'Title and course type are required']);
            break;
        }

        // Handle image upload with validation
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mimeType, $allowedTypes) && $_FILES['image_file']['size'] <= $maxSize) {
                $uploadDir = __DIR__ . '/../uploads/';
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) ? $ext : 'jpg';
                $filename = 'course_' . time() . '_' . rand(1000, 9999) . '.' . $safeExt;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                    $image = 'uploads/' . $filename;
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO courses (course_type, title, price, description, duration, modules, lessons, level, icon, image, sku, rating, reviews, instructor, credit_available, default_apr, credit_terms_months) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdssiiisssdisids',
            $course_type, $title, $price, $description, $duration,
            $modules, $lessons, $level, $icon, $image, $sku,
            $rating, $reviews, $instructor, $credit_available, $default_apr, $credit_terms_months
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course added successfully', 'id' => $conn->insert_id]);
        } else {
            error_log('Failed to add course: ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Failed to add course. Please try again.']);
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $course_type = trim($_POST['course_type'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $modules = intval($_POST['modules'] ?? 0);
        $lessons = intval($_POST['lessons'] ?? 0);
        $level = trim($_POST['level'] ?? 'Beginner');
        $icon = trim($_POST['icon'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $rating = floatval($_POST['rating'] ?? 0);
        $reviews = intval($_POST['reviews'] ?? 0);
        $instructor = trim($_POST['instructor'] ?? '');
        $credit_available = intval($_POST['credit_available'] ?? 1);
        $default_apr = floatval($_POST['default_apr'] ?? 0);
        $credit_terms_months = trim($_POST['credit_terms_months'] ?? '3,6');

        // Handle image upload with validation
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mimeType, $allowedTypes) && $_FILES['image_file']['size'] <= $maxSize) {
                $uploadDir = __DIR__ . '/../uploads/';
                $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
                $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) ? $ext : 'jpg';
                $filename = 'course_' . time() . '_' . rand(1000, 9999) . '.' . $safeExt;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                    $image = 'uploads/' . $filename;
                }
            }
        }

        $stmt = $conn->prepare("UPDATE courses SET course_type=?, title=?, price=?, description=?, duration=?, modules=?, lessons=?, level=?, icon=?, image=?, sku=?, rating=?, reviews=?, instructor=?, credit_available=?, default_apr=?, credit_terms_months=? WHERE id=?");
        $stmt->bind_param('ssdssiiisssdisidsi',
            $course_type, $title, $price, $description, $duration,
            $modules, $lessons, $level, $icon, $image, $sku,
            $rating, $reviews, $instructor, $credit_available, $default_apr, $credit_terms_months, $id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
        } else {
            error_log('Failed to update course: ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Failed to update course. Please try again.']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete course']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
