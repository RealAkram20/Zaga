<?php
// ============================================================
// Zaga Technologies - Forgot Password
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/mailer.php';

require_guest();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        // Global rate limit: max 5 requests per IP per hour (stored in session)
        $resetKey = 'reset_attempts';
        $resetWindow = 'reset_window';
        if (empty($_SESSION[$resetWindow]) || $_SESSION[$resetWindow] < time() - 3600) {
            $_SESSION[$resetKey] = 0;
            $_SESSION[$resetWindow] = time();
        }
        $_SESSION[$resetKey] = ($_SESSION[$resetKey] ?? 0) + 1;
        if ($_SESSION[$resetKey] > 5) {
            $error = 'Too many requests. Please try again later.';
            goto render;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $conn = getDbConnection();

            // Check if customer exists
            $stmt = $conn->prepare("SELECT id, name, email FROM customers WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $customer = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($customer && !empty($customer['email'])) {
                // Rate limit: max 3 reset requests per email per hour
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM password_reset_tokens WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $countRow = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($countRow['cnt'] >= 3) {
                    $error = 'Too many reset requests. Please wait an hour before trying again.';
                    $conn->close();
                    goto render;
                }

                // Generate token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Invalidate old tokens for this email
                $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE email = ? AND used = 0");
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->close();

                // Store new token
                $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                $stmt->bind_param('sss', $email, $hashedToken, $expiresAt);
                $stmt->execute();
                $stmt->close();

                // Build reset link
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $resetLink = $protocol . '://' . $host . SITE_URL . '/account/reset-password?token=' . $token . '&email=' . urlencode($email);

                // Send email
                $emailBody = build_reset_email($customer['name'], $resetLink);
                $result = send_email($email, 'Reset Your Password - Zaga Technologies', $emailBody);

                if (!$result['success']) {
                    $error = $result['message'];
                }
            }

            $conn->close();

            // Always show success to prevent email enumeration
            if (empty($error)) {
                $success = true;
            }
        }
    }
}

render:
$page_title = 'Forgot Password';
$current_page = '';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-card-logo">
                <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="<?php echo safe_output(SITE_NAME); ?>">
            </div>
            <h1>Forgot Password</h1>
            <p>Enter your email to receive a reset link</p>
        </div>

        <div class="auth-card-body">
            <?php if ($success): ?>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px;text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <h3 style="color:#166534;margin:0 0 8px;">Check Your Email</h3>
                    <p style="color:#15803d;font-size:14px;margin:0;">If an account exists with that email, we've sent a password reset link. Please check your inbox and spam folder.</p>
                </div>
                <div style="text-align:center;margin-top:20px;">
                    <a href="<?php echo SITE_URL; ?>/account/login" class="btn btn-primary btn-auth">Back to Login</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px;margin-bottom:16px;color:#991b1b;font-size:14px;">
                        <?php echo safe_output($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>/account/forgot-password" class="auth-form" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required autofocus
                            value="<?php echo safe_output($_POST['email'] ?? ''); ?>">
                    </div>

                    <button type="submit" class="btn btn-primary btn-auth">Send Reset Link</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            Remember your password? <a href="<?php echo SITE_URL; ?>/account/login">Sign in</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
