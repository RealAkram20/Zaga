<?php
// ============================================================
// Zaga Technologies - Checkout Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Checkout';
$current_page = '';

$user = current_user();

// Fetch full customer profile for auto-fill if logged in
$customer_profile = null;
if ($user) {
    require_once __DIR__ . '/../config/database.php';
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT phone, address, city, country FROM customers WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $user['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer_profile = $result->fetch_assoc();
        $stmt->close();
    }
    $conn->close();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container checkout-container">
    <h1>Checkout</h1>

    <div class="checkout-content">
        <!-- Checkout Form -->
        <div class="checkout-form-section">
            <form id="checkoutForm" class="checkout-form">
                <!-- Shipping Information -->
                <fieldset class="form-section">
                    <legend>Shipping Address</legend>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" id="fullName" required class="input"
                            <?php if ($user): ?>value="<?php echo safe_output($user['name']); ?>"<?php endif; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" id="email" required class="input"
                                <?php if ($user): ?>value="<?php echo safe_output($user['email']); ?>"<?php endif; ?>>
                        </div>
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="tel" id="phone" required class="input"
                                <?php if ($customer_profile && $customer_profile['phone']): ?>value="<?php echo safe_output($customer_profile['phone']); ?>"<?php endif; ?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Street Address *</label>
                        <input type="text" id="address" required class="input"
                            <?php if ($customer_profile && $customer_profile['address']): ?>value="<?php echo safe_output($customer_profile['address']); ?>"<?php endif; ?>>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>City *</label>
                            <input type="text" id="city" required class="input"
                                <?php if ($customer_profile && $customer_profile['city']): ?>value="<?php echo safe_output($customer_profile['city']); ?>"<?php endif; ?>>
                        </div>
                        <div class="form-group">
                            <label>Country *</label>
                            <input type="text" id="country" required class="input"
                                value="<?php echo ($customer_profile && $customer_profile['country']) ? safe_output($customer_profile['country']) : 'Uganda'; ?>">
                        </div>
                    </div>
                </fieldset>

                <?php if (!is_logged_in()): ?>
                <!-- Create Account (Optional) - only show if not logged in -->
                <fieldset class="form-section" id="accountSection">
                    <legend>Create an Account (Optional)</legend>
                    <p style="color:#64748b;font-size:13px;margin-bottom:12px;">Create an account to track your orders and manage your purchases.</p>
                    <label class="checkbox-label" style="margin-bottom:12px;display:block;">
                        <input type="checkbox" id="createAccount"> I'd like to create an account
                    </label>
                    <div id="accountFields" style="display:none;">
                        <div class="form-group">
                            <label>Password *</label>
                            <div style="position:relative;">
                                <input type="password" id="accountPassword" class="input" minlength="6" placeholder="At least 6 characters" style="padding-right:42px;">
                                <button type="button" onclick="togglePasswordVisibility(this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:18px;" aria-label="Show password">&#x1F441;</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password *</label>
                            <div style="position:relative;">
                                <input type="password" id="accountPasswordConfirm" class="input" minlength="6" placeholder="Confirm your password" style="padding-right:42px;">
                                <button type="button" onclick="togglePasswordVisibility(this)" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:18px;" aria-label="Show password">&#x1F441;</button>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <?php endif; ?>

                <!-- Shipping Method -->
                <fieldset class="form-section">
                    <legend>Shipping Method</legend>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="shipping" value="free" checked> Free Shipping (5-7 business days)
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="shipping" value="express"> Express Shipping (+UGX 15, 2-3 business days)
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="shipping" value="overnight"> Overnight Shipping (+UGX 30, Next day)
                        </label>
                    </div>
                </fieldset>

                <!-- Payment Method -->
                <fieldset class="form-section">
                    <legend>Payment Method</legend>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="paymentMethod" value="cod" checked> Cash on Delivery
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="paymentMethod" value="mobile"> Mobile Money Deposit
                        </label>
                    </div>
                </fieldset>

                <!-- Mobile Money Details -->
                <fieldset class="form-section" id="cardSection" style="display:none;">
                    <legend>Mobile Money Details</legend>
                    <div class="form-group">
                        <label>Mobile Money Number *</label>
                        <input type="tel" id="mmNumber" placeholder="e.g., 077XXXXXXX" class="input">
                    </div>
                    <div class="form-group">
                        <label>Deposit Amount (if applicable)</label>
                        <input type="text" id="mmDeposit" class="input" readonly placeholder="Auto-calculated from cart">
                        <small style="color:#64748b; display:block; margin-top:6px;">If your cart includes credit purchases, we'll collect the deposit via Mobile Money.</small>
                    </div>
                </fieldset>

                <!-- Order Terms -->
                <fieldset class="form-section">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="agreeTerms" required> I agree to the Terms and Conditions
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="agreePrivacy"> I agree to the Privacy Policy
                        </label>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; padding: 15px; font-size: 16px; margin-top: 20px;">Complete Purchase</button>
            </form>
        </div>

        <!-- Order Summary Sidebar -->
        <aside class="checkout-summary">
            <h2>Order Summary</h2>
            <div id="orderItems" class="order-items"></div>

            <div class="summary-divider"></div>

            <div class="summary-item">
                <span>Subtotal:</span>
                <span id="checkoutSubtotal">UGX 0.00</span>
            </div>
            <div class="summary-item">
                <span>Shipping:</span>
                <span id="checkoutShipping">Free</span>
            </div>
            <div class="summary-item">
                <span>VAT (18%):</span>
                <span id="checkoutTax">UGX 0.00</span>
            </div>
            <div class="summary-item total">
                <span>Total:</span>
                <span id="checkoutTotal">UGX 0.00</span>
            </div>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        // Redirect if cart is empty (e.g. user pressed Back after ordering)
        var cart = getCart();
        if (cart.length === 0) {
            window.location.replace('<?php echo SITE_URL; ?>/shop');
            return;
        }

        // Wait for products to load before displaying summary
        if (window._productsLoaded) {
            displayCheckoutSummary();
        } else {
            window.addEventListener('products-loaded', function() {
                displayCheckoutSummary();
            });
        }
        setupCheckoutForm();
        updateCartCount();

        // Toggle create account fields
        var createAccountCb = document.getElementById('createAccount');
        if (createAccountCb) {
            createAccountCb.addEventListener('change', function () {
                document.getElementById('accountFields').style.display = this.checked ? 'block' : 'none';
            });
        }
    });

    function togglePasswordVisibility(btn) {
        var input = btn.parentElement.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }

    function displayCheckoutSummary() {
        var cart = getCart();
        var itemsContainer = document.getElementById('orderItems');

        itemsContainer.innerHTML = cart.map(function(item) {
            var itemData;
            if (item.type === 'course') {
                itemData = item.courseData || courses.find(function(c) { return c.id === item.itemId; });
            } else {
                itemData = products.find(function(p) { return p.id === item.productId; });
            }
            if (!itemData) return '';
            var right = (itemData.price * item.quantity).toFixed(2);
            var extra = '';
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(itemData.price * item.quantity, item.paymentPlan.months || 3, interestRate);
                extra = ' <div style="color:#065f46;font-weight:600;">Deposit UGX ' + c.deposit.toFixed(2) + ' — UGX ' + c.monthly.toFixed(2) + '/mo x ' + c.months + '</div>';
                right = 'UGX ' + (itemData.price * item.quantity).toFixed(2) + ' (credit)';
            } else {
                right = 'UGX ' + (itemData.price * item.quantity).toFixed(2);
            }

            var label = item.type === 'course' ? escapeHtml(itemData.title) + ' (Course)' : escapeHtml(itemData.title);

            return '<div class="order-item">' +
                '<span>' + label + ' x' + item.quantity + extra + '</span>' +
                '<span>' + right + '</span>' +
            '</div>';
        }).join('');

        updateCheckoutSummary(cart);
    }

    function updateCheckoutSummary(cart) {
        var subtotalNow = cart.reduce(function(sum, item) {
            var itemData;
            if (item.type === 'course') {
                itemData = item.courseData || courses.find(function(c) { return c.id === item.itemId; });
            } else {
                itemData = products.find(function(p) { return p.id === item.productId; });
            }
            if (!itemData) return sum;
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(itemData.price * item.quantity, item.paymentPlan.months || 3, interestRate);
                return sum + c.deposit;
            }
            return sum + (itemData.price * item.quantity);
        }, 0);

        var remainingBalance = cart.reduce(function(sum, item) {
            var itemData;
            if (item.type === 'course') {
                itemData = item.courseData || courses.find(function(c) { return c.id === item.itemId; });
            } else {
                itemData = products.find(function(p) { return p.id === item.productId; });
            }
            if (!itemData) return sum;
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(itemData.price * item.quantity, item.paymentPlan.months || 3, interestRate);
                return sum + c.remaining;
            }
            return sum;
        }, 0);

        var shippingCost = 0;
        var tax = subtotalNow * 0.18;
        var totalNow = subtotalNow + shippingCost + tax;

        document.getElementById('checkoutSubtotal').textContent = 'UGX ' + subtotalNow.toFixed(2);
        document.getElementById('checkoutShipping').textContent = shippingCost === 0 ? 'Free' : 'UGX ' + shippingCost;
        document.getElementById('checkoutTax').textContent = 'UGX ' + tax.toFixed(2);
        document.getElementById('checkoutTotal').textContent = 'UGX ' + totalNow.toFixed(2);

        // show remaining balance if any
        var existing = document.getElementById('checkoutRemaining');
        if (existing) existing.remove();
        if (remainingBalance > 0) {
            var node = document.createElement('div');
            node.id = 'checkoutRemaining';
            node.style.marginTop = '10px';
            node.style.fontSize = '14px';
            node.style.color = '#374151';
            node.innerHTML = 'Remaining to be paid over time: <strong>UGX ' + remainingBalance.toFixed(2) + '</strong>';
            document.querySelector('.checkout-summary').appendChild(node);
        }
    }

    function setupCheckoutForm() {
        var form = document.getElementById('checkoutForm');

        // Handle shipping option changes
        document.querySelectorAll('input[name="shipping"]').forEach(function(radio) {
            radio.addEventListener('change', function () {
                var cart = getCart();
                updateCheckoutSummary(cart);
            });
        });

        // Toggle payment sections based on method
        var paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
        var cardSection = document.getElementById('cardSection');
        function updatePaymentVisibility() {
            var selected = document.querySelector('input[name="paymentMethod"]:checked').value;
            if (selected === 'cod') {
                cardSection.style.display = 'none';
            } else if (selected === 'mobile') {
                cardSection.style.display = 'block';
                // Pre-fill deposit if any
                var cart = getCart();
                var deposit = cart.reduce(function(sum, item) {
                    var itemData;
                    if (item.type === 'course') {
                        itemData = item.courseData || courses.find(function(c) { return c.id === item.itemId; });
                    } else {
                        itemData = products.find(function(p) { return p.id === item.productId; });
                    }
                    if (!itemData) return sum;
                    if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                        var interestRate = item.paymentPlan.interestRate || 0;
                        var c = computeCredit(itemData.price * item.quantity, item.paymentPlan.months || 3, interestRate);
                        return sum + c.deposit;
                    }
                    return sum;
                }, 0);
                var mmDeposit = document.getElementById('mmDeposit');
                mmDeposit.value = deposit > 0 ? 'UGX ' + deposit.toFixed(2) : 'UGX 0.00';
            }
        }
        paymentRadios.forEach(function(r) { r.addEventListener('change', updatePaymentVisibility); });
        updatePaymentVisibility();

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!validateCheckoutForm()) return;
            processOrder();
        });
    }

    function validateCheckoutForm() {
        // Validate account creation if opted in
        var createAccountCb = document.getElementById('createAccount');
        if (createAccountCb && createAccountCb.checked) {
            var pw = document.getElementById('accountPassword').value;
            var pwConfirm = document.getElementById('accountPasswordConfirm').value;
            if (pw.length < 6) {
                showToast('Password must be at least 6 characters.', 'error');
                return false;
            }
            if (pw !== pwConfirm) {
                showToast('Passwords do not match.', 'error');
                return false;
            }
        }

        var method = document.querySelector('input[name="paymentMethod"]:checked').value;
        if (method === 'cod') return true;

        if (method === 'mobile') {
            var mmNumber = document.getElementById('mmNumber').value.trim();
            if (!/^0[3-9]\d{8}$/.test(mmNumber)) {
                showToast('Please enter a valid Mobile Money number (e.g. 07XXXXXXXX)', 'error');
                return false;
            }
            return true;
        }

        return true;
    }

    function getItemPrice(item) {
        if (item.type === 'course') {
            var cd = item.courseData;
            return cd ? cd.price : 0;
        }
        var product = products.find(function(p) { return p.id === item.productId; });
        return product ? product.price : 0;
    }

    function processOrder() {
        var cart = getCart();
        var customerName = document.getElementById('fullName').value;
        var email = document.getElementById('email').value;
        var phone = document.getElementById('phone').value.trim();
        var submitBtn = document.querySelector('#checkoutForm button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        var subtotalNow = cart.reduce(function(sum, item) {
            var price = getItemPrice(item);
            if (!price) return sum;
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(price * item.quantity, item.paymentPlan.months || 3, interestRate);
                return sum + c.deposit;
            }
            return sum + (price * item.quantity);
        }, 0);
        var tax = subtotalNow * 0.18;
        var totalNow = subtotalNow + tax;

        var paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        var mmNumber = document.getElementById('mmNumber').value.trim();

        // Build payment schedule for credit items with due dates
        var schedule = [];
        var now = new Date();
        cart.forEach(function(item) {
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var price = getItemPrice(item);
                if (price) {
                    var interestRate = item.paymentPlan.interestRate || 0;
                    var months = item.paymentPlan.months || 3;
                    var c = computeCredit(price * item.quantity, months, interestRate);
                    for (var m = 0; m < months; m++) {
                        var due = new Date(now);
                        due.setMonth(due.getMonth() + m + 1);
                        schedule.push({
                            date: due.toISOString().split('T')[0],
                            amount: c.monthly,
                            status: 'pending'
                        });
                    }
                }
            }
        });

        var fullTotal = cart.reduce(function(sum, item) {
            var price = getItemPrice(item);
            return price ? sum + (price * item.quantity) : sum;
        }, 0);

        // DB-first: Save order to database, then clear cart and redirect
        var dbFd = new FormData();
        dbFd.append('action', 'create');
        dbFd.append('customer_name', customerName);
        dbFd.append('customer_email', email);
        dbFd.append('customer_phone', phone);
        dbFd.append('shipping_address', JSON.stringify({
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            country: document.getElementById('country').value
        }));
        dbFd.append('total_now', totalNow.toFixed(2));
        dbFd.append('total_full', (fullTotal * 1.18).toFixed(2));
        dbFd.append('payment_method', paymentMethod);
        // Enrich cart items with names and prices for self-contained order record
        var enrichedItems = cart.map(function(item) {
            var copy = Object.assign({}, item);
            if (item.type === 'course') {
                var cd = item.courseData || (typeof courses !== 'undefined' ? courses.find(function(c) { return c.id === item.itemId; }) : null);
                if (cd) { copy.title = cd.title; copy.price = cd.price; }
            } else {
                var pd = products.find(function(p) { return p.id === item.productId; });
                if (pd) { copy.title = pd.title; copy.price = pd.price; }
            }
            return copy;
        });
        dbFd.append('items_json', JSON.stringify(enrichedItems));
        dbFd.append('schedule_json', JSON.stringify(schedule));

        fetch('<?php echo SITE_URL; ?>/api/orders.php', { method: 'POST', body: dbFd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    showToast('Failed to place order: ' + (data.message || 'Unknown error'), 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Purchase';
                    return;
                }

                var orderNumber = data.order_number || data.data?.order_number || 'ORD-' + Date.now();

                // Also save to localStorage as backup
                var orderMeta = {
                    customer: customerName,
                    email: email,
                    items: cart,
                    totalNow: Number(totalNow.toFixed(2)),
                    tax: Number(tax.toFixed(2)),
                    date: new Date().toLocaleString(),
                    paymentMethod: paymentMethod,
                    mobileMoneyNumber: paymentMethod === 'mobile' ? mmNumber : null
                };
                var order = placeOrder(orderMeta);

                // Register account if opted in
                var createAccountCb = document.getElementById('createAccount');
                if (createAccountCb && createAccountCb.checked) {
                    var regFd = new FormData();
                    regFd.append('action', 'register');
                    regFd.append('name', customerName);
                    regFd.append('email', email);
                    regFd.append('phone', phone);
                    regFd.append('password', document.getElementById('accountPassword').value);
                    regFd.append('address', document.getElementById('address').value);
                    regFd.append('city', document.getElementById('city').value);
                    regFd.append('country', document.getElementById('country').value);

                    fetch('<?php echo SITE_URL; ?>/api/customers.php', { method: 'POST', body: regFd })
                        .then(function(r) { return r.json(); })
                        .then(function(regData) {
                            if (regData.success && regData.customer) {
                                localStorage.setItem('zagatech_current_user', JSON.stringify({
                                    name: regData.customer.name,
                                    email: regData.customer.email
                                }));
                            }
                        })
                        .catch(function(err) { console.warn('Account registration failed:', err); });
                }

                // Build WhatsApp redirect
                var siteBase = window.location.origin + '/Zaga';
                var isAdmin = <?php echo (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) ? 'true' : 'false'; ?>;
                var isLoggedIn = <?php echo is_logged_in() ? 'true' : 'false'; ?>;

                var viewLink;
                if (isAdmin) {
                    viewLink = siteBase + '/admin/orders?view=' + encodeURIComponent(orderNumber);
                } else if (isLoggedIn) {
                    viewLink = siteBase + '/account/orders?order=' + encodeURIComponent(orderNumber);
                } else {
                    viewLink = siteBase + '/order-history?track=' + encodeURIComponent(orderNumber);
                }

                var methodLabel = paymentMethod === 'cod' ? 'Cash on Delivery' : 'Mobile Money Deposit';
                var waMessage = encodeURIComponent(
                    'I have made an order.\n' +
                    'Order ID: ' + orderNumber + '\n' +
                    'Payment Method: ' + methodLabel + '\n' +
                    'Amount: UGX ' + totalNow.toFixed(2) + '\n' +
                    'View Order: ' + viewLink
                );

                window.location.href = 'https://wa.me/256700706809?text=' + waMessage;
            })
            .catch(function(err) {
                console.error('Order creation failed:', err);
                showToast('Failed to place order. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Purchase';
            });
    }
</script>
