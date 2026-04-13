<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$action = $_REQUEST['action'] ?? 'list';

// Public actions
if ($action === 'list' || $action === 'get') {
    $conn = getDbConnection();

    if ($action === 'list') {
        $sql = "SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC";
        $result = $conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['features'] = json_decode($row['features'] ?? '[]', true) ?: [];
            $row['additional_images'] = json_decode($row['additional_images'] ?? '[]', true) ?: [];
            $row['price'] = floatval($row['price']);
            $row['original_price'] = $row['original_price'] ? floatval($row['original_price']) : null;
            $row['rating'] = floatval($row['rating']);
            $row['id'] = intval($row['id']);
            $row['stock'] = intval($row['stock']);
            $row['reviews'] = intval($row['reviews']);
            $row['in_stock'] = intval($row['in_stock']);
            $row['discount'] = $row['discount'] ? intval($row['discount']) : null;
            $row['inStock'] = (bool)$row['in_stock'];
            $row['creditAvailable'] = isset($row['credit_available']) ? (bool)intval($row['credit_available']) : true;
            $row['defaultAPR'] = isset($row['default_apr']) ? floatval($row['default_apr']) : 0;
            $row['creditTermsMonths'] = isset($row['credit_terms_months']) ? $row['credit_terms_months'] : '3,6';
            $products[] = $row;
        }
        $conn->close();
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    if ($action === 'get') {
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if ($product) {
            $product['features'] = json_decode($product['features'] ?? '[]', true) ?: [];
            $product['additional_images'] = json_decode($product['additional_images'] ?? '[]', true) ?: [];
            $product['price'] = floatval($product['price']);
            $product['original_price'] = $product['original_price'] ? floatval($product['original_price']) : null;
            $product['rating'] = floatval($product['rating']);
            $product['id'] = intval($product['id']);
            $product['creditAvailable'] = isset($product['credit_available']) ? (bool)intval($product['credit_available']) : true;
            $product['defaultAPR'] = isset($product['default_apr']) ? floatval($product['default_apr']) : 0;
            $product['creditTermsMonths'] = isset($product['credit_terms_months']) ? $product['credit_terms_months'] : '3,6';
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        exit;
    }
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
        $title = trim($_POST['title'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
        $discount = !empty($_POST['discount']) ? intval($_POST['discount']) : null;
        $rating = floatval($_POST['rating'] ?? 0);
        $reviews = intval($_POST['reviews'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $features = trim($_POST['features'] ?? '[]');
        $sku = trim($_POST['sku'] ?? '');
        $warranty = trim($_POST['warranty'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        $in_stock = $stock > 0 ? 1 : 0;
        $image = trim($_POST['image'] ?? '');
        $additional_images = trim($_POST['additional_images'] ?? '[]');
        $credit_available = isset($_POST['credit_available']) ? intval($_POST['credit_available']) : 1;
        $default_apr = floatval($_POST['default_apr'] ?? 0);
        $credit_terms_months = trim($_POST['credit_terms_months'] ?? '3,6');

        if (empty($title) || $category_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Title and category are required']);
            break;
        }

        // Handle image upload with validation
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['image_file']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, SVG, and WebP are allowed.']);
                break;
            }
            if ($_FILES['image_file']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
                break;
            }

            $uploadDir = __DIR__ . '/../uploads/';
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']) ? $ext : 'jpg';
            $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $safeExt;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                $image = 'uploads/' . $filename;
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (title, category_id, price, original_price, discount, rating, reviews, description, features, sku, warranty, in_stock, stock, image, additional_images, credit_available, default_apr, credit_terms_months) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siddidissssiissids',
            $title, $category_id, $price, $original_price, $discount,
            $rating, $reviews, $description, $features, $sku, $warranty,
            $in_stock, $stock, $image, $additional_images,
            $credit_available, $default_apr, $credit_terms_months
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added successfully', 'id' => $conn->insert_id]);
        } else {
            error_log('Failed to add product: ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Failed to add product. Please try again.']);
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
        $discount = !empty($_POST['discount']) ? intval($_POST['discount']) : null;
        $rating = floatval($_POST['rating'] ?? 0);
        $reviews = intval($_POST['reviews'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $features = trim($_POST['features'] ?? '[]');
        $sku = trim($_POST['sku'] ?? '');
        $warranty = trim($_POST['warranty'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        $in_stock = $stock > 0 ? 1 : 0;
        $image = trim($_POST['image'] ?? '');
        $additional_images = trim($_POST['additional_images'] ?? '[]');
        $credit_available = isset($_POST['credit_available']) ? intval($_POST['credit_available']) : 1;
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
                $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $safeExt;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                    $image = 'uploads/' . $filename;
                }
            }
        }

        $stmt = $conn->prepare("UPDATE products SET title=?, category_id=?, price=?, original_price=?, discount=?, rating=?, reviews=?, description=?, features=?, sku=?, warranty=?, in_stock=?, stock=?, image=?, additional_images=?, credit_available=?, default_apr=?, credit_terms_months=? WHERE id=?");
        $stmt->bind_param('siddidissssiissidsi',
            $title, $category_id, $price, $original_price, $discount,
            $rating, $reviews, $description, $features, $sku, $warranty,
            $in_stock, $stock, $image, $additional_images,
            $credit_available, $default_apr, $credit_terms_months, $id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            error_log('Failed to update product: ' . $conn->error); echo json_encode(['success' => false, 'message' => 'Failed to update product. Please try again.']);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
