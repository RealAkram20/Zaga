<?php
// ============================================================
// Zaga Technologies - Reset Password
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_guest();

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$error = '';
$success = false;
$tokenValid = false;

// Validate token
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
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    }
    $conn->close();
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!csrf_verify()) {
        $error = 'Invalid security token. Please try again.';
        $tokenValid = true; // Keep form visible
    } else {
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
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update customer password
            $stmt = $conn->prepare("UPDATE customers SET password_hash = ? WHERE email = ?");
            $stmt->bind_param('ss', $hashedPassword, $email);
            $stmt->execute();
            $stmt->close();

            // Mark token as used
            $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->close();

            $conn->close();
            $success = true;
        }
    }
}

$page_title = 'Reset Password';
$current_page = '';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-card-logo">
                <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="<?php echo safe_output(SITE_NAME); ?>">
            </div>
            <h1>Reset Password</h1>
            <p>Create a new password for your account</p>
        </div>

        <div class="auth-card-body">
            <?php if ($success): ?>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px;text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <h3 style="color:#166534;margin:0 0 8px;">Password Reset Successful</h3>
                    <p style="color:#15803d;font-size:14px;margin:0;">Your password has been updated. You can now sign in with your new password.</p>
                </div>
                <div style="text-align:center;margin-top:20px;">
                    <a href="<?php echo SITE_URL; ?>/account/login" class="btn btn-primary btn-auth">Sign In</a>
                </div>

            <?php elseif ($tokenValid): ?>
                <?php if ($error): ?>
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px;margin-bottom:16px;color:#991b1b;font-size:14px;">
                        <?php echo safe_output($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo SITE_URL; ?>/account/reset-password" class="auth-form" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo safe_output($token); ?>">
                    <input type="hidden" name="email" value="<?php echo safe_output($email); ?>">

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" placeholder="At least 6 characters" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirm New Password</label>
                        <div class="password-field">
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm your new password" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirm', this)" aria-label="Toggle password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-auth">Reset Password</button>
                </form>

            <?php else: ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:20px;text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <h3 style="color:#991b1b;margin:0 0 8px;">Invalid or Expired Link</h3>
                    <p style="color:#b91c1c;font-size:14px;margin:0;"><?php echo safe_output($error ?: 'This password reset link is invalid or has expired.'); ?></p>
                </div>
                <div style="text-align:center;margin-top:20px;">
                    <a href="<?php echo SITE_URL; ?>/account/forgot-password" class="btn btn-primary btn-auth">Request New Link</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            Remember your password? <a href="<?php echo SITE_URL; ?>/account/login">Sign in</a>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    var input = document.getElementById(fieldId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
