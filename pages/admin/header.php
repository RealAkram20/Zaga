<?php
/**
 * Zaga Technologies - Admin Panel Header
 *
 * Include this at the top of every admin page.
 * Set $page_title and $admin_page before including.
 */
require_once __DIR__ . '/../../includes/config.php';

// Default admin page identifier
$admin_page = $admin_page ?? 'dashboard';

// Auth guard: skip for login page, enforce for everything else
if ($admin_page !== 'login' && empty($_SESSION['admin_logged_in'])) {
    redirect(SITE_URL . '/admin/login');
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$page_title = isset($page_title) ? $page_title . ' - Admin | ' . SITE_NAME : 'Admin Panel | ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo safe_output($page_title); ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/images/zz.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/base.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/pages/admin.css">
</head>
<body class="admin-body">

<?php
// Display flash messages
$flash_messages = get_flash();
if (!empty($flash_messages)): ?>
<div class="admin-flash-container" style="position:fixed;top:var(--spacing-xl);right:var(--spacing-xl);z-index:6000;display:flex;flex-direction:column;gap:var(--spacing-sm);">
    <?php foreach ($flash_messages as $flash): ?>
    <div class="admin-flash admin-flash--<?php echo safe_output($flash['type']); ?>">
        <?php echo safe_output($flash['message']); ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Toast notification container (used by admin.js) -->
<div id="admin-toast-container" class="admin-toast-container"></div>
