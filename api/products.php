<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

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

        if (empty($title) || $category_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Title and category are required']);
            break;
        }

        // Handle image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                $image = 'uploads/' . $filename;
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (title, category_id, price, original_price, discount, rating, reviews, description, features, sku, warranty, in_stock, stock, image, additional_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siddiidssssisis',
            $title, $category_id, $price, $original_price, $discount,
            $rating, $reviews, $description, $features, $sku, $warranty,
            $in_stock, $stock, $image, $additional_images
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product added successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $conn->error]);
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

        // Handle image upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $filename)) {
                $image = 'uploads/' . $filename;
            }
        }

        $stmt = $conn->prepare("UPDATE products SET title=?, category_id=?, price=?, original_price=?, discount=?, rating=?, reviews=?, description=?, features=?, sku=?, warranty=?, in_stock=?, stock=?, image=?, additional_images=? WHERE id=?");
        $stmt->bind_param('siddiidssssissi',
            $title, $category_id, $price, $original_price, $discount,
            $rating, $reviews, $description, $features, $sku, $warranty,
            $in_stock, $stock, $image, $additional_images, $id
        );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update product: ' . $conn->error]);
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
