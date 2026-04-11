<?php
$admin_page = 'profile';
$page_title = 'My Profile';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT id, username, full_name, email FROM admin_users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['admin_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
    <div class="admin-content-inner">

      <div class="admin-page-header">
        <h1>My Profile</h1>
        <p>Update your account settings</p>
      </div>

      <div class="admin-form-grid" style="max-width:900px;">
        <!-- Profile Info -->
        <div class="admin-card">
          <div class="admin-card-header"><h2>Profile Information</h2></div>
          <div style="padding:20px;">
            <form id="profileForm">
              <div class="admin-form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo safe_output($admin['full_name']); ?>" required class="admin-input">
              </div>
              <div class="admin-form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo safe_output($admin['email'] ?? ''); ?>" required class="admin-input">
              </div>
              <div class="admin-form-group">
                <label>Username</label>
                <input type="text" value="<?php echo safe_output($admin['username']); ?>" disabled class="admin-input" style="background:#f1f5f9;cursor:not-allowed;">
                <small style="color:#64748b;">Username cannot be changed</small>
              </div>
              <button type="submit" class="admin-btn admin-btn-primary" style="margin-top:12px;">Save Changes</button>
            </form>
          </div>
        </div>

        <!-- Change Password -->
        <div class="admin-card">
          <div class="admin-card-header"><h2>Change Password</h2></div>
          <div style="padding:20px;">
            <form id="passwordForm">
              <div class="admin-form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required class="admin-input">
              </div>
              <div class="admin-form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6" class="admin-input">
              </div>
              <div class="admin-form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6" class="admin-input">
              </div>
              <button type="submit" class="admin-btn admin-btn-primary" style="margin-top:12px;">Update Password</button>
            </form>
          </div>
        </div>
      </div>

    </div>

<script>
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fd.append('action', 'update_profile');
    try {
        var res = await fetch('<?php echo SITE_URL; ?>/api/auth.php', { method: 'POST', body: fd });
        var json = await res.json();
        if (json.success) {
            showToast(json.message || 'Profile updated');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showToast(json.message || 'Failed to update profile', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
});

document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    if (fd.get('new_password') !== fd.get('confirm_password')) {
        showToast('Passwords do not match', 'error');
        return;
    }
    fd.append('action', 'change_password');
    try {
        var res = await fetch('<?php echo SITE_URL; ?>/api/auth.php', { method: 'POST', body: fd });
        var json = await res.json();
        if (json.success) {
            showToast(json.message || 'Password updated');
            this.reset();
        } else {
            showToast(json.message || 'Failed to update password', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    }
});
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
