<?php
$admin_page = 'login';
$page_title = 'Reset Password';
require_once __DIR__ . '/header.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$error = '';
$success = false;
$tokenValid = false;

if (!empty($token) && !empty($email)) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, token, expires_at FROM password_reset_tokens WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $resetRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($resetRow && password_verify($token, $resetRow['token']) && strtotime($resetRow['expires_at']) > time()) {
        $tokenValid = true;
    } else {
        $error = 'This reset link is invalid or has expired.';
    }
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
        $tokenValid = true;
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
        $tokenValid = true;
    } else {
        $conn = getDbConnection();
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE email = ?");
        $stmt->bind_param('ss', $hashed, $email);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        $success = true;
    }
}
?>

<div class="admin-login-page">
    <div class="admin-login-card">
        <div class="admin-login-logo">
            <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="Zaga Technologies">
            <h1>Reset Password</h1>
            <p>Create a new admin password</p>
        </div>

        <?php if ($success): ?>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px;text-align:center;margin:20px 0;">
                <h3 style="color:#166534;margin:0 0 8px;">Password Updated</h3>
                <p style="color:#15803d;font-size:14px;margin:0 0 16px;">Your admin password has been reset successfully.</p>
                <a href="<?php echo SITE_URL; ?>/admin/login" class="admin-btn admin-btn--primary" style="display:inline-block;">Sign In</a>
            </div>
        <?php elseif ($tokenValid): ?>
            <?php if ($error): ?>
                <div class="admin-login-error visible"><?php echo safe_output($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="<?php echo SITE_URL; ?>/admin/reset-password" class="admin-login-form">
                <input type="hidden" name="token" value="<?php echo safe_output($token); ?>">
                <input type="hidden" name="email" value="<?php echo safe_output($email); ?>">
                <div class="admin-form-group">
                    <label>New Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="At least 6 characters">
                </div>
                <div class="admin-form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirm" required minlength="6" placeholder="Confirm new password">
                </div>
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--lg" style="width:100%;">Reset Password</button>
            </form>
        <?php else: ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:20px;text-align:center;margin:20px 0;">
                <h3 style="color:#991b1b;margin:0 0 8px;">Invalid Link</h3>
                <p style="color:#b91c1c;font-size:14px;margin:0 0 16px;"><?php echo safe_output($error ?: 'This link is invalid or expired.'); ?></p>
                <a href="<?php echo SITE_URL; ?>/admin/login" class="admin-btn admin-btn--primary" style="display:inline-block;">Back to Login</a>
            </div>
        <?php endif; ?>

        <div class="admin-login-footer">
            <a href="<?php echo SITE_URL; ?>/admin/login">&larr; Back to login</a>
        </div>
    </div>
</div>
</body>
</html>
