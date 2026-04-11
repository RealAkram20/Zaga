<?php
// ============================================================
// Zaga Technologies - Payment Portal Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Payment Portal';
$current_page = '';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .payment-portal-container { padding: 40px 20px; }
    .payment-card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .payment-header h3 { margin: 0 0 15px 0; color: #1e293b; }
    .payment-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .info-box { background: #f8fafc; padding: 12px; border-radius: 5px; border-left: 4px solid #2563eb; }
    .info-label { font-size: 12px; color: #64748b; font-weight: 600; }
    .info-value { font-size: 18px; font-weight: 600; color: #1e293b; margin-top: 5px; }
    .payment-form { background: #f8fafc; padding: 20px; border-radius: 8px; margin-top: 20px; }
    .payment-form .form-group { margin-bottom: 15px; }
    .payment-form .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #1e293b; }
    .payment-form .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 14px; }
    .payment-amount-selector { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px; }
    .amount-option { padding: 12px; border: 2px solid #e2e8f0; border-radius: 5px; text-align: center; cursor: pointer; transition: all 0.3s; }
    .amount-option:hover { border-color: #2563eb; background: #f0f6ff; }
    .amount-option.selected { border-color: #2563eb; background: #dbeafe; color: #2563eb; font-weight: 600; }
    .amount-option input { display: none; }
    .pay-btn { width: 100%; padding: 12px; background: #16a34a; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; }
    .pay-btn:hover { background: #15803d; }
    .pay-btn:disabled { background: #cbd5e1; cursor: not-allowed; }
    .schedule-list { list-style: none; padding: 0; margin: 15px 0; }
    .schedule-item { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
    .schedule-item:last-child { border-bottom: none; }
    .schedule-item-paid { background: #f0fdf4; color: #166534; }
    .success-message { background: #dcfce7; border: 1px solid #86efac; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #166534; }
    .no-orders { text-align: center; padding: 40px; color: #64748b; }
</style>

<div class="container payment-portal-container">
    <h1>Payment Portal</h1>
    <p style="color: #64748b; margin-bottom: 20px;">Manage and make payments on your credit purchases</p>

    <div id="successMessage"></div>
    <div id="paymentContent"></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        updateCartCount();
        // Wait for products to be loaded before rendering payment portal
        if (window._productsLoaded) {
            initPaymentPortal();
        } else {
            window.addEventListener('products-loaded', function() {
                initPaymentPortal();
            });
        }
    });

    function initPaymentPortal() {
        var orderNumber = sessionStorage.getItem('paymentOrderNumber');
        var orders = JSON.parse(localStorage.getItem('orders') || '[]');
        var order = orders.find(function(o) { return o.orderNumber === orderNumber; });
        var container = document.getElementById('paymentContent');

        if (!order) {
            container.innerHTML = '<div class="no-orders"><p>No order found</p><a href="<?php echo SITE_URL; ?>/order-history" class="btn btn-primary">Back to Orders</a></div>';
            return;
        }

        var hasCredit = order.items && order.items.some(function(i) { return i.paymentPlan && i.paymentPlan.type === 'credit'; });
        if (!hasCredit) {
            container.innerHTML = '<div class="no-orders"><p>This order has no credit payments due</p><a href="<?php echo SITE_URL; ?>/order-history" class="btn btn-primary">Back to Orders</a></div>';
            return;
        }

        var remainingBalance = computeOrderRemainingBalance(order);
        var nextPayment = computeNextPaymentAmount(order);

        container.innerHTML =
            '<div class="payment-card">' +
                '<div class="payment-header"><h3>Order ' + escapeHtml(order.orderNumber) + '</h3></div>' +
                '<div class="payment-info">' +
                    '<div class="info-box">' +
                        '<div class="info-label">Total Outstanding Balance</div>' +
                        '<div class="info-value">UGX ' + remainingBalance.toFixed(2) + '</div>' +
                    '</div>' +
                    '<div class="info-box">' +
                        '<div class="info-label">Next Payment Due</div>' +
                        '<div class="info-value">UGX ' + nextPayment.toFixed(2) + '</div>' +
                    '</div>' +
                '</div>' +
                '<h4 style="margin-top: 25px; color: #1e293b;">Payment Schedule</h4>' +
                '<ul class="schedule-list" id="scheduleList"></ul>' +
                '<div class="payment-form">' +
                    '<h4 style="margin: 0 0 15px 0; color: #1e293b;">Make a Payment</h4>' +
                    '<div class="payment-amount-selector" id="amountSelector"></div>' +
                    '<div class="form-group">' +
                        '<label>Custom Amount (Optional)</label>' +
                        '<input type="number" id="customAmount" placeholder="Enter amount" step="0.01">' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>Mobile Money Number</label>' +
                        '<input type="tel" id="mobileNumber" placeholder="07XXXXXXXX" maxlength="10" pattern="0[3-9][0-9]{8}">' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>Account Name</label>' +
                        '<input type="text" id="accountName" placeholder="Enter name on account">' +
                    '</div>' +
                    '<button class="pay-btn" onclick="processPayment(\'' + escapeHtml(order.orderNumber) + '\')">Process Payment</button>' +
                    '<a href="<?php echo SITE_URL; ?>/order-history" class="btn btn-secondary" style="display: block; margin-top: 10px; text-align: center; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; text-decoration: none; color: #1e293b; font-weight: 600;">Back to Orders</a>' +
                '</div>' +
            '</div>';

        populateSchedule(order);
        populateAmountSelector(order, nextPayment);
    }

    function populateSchedule(order) {
        var scheduleHtml = '';
        var paymentCount = 0;

        if (order.schedule && order.schedule.length > 0) {
            order.schedule.forEach(function(payment, idx) {
                var isPaid = payment.paid || false;
                scheduleHtml +=
                    '<li class="schedule-item ' + (isPaid ? 'schedule-item-paid' : '') + '">' +
                        '<div>Payment ' + (idx + 1) + '</div>' +
                        '<div>UGX ' + payment.amount.toFixed(2) + '</div>' +
                        '<div>' + escapeHtml(payment.dueDate) + '</div>' +
                        '<div>' + (isPaid ? '&#10003; Paid' : 'Pending') + '</div>' +
                    '</li>';
            });
        } else {
            (order.items || []).forEach(function(item) {
                if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                    var product = products.find(function(p) { return p.id === item.productId; });
                    if (product) {
                        var c = computeCredit(product.price * item.quantity, item.paymentPlan.months || 3, item.paymentPlan.interestRate || 0);
                        var startDate = new Date(order.date);

                        for (var m = 0; m < c.months; m++) {
                            var dueDate = new Date(startDate);
                            dueDate.setMonth(dueDate.getMonth() + m + 1);
                            var isPaid = (order.paymentsMade || []).some(function(p) { return p.monthIndex === paymentCount; });
                            var dateStr = dueDate.toLocaleDateString();

                            scheduleHtml +=
                                '<li class="schedule-item ' + (isPaid ? 'schedule-item-paid' : '') + '">' +
                                    '<div>Payment ' + (paymentCount + 1) + ' of ' + c.months + '</div>' +
                                    '<div>UGX ' + c.monthly.toFixed(2) + '</div>' +
                                    '<div>' + dateStr + '</div>' +
                                    '<div>' + (isPaid ? '&#10003; Paid' : 'Pending') + '</div>' +
                                '</li>';
                            paymentCount++;
                        }
                    }
                }
            });
        }

        document.getElementById('scheduleList').innerHTML = scheduleHtml;
    }

    function populateAmountSelector(order, nextPayment) {
        var remainingBalance = computeOrderRemainingBalance(order);
        var selector = document.getElementById('amountSelector');

        selector.innerHTML =
            '<label class="amount-option selected">' +
                '<input type="radio" name="amount" value="' + nextPayment.toFixed(2) + '" onchange="selectAmount(this.value)" checked>' +
                '<div>Next Payment</div>' +
                '<div style="font-size: 16px; margin-top: 5px;">UGX ' + nextPayment.toFixed(2) + '</div>' +
            '</label>' +
            '<label class="amount-option">' +
                '<input type="radio" name="amount" value="' + (nextPayment * 2).toFixed(2) + '" onchange="selectAmount(this.value)">' +
                '<div>Double Payment</div>' +
                '<div style="font-size: 16px; margin-top: 5px;">UGX ' + (nextPayment * 2).toFixed(2) + '</div>' +
            '</label>' +
            '<label class="amount-option">' +
                '<input type="radio" name="amount" value="' + remainingBalance.toFixed(2) + '" onchange="selectAmount(this.value)">' +
                '<div>Full Balance</div>' +
                '<div style="font-size: 16px; margin-top: 5px;">UGX ' + remainingBalance.toFixed(2) + '</div>' +
            '</label>';
    }

    function selectAmount(amount) {
        document.querySelectorAll('.amount-option').forEach(function(opt) { opt.classList.remove('selected'); });
        if (event && event.target) {
            event.target.closest('.amount-option').classList.add('selected');
        }
        document.getElementById('customAmount').value = '';
    }

    function processPayment(orderNumber) {
        var selectedRadio = document.querySelector('input[name="amount"]:checked');
        var selectedAmount = selectedRadio ? selectedRadio.value : document.getElementById('customAmount').value;
        var mobileNumber = document.getElementById('mobileNumber').value.trim();
        var accountName = document.getElementById('accountName').value.trim();

        if (!selectedAmount || parseFloat(selectedAmount) <= 0) {
            showToast('Please select or enter a valid payment amount', 'warning');
            return;
        }
        if (!mobileNumber || !/^0[3-9]\d{8}$/.test(mobileNumber)) {
            showToast('Please enter a valid mobile number', 'error');
            return;
        }
        if (!accountName || accountName.length < 2) {
            showToast('Please enter the account holder name', 'warning');
            return;
        }

        var orders = JSON.parse(localStorage.getItem('orders') || '[]');
        var orderIdx = orders.findIndex(function(o) { return o.orderNumber === orderNumber; });

        if (orderIdx !== -1) {
            if (!orders[orderIdx].paymentsMade) {
                orders[orderIdx].paymentsMade = [];
            }

            orders[orderIdx].paymentsMade.push({
                date: new Date().toLocaleDateString(),
                amount: parseFloat(selectedAmount)
            });

            localStorage.setItem('orders', JSON.stringify(orders));

            var successDiv = document.getElementById('successMessage');
            successDiv.innerHTML =
                '<div class="success-message">' +
                    '<strong>&#10003; Payment Successful!</strong><br>' +
                    'Amount: UGX ' + parseFloat(selectedAmount).toFixed(2) + '<br>' +
                    'Transaction Date: ' + new Date().toLocaleDateString() + '<br>' +
                    '<a href="<?php echo SITE_URL; ?>/order-history" style="color: #166534; text-decoration: underline;">View all orders</a>' +
                '</div>';
            successDiv.scrollIntoView({ behavior: 'smooth' });

            setTimeout(function() {
                initPaymentPortal();
            }, 1500);
        }
    }

    function computeOrderRemainingBalance(order) {
        if (!order.items) return 0;
        var balance = (order.items || []).reduce(function(sum, item) {
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var product = products.find(function(p) { return p.id === item.productId; });
                if (product) {
                    var c = computeCredit(product.price * item.quantity, item.paymentPlan.months || 3);
                    return sum + (c.remaining || 0);
                }
            }
            return sum;
        }, 0);

        (order.paymentsMade || []).forEach(function(payment) {
            balance -= payment.amount;
        });

        return Math.max(0, balance);
    }

    function computeNextPaymentAmount(order) {
        if (!order.items) return 0;
        return (order.items || []).reduce(function(sum, item) {
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var product = products.find(function(p) { return p.id === item.productId; });
                if (product) {
                    var c = computeCredit(product.price * item.quantity, item.paymentPlan.months || 3);
                    return sum + (c.monthly || 0);
                }
            }
            return sum;
        }, 0);
    }
</script>
