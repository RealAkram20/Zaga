<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();

$products = $conn->query("SELECT COUNT(*) as cnt FROM products")->fetch_assoc()['cnt'];
$categories = $conn->query("SELECT COUNT(*) as cnt FROM categories")->fetch_assoc()['cnt'];
$digitalCourses = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE course_type = 'digital_skilling'")->fetch_assoc()['cnt'];
$entreprenCourses = $conn->query("SELECT COUNT(*) as cnt FROM courses WHERE course_type = 'entrepreneurship'")->fetch_assoc()['cnt'];
$orders = $conn->query("SELECT COUNT(*) as cnt FROM orders")->fetch_assoc()['cnt'];
$revenue = $conn->query("SELECT COALESCE(SUM(total_now), 0) as total FROM orders")->fetch_assoc()['total'];

// New V2 stats
$customers = 0;
$pendingReviews = 0;
$testimonials = 0;
$res = $conn->query("SHOW TABLES LIKE 'customers'");
if ($res && $res->num_rows > 0) {
    $customers = $conn->query("SELECT COUNT(*) as cnt FROM customers")->fetch_assoc()['cnt'];
}
$res = $conn->query("SHOW TABLES LIKE 'reviews'");
if ($res && $res->num_rows > 0) {
    $pendingReviews = $conn->query("SELECT COUNT(*) as cnt FROM reviews WHERE status = 'pending'")->fetch_assoc()['cnt'];
}
$res = $conn->query("SHOW TABLES LIKE 'testimonials'");
if ($res && $res->num_rows > 0) {
    $testimonials = $conn->query("SELECT COUNT(*) as cnt FROM testimonials WHERE status = 1")->fetch_assoc()['cnt'];
}

$conn->close();

echo json_encode([
    'success' => true,
    'data' => [
        'total_products' => intval($products),
        'total_categories' => intval($categories),
        'total_digital_courses' => intval($digitalCourses),
        'total_entrepreneurship_courses' => intval($entreprenCourses),
        'total_orders' => intval($orders),
        'total_revenue' => floatval($revenue),
        'total_customers' => intval($customers),
        'pending_reviews' => intval($pendingReviews),
        'total_testimonials' => intval($testimonials)
    ]
]);
