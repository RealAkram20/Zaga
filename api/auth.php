<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/notifications.php';

// CSRF token generation and validation
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$action = $_REQUEST['action'] ?? '';

// ---- Login rate limiting helpers (file-based, 5 attempts / 15 min) ----
function _login_cache_file(string $key): string {
    $dir = __DIR__ . '/../storage/';
    if (!is_dir($dir)) mkdir($dir, 0700, true);
    return $dir . 'login_' . md5($key) . '.json';
}
function _login_attempts(string $key): array {
    $file = _login_cache_file($key);
    if (!file_exists($file)) return ['attempts' => 0, 'first' => time(), 'locked_until' => 0];
    return json_decode(file_get_contents($file), true) ?: ['attempts' => 0, 'first' => time(), 'locked_until' => 0];
}
function _login_record_failure(string $key): void {
    $data = _login_attempts($key);
    if (time() - $data['first'] > 900) $data = ['attempts' => 0, 'first' => time(), 'locked_until' => 0];
    $data['attempts']++;
    if ($data['attempts'] >= 5) $data['locked_until'] = time() + 900;
    file_put_contents(_login_cache_file($key), json_encode($data));
}
function _login_clear(string $key): void {
    $file = _login_cache_file($key);
    if (file_exists($file)) unlink($file);
}

switch ($action) {
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Username/email and password are required']);
            exit;
        }

        // Rate limit by IP + username
        $rlKey = ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . strtolower($username);
        $rlData = _login_attempts($rlKey);
        if (time() - $rlData['first'] > 900) $rlData = ['attempts' => 0, 'first' => time(), 'locked_until' => 0];
        if ($rlData['locked_until'] > time()) {
            $wait = ceil(($rlData['locked_until'] - time()) / 60);
            echo json_encode(['success' => false, 'message' => "Too many failed attempts. Try again in {$wait} minute(s)."]);
            exit;
        }

        $conn = getDbConnection();
        // Allow login with username OR email
        $stmt = $conn->prepare("SELECT id, username, password, full_name FROM admin_users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if ($user && password_verify($password, $user['password'])) {
            _login_clear($rlKey);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $token = generateCsrfToken();
            echo json_encode(['success' => true, 'message' => 'Login successful', 'name' => $user['full_name'], 'csrf_token' => $token]);
        } else {
            _login_record_failure($rlKey);
            $remaining = max(0, 5 - (_login_attempts($rlKey)['attempts']));
            $msg = 'Invalid username or password';
            if ($remaining <= 2 && $remaining > 0) $msg .= ". {$remaining} attempt(s) remaining before lockout.";
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out']);
        break;

    case 'check':
        $token = generateCsrfToken();
        echo json_encode([
            'success' => true,
            'logged_in' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
            'name' => $_SESSION['admin_name'] ?? '',
            'csrf_token' => $token
        ]);
        break;

    case 'csrf_token':
        echo json_encode(['success' => true, 'csrf_token' => generateCsrfToken()]);
        break;

    case 'admin_forgot_password':
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            break;
        }
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, full_name, email FROM admin_users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin) {
            // Rate limit: max 3 per hour
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM password_reset_tokens WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();

            if ($cnt < 3) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmtInvalidate = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE email = ? AND used = 0");
                $stmtInvalidate->bind_param('s', $email);
                $stmtInvalidate->execute();
                $stmtInvalidate->close();
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $email, $hashedToken, $expiresAt);
                $stmt->execute();
                $stmt->close();

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $siteUrl = defined('SITE_URL') ? SITE_URL : '/Zaga';
                $resetLink = $protocol . '://' . $host . $siteUrl . '/admin/reset-password?token=' . $token . '&email=' . urlencode($email);

                require_once __DIR__ . '/../includes/mailer.php';
                $emailBody = build_reset_email($admin['full_name'], $resetLink);
                $mailResult = send_email($email, 'Admin Password Reset - Zaga Technologies', $emailBody);
                if (!$mailResult['success']) {
                    echo json_encode(['success' => false, 'message' => 'Failed to send email: ' . $mailResult['message']]);
                    $conn->close();
                    break;
                }
            }
        }
        $conn->close();
        echo json_encode(['success' => true, 'message' => 'If that email exists, a reset link has been sent.']);
        break;

    case 'update_profile':
        if (empty($_SESSION['admin_logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            break;
        }
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (empty($fullName)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            break;
        }
        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param('ssi', $fullName, $email, $_SESSION['admin_id']);
        if ($stmt->execute()) {
            $_SESSION['admin_name'] = $fullName;
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
        $stmt->close();
        $conn->close();
        break;

    case 'change_password':
        if (empty($_SESSION['admin_logged_in'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            break;
        }
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
            break;
        }
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['admin_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row || !password_verify($currentPassword, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            $conn->close();
            break;
        }
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hashed, $_SESSION['admin_id']);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
        $stmt->close();
        $conn->close();
        break;

    case 'customer_login':
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            exit;
        }

        $conn = getDbConnection();
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
            session_regenerate_id(true);
            $_SESSION['user_id'] = $customer['id'];
            $_SESSION['user_name'] = $customer['name'];
            $_SESSION['user_email'] = $customer['email'];
            $_SESSION['user_role'] = 'customer';
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'customer' => [
                    'id' => $customer['id'],
                    'name' => $customer['name'],
                    'email' => $customer['email']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        break;

    case 'customer_register':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Name, email, and password are required']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
            exit;
        }

        $conn = getDbConnection();

        // Check if email exists
        $stmt = $conn->prepare("SELECT id, password_hash FROM customers WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            if (!empty($existing['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
                $conn->close();
                exit;
            }
            // Customer exists from order but no password - upgrade
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, password_hash = ? WHERE id = ?");
            $stmt->bind_param('sssi', $name, $phone, $hash, $existing['id']);
            if ($stmt->execute()) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $existing['id'];
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'customer';
                echo json_encode(['success' => true, 'message' => 'Account created successfully', 'customer' => ['id' => $existing['id'], 'name' => $name, 'email' => $email]]);
                try { notify_new_signup($name, $email, $phone); } catch (Exception $e) {}
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed']);
            }
            $stmt->close();
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $country = 'Uganda';
            $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, country, password_hash) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $name, $email, $phone, $country, $hash);
            if ($stmt->execute()) {
                $newId = $conn->insert_id;
                session_regenerate_id(true);
                $_SESSION['user_id'] = $newId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'customer';
                echo json_encode(['success' => true, 'message' => 'Account created successfully', 'customer' => ['id' => $newId, 'name' => $name, 'email' => $email]]);
                try { notify_new_signup($name, $email, $phone); } catch (Exception $e) {}
            } else {
                echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
            }
            $stmt->close();
        }
        $conn->close();
        break;

    case 'customer_logout':
        // Clear customer session vars
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
