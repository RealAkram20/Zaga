<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit;
        }

        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, username, password, full_name FROM admin_users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            echo json_encode(['success' => true, 'message' => 'Login successful', 'name' => $user['full_name']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out']);
        break;

    case 'check':
        echo json_encode([
            'success' => true,
            'logged_in' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
            'name' => $_SESSION['admin_name'] ?? ''
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
