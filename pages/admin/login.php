<?php
$admin_page = 'login';
$page_title = 'Admin Login';
require_once __DIR__ . '/header.php';

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    redirect(SITE_URL . '/admin');
}
?>

<div class="admin-login-page">
    <div class="admin-login-card">
        <div class="admin-login-logo">
            <img src="<?php echo SITE_URL; ?>/images/zz.png" alt="Zaga Technologies">
            <h1>Zaga Technologies</h1>
            <p>Sign in to the admin panel</p>
        </div>

        <div class="admin-login-error" id="loginError"></div>

        <form class="admin-login-form" id="adminLoginForm" autocomplete="off">
            <div class="admin-form-group">
                <label for="loginUsername">Username or Email</label>
                <input
                    type="text"
                    id="loginUsername"
                    name="username"
                    placeholder="Enter username or email"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="admin-form-group">
                <label for="loginPassword">Password</label>
                <div class="admin-password-wrapper">
                    <input
                        type="password"
                        id="loginPassword"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="admin-password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" id="eyeIcon">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div style="text-align:right;margin-bottom:12px;">
                <a href="#" id="forgotPasswordLink" style="font-size:13px;color:#2563eb;text-decoration:none;">Forgot password?</a>
            </div>

            <div class="admin-login-actions">
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--lg" id="loginSubmitBtn" style="width:100%;">
                    Sign In
                </button>
                <a href="<?php echo SITE_URL; ?>/" class="admin-btn admin-btn--secondary admin-btn--lg" style="width:100%;text-align:center;">
                    Cancel
                </a>
            </div>
        </form>

        <!-- Forgot Password Form (hidden by default) -->
        <div id="forgotPasswordSection" style="display:none;margin-top:20px;padding-top:20px;border-top:1px solid #e2e8f0;">
            <h3 style="font-size:16px;margin:0 0 8px;">Reset Password</h3>
            <p style="font-size:13px;color:#64748b;margin:0 0 12px;">Enter your admin email to receive a reset link.</p>
            <div id="forgotError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;margin-bottom:10px;color:#991b1b;font-size:13px;"></div>
            <div id="forgotSuccess" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px;margin-bottom:10px;color:#166534;font-size:13px;"></div>
            <form id="forgotPasswordForm" style="display:flex;gap:8px;">
                <input type="email" id="forgotEmail" placeholder="admin@example.com" required style="flex:1;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                <button type="submit" class="admin-btn admin-btn--primary" id="forgotSubmitBtn">Send Link</button>
            </form>
        </div>

        <div class="admin-login-footer">
            <a href="<?php echo SITE_URL; ?>/">&larr; Return to website</a>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('adminLoginForm');
    const errorBox = document.getElementById('loginError');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const passwordInput = document.getElementById('loginPassword');
    const passwordToggle = document.getElementById('passwordToggle');
    const eyeIcon = document.getElementById('eyeIcon');

    // Password visibility toggle
    passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeIcon.innerHTML = isPassword
            ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
            : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('loginUsername').value.trim();
        const password = passwordInput.value;

        if (!username || !password) {
            showError('Please enter both username and password.');
            return;
        }

        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="admin-spinner"></span> Signing in...';
        hideError();

        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', username);
        formData.append('password', password);

        fetch('<?php echo SITE_URL; ?>/api/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = '<?php echo SITE_URL; ?>/admin';
            } else {
                showError(data.message || 'Invalid username or password.');
                resetButton();
            }
        })
        .catch(function() {
            showError('A network error occurred. Please try again.');
            resetButton();
        });
    });

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.add('visible');
    }

    function hideError() {
        errorBox.classList.remove('visible');
    }

    function resetButton() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Sign In';
    }

    // Forgot password toggle
    document.getElementById('forgotPasswordLink').addEventListener('click', function(e) {
        e.preventDefault();
        var section = document.getElementById('forgotPasswordSection');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });

    // Forgot password submit
    document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var email = document.getElementById('forgotEmail').value.trim();
        var btn = document.getElementById('forgotSubmitBtn');
        var errDiv = document.getElementById('forgotError');
        var successDiv = document.getElementById('forgotSuccess');

        errDiv.style.display = 'none';
        successDiv.style.display = 'none';
        btn.disabled = true;
        btn.textContent = 'Sending...';

        var fd = new FormData();
        fd.append('action', 'admin_forgot_password');
        fd.append('email', email);

        fetch('<?php echo SITE_URL; ?>/api/auth.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    successDiv.textContent = data.message || 'Reset link sent. Check your email.';
                    successDiv.style.display = 'block';
                } else {
                    errDiv.textContent = data.message || 'Failed to send reset link.';
                    errDiv.style.display = 'block';
                }
                btn.disabled = false;
                btn.textContent = 'Send Link';
            })
            .catch(function() {
                errDiv.textContent = 'Network error. Please try again.';
                errDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Send Link';
            });
    });
})();
</script>

</body>
</html>
