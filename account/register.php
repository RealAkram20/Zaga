<?php
// ============================================================
// Zaga Technologies - Customer Registration
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';
require_once __DIR__ . '/../includes/notifications.php';

require_guest();

// --- Handle POST (register) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect(SITE_URL . '/account/register');
    }

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        foreach ($errors as $err) {
            set_flash('error', $err);
        }
        redirect(SITE_URL . '/account/register');
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
            set_flash('error', 'An account with this email already exists. Please log in.');
            $conn->close();
            redirect(SITE_URL . '/account/login');
        }

        // Customer exists from a previous order but has no password - upgrade to full account
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, password_hash = ? WHERE id = ?");
        $stmt->bind_param('sssi', $name, $phone, $hash, $existing['id']);
        $stmt->execute();
        $stmt->close();

        $customerId = $existing['id'];
    } else {
        // Create new customer
        $hash    = password_hash($password, PASSWORD_DEFAULT);
        $country = 'Uganda';
        $stmt    = $conn->prepare("INSERT INTO customers (name, email, phone, country, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $email, $phone, $country, $hash);

        if (!$stmt->execute()) {
            set_flash('error', 'Registration failed. Please try again.');
            $stmt->close();
            $conn->close();
            redirect(SITE_URL . '/account/register');
        }

        $customerId = $conn->insert_id;
        $stmt->close();
    }

    $conn->close();

    // Set session
    session_regenerate_id(true);
    $_SESSION['user_id']    = $customerId;
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role']  = 'customer';

    set_flash('success', 'Account created successfully! Welcome, ' . $name . '.');
    try { notify_new_signup($name, $email, $phone); } catch (Exception $e) {}
    redirect(SITE_URL . '/account');
}

// --- Render page ---
$page_title   = 'Create Account';
$current_page = '';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-card-logo">
                <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="<?php echo safe_output(SITE_NAME); ?>">
            </div>
            <h1>Create Account</h1>
            <p>Join <?php echo safe_output(SITE_NAME); ?> today</p>
        </div>

        <div class="auth-card-body">
            <form method="POST" action="<?php echo SITE_URL; ?>/account/register" class="auth-form" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+256 700 000000">
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar"><div class="password-strength-fill" id="strengthFill"></div></div>
                        <span class="password-strength-label" id="strengthLabel"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                    <div class="password-field">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Re-enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirm', this)" aria-label="Toggle password visibility">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-auth">Create Account</button>
            </form>
        </div>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo SITE_URL; ?>/account/login">Sign in</a>
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

// Password strength meter
document.getElementById('password').addEventListener('input', function() {
    var val = this.value;
    var fill = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');

    if (val.length === 0) {
        fill.className = 'password-strength-fill';
        label.textContent = '';
        return;
    }

    var score = 0;
    if (val.length >= 6) score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    if (score <= 1) {
        fill.className = 'password-strength-fill weak';
        label.textContent = 'Weak';
    } else if (score <= 2) {
        fill.className = 'password-strength-fill fair';
        label.textContent = 'Fair';
    } else if (score <= 3) {
        fill.className = 'password-strength-fill good';
        label.textContent = 'Good';
    } else {
        fill.className = 'password-strength-fill strong';
        label.textContent = 'Strong';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
