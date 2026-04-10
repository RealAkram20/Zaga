<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

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

$conn->close();

echo json_encode([
    'success' => true,
    'data' => [
        'total_products' => intval($products),
        'total_categories' => intval($categories),
        'total_digital_courses' => intval($digitalCourses),
        'total_entrepreneurship_courses' => intval($entreprenCourses),
        'total_orders' => intval($orders),
        'total_revenue' => floatval($revenue)
    ]
]);
