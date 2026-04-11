<?php
// ============================================================
// Zaga Technologies - Customer Login
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_guest();

// --- Handle POST (login) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect(SITE_URL . '/account/login');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_flash('error', 'Email and password are required.');
        redirect(SITE_URL . '/account/login');
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, name, email, password_hash FROM customers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$customer || empty($customer['password_hash'])) {
        set_flash('error', 'Invalid email or password.');
        redirect(SITE_URL . '/account/login');
    }

    if (!password_verify($password, $customer['password_hash'])) {
        set_flash('error', 'Invalid email or password.');
        redirect(SITE_URL . '/account/login');
    }

    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    $_SESSION['user_id']    = $customer['id'];
    $_SESSION['user_name']  = $customer['name'];
    $_SESSION['user_email'] = $customer['email'];
    $_SESSION['user_role']  = 'customer';

    set_flash('success', 'Welcome back, ' . $customer['name'] . '!');

    // Redirect to intended page or dashboard
    $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
    if (!empty($redirect) && strpos($redirect, '/Zaga/') === 0) {
        redirect($redirect);
    }
    redirect(SITE_URL . '/account');
}

// --- Render page ---
$page_title   = 'Login';
$current_page = '';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-card-logo">
                <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="<?php echo safe_output(SITE_NAME); ?>">
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>

        <div class="auth-card-body">
            <form method="POST" action="<?php echo SITE_URL; ?>/account/login<?php echo !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="auth-form" novalidate>
                <?php echo csrf_field(); ?>
                <?php if (!empty($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?php echo safe_output($_GET['redirect']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="auth-options" style="display:flex;justify-content:space-between;align-items:center;">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a href="<?php echo SITE_URL; ?>/account/forgot-password" style="font-size:14px;color:var(--color-primary, #2563eb);">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">Sign In</button>
            </form>
        </div>

        <div class="auth-footer">
            Don't have an account? <a href="<?php echo SITE_URL; ?>/account/register">Create one</a>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    var input = document.getElementById(fieldId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
