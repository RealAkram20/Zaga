<?php
// ============================================================
// Zaga Technologies - Order History Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Order History';
$current_page = '';

$user = current_user();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .order-history-container { padding: 40px 20px; }
    .order-card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
    .order-number { font-weight: 600; font-size: 16px; color: #2563eb; }
    .order-date { color: #64748b; font-size: 14px; }
    .order-status { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-completed { background: #dcfce7; color: #166534; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .order-items { margin: 10px 0; }
    .order-item-row { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
    .order-item-row:last-child { border-bottom: none; }
    .order-summary { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
    .summary-box { background: #f8fafc; padding: 12px; border-radius: 5px; }
    .summary-box h4 { margin: 0 0 8px 0; font-size: 14px; color: #64748b; }
    .summary-box p { margin: 5px 0; font-size: 13px; }
    .amount-due { color: #dc2626; font-weight: 600; font-size: 16px; }
    .amount-good { color: #16a34a; font-weight: 600; font-size: 16px; }
    .schedule-btn { margin-top: 12px; padding: 8px 15px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; }
    .schedule-btn:hover { background: #1d4ed8; }
    .schedule-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; }
    .schedule-content { background: white; padding: 25px; border-radius: 8px; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; }
    .schedule-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .schedule-table th { background: #f1f5f9; padding: 8px; text-align: left; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
    .schedule-table td { padding: 8px; border-bottom: 1px solid #f1f5f9; }
    .payment-btn { margin-top: 12px; padding: 8px 15px; background: #059669; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; }
    .payment-btn:hover { background: #047857; }
    .no-orders { text-align: center; padding: 40px; color: #64748b; }
</style>

<div class="container order-history-container">
    <h1>Order History &amp; Payment Management</h1>
    <p style="color: #64748b; margin-bottom: 20px;">View your orders, credit schedules, and make payments</p>

    <!-- Order Tracking Form -->
    <div class="order-card" id="orderTrackingSection" style="background:#f0f6ff;border:1px solid #bfdbfe;">
        <h3 style="margin:0 0 8px;color:#1e40af;">Track Your Order</h3>
        <p style="color:#64748b;font-size:14px;margin-bottom:15px;">Enter your order number and the email or phone you used at checkout.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" id="trackOrderNumber" placeholder="e.g. ORD-1712345678" class="input" style="flex:1;min-width:180px;padding:10px;">
            <input type="text" id="trackIdentifier" placeholder="Email or phone number" class="input" style="flex:1;min-width:180px;padding:10px;">
            <button onclick="trackOrder()" class="schedule-btn" style="margin:0;padding:10px 20px;">Track Order</button>
        </div>
        <div id="trackResult" style="margin-top:15px;"></div>
    </div>

    <h2 style="margin:25px 0 15px;font-size:18px;">Your Orders</h2>
    <div id="ordersContainer"></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    var isLoggedIn = <?php echo $user ? 'true' : 'false'; ?>;
    var userEmail = <?php echo $user ? json_encode($user['email']) : '""'; ?>;

    window.addEventListener('DOMContentLoaded', function () {
        updateCartCount();

        // Auto-fill tracking form from URL params
        var urlParams = new URLSearchParams(window.location.search);
        var trackParam = urlParams.get('track');
        if (trackParam) {
            document.getElementById('trackOrderNumber').value = trackParam;
            document.getElementById('trackOrderNumber').focus();
        }

        // Load orders from DB for logged-in users
        if (isLoggedIn) {
            loadUserOrders();
        } else {
            document.getElementById('ordersContainer').innerHTML = '<div class="no-orders"><p>Please log in to view your orders, or use the tracking form above.</p></div>';
        }
    });

    function trackOrder() {
        var orderNumber = document.getElementById('trackOrderNumber').value.trim();
        var identifier = document.getElementById('trackIdentifier').value.trim();
        var resultDiv = document.getElementById('trackResult');

        if (!orderNumber || !identifier) {
            resultDiv.innerHTML = '<p style="color:#dc2626;">Please enter both order number and email/phone.</p>';
            return;
        }

        resultDiv.innerHTML = '<p style="color:#64748b;">Searching...</p>';

        fetch('<?php echo SITE_URL; ?>/api/orders.php?action=track&order_number=' + encodeURIComponent(orderNumber) + '&identifier=' + encodeURIComponent(identifier))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    var o = data.data;
                    resultDiv.innerHTML = renderOrderCard(o);
                } else {
                    resultDiv.innerHTML = '<p style="color:#dc2626;">' + escapeHtml(data.message || 'Order not found. Please check your details.') + '</p>';
                }
            })
            .catch(function() {
                resultDiv.innerHTML = '<p style="color:#dc2626;">Unable to connect. Please try again later.</p>';
            });
    }

    function loadUserOrders() {
        var container = document.getElementById('ordersContainer');
        container.innerHTML = '<p style="color:#64748b;">Loading your orders...</p>';

        fetch('<?php echo SITE_URL; ?>/api/orders.php?action=customer_orders')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.length > 0) {
                    container.innerHTML = data.data.map(function(o) {
                        return renderOrderCard(o);
                    }).join('');
                } else {
                    container.innerHTML = '<div class="no-orders"><p>No orders found.</p><a href="<?php echo SITE_URL; ?>/shop" class="schedule-btn" style="text-decoration:none;display:inline-block;margin-top:10px;">Start Shopping</a></div>';
                }
            })
            .catch(function() {
                container.innerHTML = '<div class="no-orders"><p>Failed to load orders. Please try again.</p></div>';
            });
    }

    function renderOrderCard(o) {
        var statusColors = {
            pending: 'background:#fef3c7;color:#92400e;',
            processing: 'background:#dbeafe;color:#1e40af;',
            completed: 'background:#dcfce7;color:#166534;',
            cancelled: 'background:#fce7e7;color:#991b1b;'
        };
        var statusStyle = statusColors[o.status] || statusColors.pending;
        var statusLabel = o.status ? o.status.charAt(0).toUpperCase() + o.status.slice(1) : 'Pending';

        // Parse items
        var items = [];
        try { items = JSON.parse(o.items_json || '[]'); } catch (e) {}

        var itemsHtml = items.map(function(i) {
            var name = i.name || i.title || null;
            if (!name) {
                if (i.type === 'course') {
                    var course = (typeof courses !== 'undefined' && courses) ? courses.find(function(c) { return c.id === i.itemId || c.id === i.productId; }) : null;
                    if (course) { name = course.title; i.price = i.price || course.price; }
                } else {
                    var product = (typeof products !== 'undefined' && products) ? products.find(function(p) { return p.id === i.productId; }) : null;
                    if (product) { name = product.title; i.price = i.price || product.price; }
                }
            }
            name = name || 'Product #' + (i.productId || i.itemId || '?');
            var type = (i.type === 'course') ? ' (Course)' : '';
            var priceText = i.price ? 'UGX ' + Number(i.price * (i.quantity || 1)).toLocaleString() : '';
            return '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:14px;">' +
                '<span>' + escapeHtml(name) + type + ' x' + (i.quantity || 1) + '</span>' +
                '<span>' + priceText + ' — ' + (i.paymentPlan ? 'Credit (' + (i.paymentPlan.months || 3) + 'mo)' : 'Full Payment') + '</span>' +
            '</div>';
        }).join('');

        // Amount labels based on status
        var amountLabel, amountClass;
        if (o.status === 'completed') {
            amountLabel = 'Amount Paid';
            amountClass = 'amount-good';
        } else if (o.status === 'cancelled') {
            amountLabel = 'Amount (Cancelled)';
            amountClass = 'amount-due';
        } else {
            amountLabel = 'Amount Paying Now';
            amountClass = 'amount-due';
        }

        var paymentMethods = {
            'cash_on_delivery': 'Cash on Delivery',
            'mobile_money': 'Mobile Money',
            'credit': 'Credit Plan',
            'bank_transfer': 'Bank Transfer',
            'admin_assigned': 'Admin Assigned'
        };
        var methodLabel = paymentMethods[o.payment_method] || (o.payment_method || '-').replace(/_/g, ' ');

        return '<div class="order-card">' +
            '<div class="order-header">' +
                '<div>' +
                    '<div class="order-number">' + escapeHtml(o.order_number) + '</div>' +
                    '<div class="order-date">' + (o.order_date ? new Date(o.order_date).toLocaleString() : '') + '</div>' +
                '</div>' +
                '<span style="' + statusStyle + 'padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">' + statusLabel + '</span>' +
            '</div>' +
            (itemsHtml ? '<div style="margin:10px 0;"><strong style="font-size:13px;">Items:</strong>' + itemsHtml + '</div>' : '') +
            '<div class="order-summary">' +
                '<div class="summary-box">' +
                    '<h4>' + amountLabel + '</h4>' +
                    '<p class="' + amountClass + '">UGX ' + Number(o.total_now || 0).toLocaleString() + '</p>' +
                '</div>' +
                '<div class="summary-box">' +
                    '<h4>Full Total</h4>' +
                    '<p style="font-weight:600;font-size:16px;">UGX ' + Number(o.total_full || 0).toLocaleString() + '</p>' +
                '</div>' +
            '</div>' +
            '<div style="display:flex;gap:10px;margin-top:12px;font-size:13px;color:#64748b;">' +
                '<span><strong>Payment:</strong> ' + escapeHtml(methodLabel) + '</span>' +
            '</div>' +
            '<div style="margin-top:15px;padding:12px;background:#f0fdf4;border-radius:6px;text-align:center;">' +
                '<p style="margin:0 0 8px;font-size:14px;color:#166534;">Need help with your order?</p>' +
                '<a href="https://wa.me/256700706809?text=Hi,%20I%20need%20help%20with%20order%20' + encodeURIComponent(o.order_number) + '" target="_blank" style="display:inline-block;padding:8px 20px;background:#25d366;color:white;border-radius:5px;text-decoration:none;font-weight:600;font-size:14px;">Chat on WhatsApp</a>' +
            '</div>' +
        '</div>';
    }

    function navigateToPaymentPortal(orderNumber) {
        sessionStorage.setItem('paymentOrderNumber', orderNumber);
        window.location.href = '<?php echo SITE_URL; ?>/payment-portal';
    }
</script>
