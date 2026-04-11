<?php
// ============================================================
// Zaga Technologies - Customer Profile Settings
// ============================================================

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_middleware.php';

require_login();

$user = current_user();
$conn = getDbConnection();

// --- Handle POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect(SITE_URL . '/account/profile');
    }

    $action = $_POST['form_action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? '');

        if (empty($name) || empty($email)) {
            set_flash('error', 'Name and email are required.');
            redirect(SITE_URL . '/account/profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Please enter a valid email address.');
            redirect(SITE_URL . '/account/profile');
        }

        // Check email uniqueness (exclude current user)
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->bind_param('si', $email, $user['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            set_flash('error', 'This email address is already used by another account.');
            $stmt->close();
            $conn->close();
            redirect(SITE_URL . '/account/profile');
        }
        $stmt->close();

        // Update profile
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, city = ?, country = ? WHERE id = ?");
        $stmt->bind_param('ssssssi', $name, $email, $phone, $address, $city, $country, $user['id']);

        if ($stmt->execute()) {
            // Update session
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
            set_flash('success', 'Profile updated successfully.');
        } else {
            set_flash('error', 'Failed to update profile. Please try again.');
        }
        $stmt->close();
        $conn->close();
        redirect(SITE_URL . '/account/profile');
    }

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            set_flash('error', 'All password fields are required.');
            redirect(SITE_URL . '/account/profile');
        }

        if (strlen($newPassword) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
            redirect(SITE_URL . '/account/profile');
        }

        if ($newPassword !== $confirmPassword) {
            set_flash('error', 'New passwords do not match.');
            redirect(SITE_URL . '/account/profile');
        }

        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM customers WHERE id = ?");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            set_flash('error', 'Current password is incorrect.');
            $conn->close();
            redirect(SITE_URL . '/account/profile');
        }

        // Update password
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET password_hash = ? WHERE id = ?");
        $stmt->bind_param('si', $hash, $user['id']);

        if ($stmt->execute()) {
            set_flash('success', 'Password changed successfully.');
        } else {
            set_flash('error', 'Failed to change password. Please try again.');
        }
        $stmt->close();
        $conn->close();
        redirect(SITE_URL . '/account/profile');
    }
}

// --- Fetch current profile data ---
$stmt = $conn->prepare("SELECT name, email, phone, address, city, country FROM customers WHERE id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$profile) {
    set_flash('error', 'Could not load profile data.');
    redirect(SITE_URL . '/account');
}

// --- Render page ---
$page_title   = 'Profile Settings';
$current_page = 'account';
$sidebar_page = 'profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="dashboard-content">
        <div class="dashboard-page-header">
            <div>
                <h1>Profile Settings</h1>
                <p>Manage your personal information and password.</p>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="dashboard-form-card">
            <div class="dashboard-form-card-header">
                <h3>Personal Information</h3>
            </div>
            <div class="dashboard-form-card-body">
                <form method="POST" action="<?php echo SITE_URL; ?>/account/profile" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="form_action" value="update_profile">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo safe_output($profile['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo safe_output($profile['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo safe_output($profile['phone']); ?>" placeholder="+256 700 000000">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo safe_output($profile['city']); ?>" placeholder="Kampala">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?php echo safe_output($profile['country']); ?>" placeholder="Uganda">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo safe_output($profile['address']); ?>" placeholder="Street address">
                        </div>
                    </div>

                    <div style="margin-top: var(--spacing-lg);">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="dashboard-form-card">
            <div class="dashboard-form-card-header">
                <h3>Change Password</h3>
            </div>
            <div class="dashboard-form-card-body">
                <form method="POST" action="<?php echo SITE_URL; ?>/account/profile" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="form_action" value="change_password">

                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <div class="password-field">
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter your current password">
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)" aria-label="Toggle password visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password <span class="required">*</span></label>
                            <div class="password-field">
                                <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="At least 6 characters">
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)" aria-label="Toggle password visibility">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)" aria-label="Toggle password visibility">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: var(--spacing-lg);">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
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
