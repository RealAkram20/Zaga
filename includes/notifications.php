<?php
/**
 * Zaga Technologies - Email Notification System
 * Sends transactional emails to admin and customers for key events.
 */

require_once __DIR__ . '/mailer.php';

/**
 * Get admin email from database.
 */
function get_admin_email(): string {
    try {
        $conn = getDbConnection();
        $result = $conn->query("SELECT email FROM admin_users WHERE id = 1 LIMIT 1");
        $row = $result->fetch_assoc();
        $conn->close();
        return $row['email'] ?? (getenv('SMTP_FROM') ?: 'support@zagatechcredit.com');
    } catch (Exception $e) {
        return getenv('SMTP_FROM') ?: 'support@zagatechcredit.com';
    }
}

/**
 * Wrap content in a styled email template.
 */
function email_template(string $title, string $body): string {
    $year = date('Y');
    return "
    <div style=\"max-width:600px;margin:0 auto;font-family:system-ui,-apple-system,sans-serif;color:#1e293b;\">
        <div style=\"background:linear-gradient(135deg,#2563eb,#1e40af);padding:24px 20px;text-align:center;border-radius:8px 8px 0 0;\">
            <h1 style=\"color:white;margin:0;font-size:22px;\">Zaga Technologies</h1>
        </div>
        <div style=\"background:white;padding:24px 20px;border:1px solid #e2e8f0;border-top:none;\">
            <h2 style=\"color:#1e293b;font-size:18px;margin:0 0 16px;\">{$title}</h2>
            {$body}
        </div>
        <div style=\"text-align:center;padding:14px;color:#94a3b8;font-size:11px;\">
            &copy; {$year} Zaga Technologies Ltd. All rights reserved.
        </div>
    </div>";
}

// ============================================================
// NOTIFICATION FUNCTIONS
// ============================================================

/**
 * Notify admin when a new order is placed.
 */
