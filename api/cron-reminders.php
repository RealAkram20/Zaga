<?php
/**
 * Cron Job: Send payment due reminders
 * Run daily: curl http://localhost/Zaga/api/cron-reminders.php?key=YOUR_CRON_KEY
 * Or set up a cron job: 0 8 * * * curl -s http://yoursite.com/Zaga/api/cron-reminders.php?key=YOUR_CRON_KEY
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/notifications.php';

// Simple security key to prevent unauthorized access
$cronKey = getenv('CRON_KEY') ?: 'zaga-cron-2025';
if (($_GET['key'] ?? '') !== $cronKey) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$conn = getDbConnection();
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$sent = 0;
$errors = 0;

// Find all completed orders with credit schedules
$result = $conn->query("SELECT id, order_number, customer_name, customer_email, schedule_json, payments_made_json FROM orders WHERE status = 'completed' AND schedule_json IS NOT NULL AND schedule_json != '[]'");

while ($order = $result->fetch_assoc()) {
    $schedule = json_decode($order['schedule_json'] ?? '[]', true) ?: [];

    foreach ($schedule as $entry) {
        $dueDate = $entry['date'] ?? $entry['due_date'] ?? '';
        $status = $entry['status'] ?? 'pending';

        // Send reminder if due tomorrow and not yet paid
        if ($dueDate === $tomorrow && $status !== 'paid') {
            $amount = floatval($entry['amount'] ?? $entry['payment'] ?? 0);
            try {
                notify_payment_reminder(
                    $order['order_number'],
                    $order['customer_name'],
                    $order['customer_email'],
                    $dueDate,
                    $amount
                );
                $sent++;
            } catch (Exception $e) {
                $errors++;
            }
        }
    }
}

$conn->close();

echo json_encode([
    'success' => true,
    'message' => "Reminders sent: $sent, Errors: $errors",
    'date_checked' => $tomorrow
]);
