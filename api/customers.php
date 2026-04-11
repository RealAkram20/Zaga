<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';

$action = $_REQUEST['action'] ?? 'list';

// Cart persistence for logged-in users
if ($action === 'save_cart' || $action === 'load_cart') {
    $email = $_SESSION['user_email'] ?? $_SESSION['customer_email'] ?? '';
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    $conn = getDbConnection();

    if ($action === 'save_cart') {
        $cartJson = trim($_POST['cart_json'] ?? '[]');
        $stmt = $conn->prepare("UPDATE customers SET cart_json = ? WHERE email = ?");
        $stmt->bind_param('ss', $cartJson, $email);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'load_cart') {
        $stmt = $conn->prepare("SELECT cart_json FROM customers WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        $cart = $row && $row['cart_json'] ? $row['cart_json'] : '[]';
        echo json_encode(['success' => true, 'cart' => json_decode($cart)]);
        exit;
    }
}

// Public actions (register and login)
if ($action === 'register' || $action === 'login') {
    $conn = getDbConnection();

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? 'Uganda');

        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Name, email, and password are required']);
            $conn->close();
            exit;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            $conn->close();
            exit;
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT id, password_hash FROM customers WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            if (!empty($existing['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please log in.']);
                $conn->close();
                exit;
            }
            // Customer exists (from an order) but has no password — upgrade to full account
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, city = ?, country = ?, password_hash = ? WHERE id = ?");
            $stmt->bind_param('ssssssi', $name, $phone, $address, $city, $country, $hash, $existing['id']);
            if ($stmt->execute()) {
                $_SESSION['customer_id'] = $existing['id'];
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;
                echo json_encode(['success' => true, 'message' => 'Account created successfully', 'customer' => ['id' => $existing['id'], 'name' => $name, 'email' => $email]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed']);
            }
            $stmt->close();
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, city, country, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $name, $email, $phone, $address, $city, $country, $hash);
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                $_SESSION['customer_id'] = $newId;
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;
                echo json_encode(['success' => true, 'message' => 'Account created successfully', 'customer' => ['id' => $newId, 'name' => $name, 'email' => $email]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
            }
            $stmt->close();
        }
        $conn->close();
        exit;
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            $conn->close();
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name, email, password_hash FROM customers WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$customer || empty($customer['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }

        if (password_verify($password, $customer['password_hash'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            echo json_encode(['success' => true, 'message' => 'Login successful', 'customer' => ['id' => $customer['id'], 'name' => $customer['name'], 'email' => $customer['email']]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
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
    case 'list':
        $search = trim($_GET['search'] ?? '');
        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt = $conn->prepare("SELECT c.*, (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count FROM customers c WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? ORDER BY c.created_at DESC");
            $stmt->bind_param('sss', $like, $like, $like);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count FROM customers c ORDER BY c.created_at DESC");
        }
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $customers]);
        break;

    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($customer) {
            // Also get their orders
            $stmt2 = $conn->prepare("SELECT id, order_number, total_now, total_full, status, order_date FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $ordersResult = $stmt2->get_result();
            $orders = [];
            while ($row = $ordersResult->fetch_assoc()) {
                $orders[] = $row;
            }
            $stmt2->close();
            $customer['orders'] = $orders;
            echo json_encode(['success' => true, 'data' => $customer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
        break;

    case 'add':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? 'Uganda');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, city, country, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $name, $email, $phone, $address, $city, $country, $notes);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Customer added successfully', 'id' => $conn->insert_id]);
        } else {
            if ($conn->errno === 1062) {
                echo json_encode(['success' => false, 'message' => 'A customer with this email already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add customer: ' . $conn->error]);
            }
        }
        $stmt->close();
        break;

    case 'edit':
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? 'Uganda');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            break;
        }

        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, city = ?, country = ?, notes = ? WHERE id = ?");
        $stmt->bind_param('sssssssi', $name, $email, $phone, $address, $city, $country, $notes, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
        } else {
            if ($conn->errno === 1062) {
                echo json_encode(['success' => false, 'message' => 'A customer with this email already exists']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update customer: ' . $conn->error]);
            }
        }
        $stmt->close();
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        // Check for existing orders
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM orders WHERE customer_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => "Cannot delete: customer has $count order(s). Delete or reassign orders first."]);
            break;
        }

        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete customer']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