function notify_new_order(string $orderNumber, string $customerName, string $customerEmail, float $totalNow, string $paymentMethod): void {
    $methods = [
        'cod' => 'Cash on Delivery',
        'cash_on_delivery' => 'Cash on Delivery',
        'mobile' => 'Mobile Money',
        'mobile_money' => 'Mobile Money',
        'credit' => 'Credit Plan',
        'bank_transfer' => 'Bank Transfer',
    ];
    $methodLabel = $methods[$paymentMethod] ?? ucwords(str_replace('_', ' ', $paymentMethod));
    $amount = 'UGX ' . number_format($totalNow, 0);

    // Email to admin
    $adminBody = email_template('New Order Received', "
        <p>A new order has been placed:</p>
        <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;width:140px;\">Order #</td><td style=\"padding:10px 0;\">" . htmlspecialchars($orderNumber) . "</td></tr>
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;\">Customer</td><td style=\"padding:10px 0;\">" . htmlspecialchars($customerName) . "</td></tr>
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;\">Email</td><td style=\"padding:10px 0;\">" . htmlspecialchars($customerEmail) . "</td></tr>
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;\">Amount</td><td style=\"padding:10px 0;font-weight:600;color:#059669;\">{$amount}</td></tr>
            <tr><td style=\"padding:10px 0;font-weight:600;\">Payment</td><td style=\"padding:10px 0;\">{$methodLabel}</td></tr>
        </table>
        <div style=\"text-align:center;margin-top:20px;\">
            <a href=\"" . htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . SITE_URL . '/admin/orders') . "\" style=\"display:inline-block;background:#2563eb;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;\">View Orders</a>
        </div>
    ");
    send_email(get_admin_email(), 'New Order: ' . $orderNumber, $adminBody);

    // Email to customer
    $customerBody = email_template('Order Confirmation', "
        <p>Hello <strong>" . htmlspecialchars($customerName) . "</strong>,</p>
        <p>Thank you for your order! Here are your order details:</p>
        <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;width:140px;\">Order #</td><td style=\"padding:10px 0;\">" . htmlspecialchars($orderNumber) . "</td></tr>
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;\">Amount Due</td><td style=\"padding:10px 0;font-weight:600;color:#059669;\">{$amount}</td></tr>
            <tr><td style=\"padding:10px 0;font-weight:600;\">Payment Method</td><td style=\"padding:10px 0;\">{$methodLabel}</td></tr>
        </table>
        <p style=\"color:#64748b;font-size:14px;\">We will process your order shortly. You will receive an update when your order status changes.</p>
    ");
    send_email($customerEmail, 'Order Confirmation: ' . $orderNumber, $customerBody);
}

/**
 * Notify customer when order status is updated.
 */
function notify_order_status(string $orderNumber, string $customerName, string $customerEmail, string $newStatus): void {
    $statusLabels = [
        'pending' => ['Pending', '#92400e', '#fef3c7'],
        'processing' => ['Processing', '#1e40af', '#dbeafe'],
        'completed' => ['Completed', '#166534', '#dcfce7'],
        'cancelled' => ['Cancelled', '#991b1b', '#fef2f2'],
    ];
    $label = $statusLabels[$newStatus] ?? ['Updated', '#1e293b', '#f8fafc'];

    $body = email_template('Order Status Update', "
        <p>Hello <strong>" . htmlspecialchars($customerName) . "</strong>,</p>
        <p>Your order status has been updated:</p>
        <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;width:140px;\">Order #</td><td style=\"padding:10px 0;\">" . htmlspecialchars($orderNumber) . "</td></tr>
            <tr><td style=\"padding:10px 0;font-weight:600;\">New Status</td><td style=\"padding:10px 0;\"><span style=\"background:{$label[2]};color:{$label[1]};padding:4px 14px;border-radius:20px;font-weight:600;font-size:13px;\">{$label[0]}</span></td></tr>
        </table>
        " . ($newStatus === 'completed' ? '<p style="color:#166534;font-weight:600;">Your order has been completed. Thank you for your purchase!</p>' : '') . "
        " . ($newStatus === 'cancelled' ? '<p style="color:#991b1b;">If you did not request this cancellation, please contact us immediately.</p>' : '') . "
        <p style=\"color:#64748b;font-size:14px;\">If you have any questions, contact us via WhatsApp at +256 700 706809.</p>
    ");
    send_email($customerEmail, 'Order ' . $orderNumber . ' - Status: ' . ucfirst($newStatus), $body);
}

/**
 * Send payment due reminder to customer (1 day before due date).
 */
function notify_payment_reminder(string $orderNumber, string $customerName, string $customerEmail, string $dueDate, float $amount): void {
    $formattedDate = date('F j, Y', strtotime($dueDate));
    $formattedAmount = 'UGX ' . number_format($amount, 0);

    $body = email_template('Payment Reminder', "
        <p>Hello <strong>" . htmlspecialchars($customerName) . "</strong>,</p>
        <p>This is a friendly reminder that your credit payment is due <strong>tomorrow</strong>.</p>
        <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;width:140px;\">Order #</td><td style=\"padding:10px 0;\">" . htmlspecialchars($orderNumber) . "</td></tr>
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;\">Due Date</td><td style=\"padding:10px 0;font-weight:600;color:#dc2626;\">{$formattedDate}</td></tr>
            <tr><td style=\"padding:10px 0;font-weight:600;\">Amount Due</td><td style=\"padding:10px 0;font-weight:600;color:#dc2626;\">{$formattedAmount}</td></tr>
        </table>
        <p>Please make your payment via WhatsApp or Mobile Money to avoid any late fees.</p>
        <div style=\"text-align:center;margin-top:20px;\">
            <a href=\"https://wa.me/256700706809?text=" . urlencode("Hi, I want to make a payment of {$formattedAmount} for order {$orderNumber}") . "\" style=\"display:inline-block;background:#25d366;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;\">Pay via WhatsApp</a>
        </div>
    ");
    send_email($customerEmail, 'Payment Reminder: ' . $orderNumber . ' - Due ' . $formattedDate, $body);
}

/**
 * Notify admin when a new customer registers.
 */
function notify_new_signup(string $customerName, string $customerEmail, string $phone = ''): void {
    $body = email_template('New Customer Registration', "
        <p>A new customer has registered on the website:</p>
        <table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;width:140px;\">Name</td><td style=\"padding:10px 0;\">" . htmlspecialchars($customerName) . "</td></tr>
            <tr style=\"border-bottom:1px solid #e2e8f0;\"><td style=\"padding:10px 0;font-weight:600;\">Email</td><td style=\"padding:10px 0;\">" . htmlspecialchars($customerEmail) . "</td></tr>
            " . ($phone ? "<tr><td style=\"padding:10px 0;font-weight:600;\">Phone</td><td style=\"padding:10px 0;\">" . htmlspecialchars($phone) . "</td></tr>" : "") . "
        </table>
        <div style=\"text-align:center;margin-top:20px;\">
            <a href=\"" . htmlspecialchars((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . SITE_URL . '/admin/customers') . "\" style=\"display:inline-block;background:#2563eb;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;\">View Customers</a>
        </div>
    ");
    send_email(get_admin_email(), 'New Customer: ' . $customerName, $body);
}
