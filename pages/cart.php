<?php
// ============================================================
// Zaga Technologies - Cart Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Shopping Cart';
$current_page = '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container cart-container">
    <h1>Shopping Cart</h1>

    <div class="cart-content">
        <!-- Cart Items -->
        <div class="cart-items">
            <p class="cart-scroll-hint" style="display:none;font-size:12px;color:#64748b;margin-bottom:8px;text-align:center;">Swipe left/right to see all columns</p>
            <table class="cart-table" id="cartTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cartItemsBody">
                    <!-- Cart items will be loaded here -->
                </tbody>
            </table>
            <div id="emptyCart" class="empty-cart" style="display: none;">
                <p>Your cart is empty</p>
                <a href="<?php echo SITE_URL; ?>/shop" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>

        <!-- Cart Summary -->
        <aside class="cart-summary">
            <h2>Order Summary</h2>
            <div class="summary-item">
                <span>Subtotal:</span>
                <span id="subtotal">UGX 0.00</span>
            </div>
            <div class="summary-item">
                <span>Shipping:</span>
                <span id="shipping">Free</span>
            </div>
            <div class="summary-item">
                <span>VAT (18%):</span>
                <span id="tax">UGX 0.00</span>
            </div>
            <div class="summary-item total">
                <span>Total:</span>
                <span id="total">UGX 0.00</span>
            </div>

            <div class="promo-code">
                <input type="text" id="promoCode" placeholder="Enter promo code" class="input">
                <button id="applyPromo" class="btn btn-secondary">Apply</button>
            </div>

            <button id="checkoutBtn" class="btn btn-primary"
                style="width: 100%; padding: 12px; margin-top: 20px;">Proceed to Checkout</button>
            <a href="<?php echo SITE_URL; ?>/shop" class="btn btn-secondary"
                style="width: 100%; padding: 12px; margin-top: 10px; text-align: center; text-decoration: none; display: block;">Continue
                Shopping</a>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        // Wait for products to be loaded before rendering cart
        if (window._productsLoaded) {
            displayCart();
        } else {
            window.addEventListener('products-loaded', function() {
                displayCart();
            });
        }
        setupCartEvents();
        updateCartCount();
    });

    function displayCart() {
        var cart = getCart();
        var tbody = document.getElementById('cartItemsBody');
        var emptyCart = document.getElementById('emptyCart');
        var cartTable = document.getElementById('cartTable');

        if (cart.length === 0) {
            tbody.innerHTML = '';
            cartTable.style.display = 'none';
            emptyCart.style.display = 'block';
            updateCartSummary([]);
            return;
        }

        cartTable.style.display = 'table';
        emptyCart.style.display = 'none';

        tbody.innerHTML = cart.map(function(item) {
            // Handle both products and courses
            var itemData, itemId;
            if (item.type === 'course') {
                itemData = item.courseData || courses.find(function(c) { return c.id === item.itemId; });
                itemId = item.itemId;
            } else {
                itemData = products.find(function(p) { return p.id === item.productId; });
                itemId = item.productId;
            }
            if (!itemData) return '';

            var total = itemData.price * item.quantity;
            // If there is a credit payment plan, compute deposit and monthly
            var creditHtml = '';
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var months = item.paymentPlan.months || 3;
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(itemData.price * item.quantity, months, interestRate);
                creditHtml = '<div style="margin-top:6px;color:#065f46;font-weight:600;">Credit: Deposit UGX ' + c.deposit.toFixed(2) + ' — UGX ' + c.monthly.toFixed(2) + '/mo x ' + c.months + ' months</div>';
            }

            var removeCall = item.type === 'course' ? "removeFromCart('" + itemId + "', 'course')" : 'removeFromCart(' + itemId + ')';
            var updateCall = item.type === 'course' ? "updateQuantity('" + itemId + "', this.value, 'course')" : 'updateQuantity(' + itemId + ', this.value)';

            var detailUrl;
            if (item.type === 'course') {
                var courseSlug = (itemData.slug || itemData.title || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                detailUrl = '<?php echo SITE_URL; ?>/course/' + courseSlug;
            } else {
                var productSlug = itemData.slug;
                detailUrl = productSlug ? '<?php echo SITE_URL; ?>/product/' + productSlug : '<?php echo SITE_URL; ?>/product-detail?id=' + itemId;
            }

            // Course items use icon (emoji) when no image; products use image file
            var imageHtml;
            if (item.type === 'course' && (!itemData.image || itemData.image === '')) {
                var icon = itemData.icon || item.courseData?.icon || '📚';
                imageHtml = '<a href="' + detailUrl + '"><span class="cart-item-image" style="display:flex;align-items:center;justify-content:center;font-size:32px;background:#f1f5f9;border-radius:5px;width:80px;height:80px;">' + icon + '</span></a>';
            } else {
                imageHtml = '<a href="' + detailUrl + '"><img src="' + escapeHtml(itemData.image) + '" alt="' + escapeHtml(itemData.title) + '" class="cart-item-image"></a>';
            }

            return '<tr>' +
                '<td>' +
                    '<div class="cart-item-info">' +
                        imageHtml +
                        '<div>' +
                            '<h4><a href="' + detailUrl + '" style="color:inherit;text-decoration:none;">' + escapeHtml(itemData.title) + '</a></h4>' +
                            '<p>' + escapeHtml(itemData.category || '') + (item.type === 'course' ? ' (Course)' : '') + '</p>' +
                            creditHtml +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '<td>' + formatPrice(itemData.price) + '</td>' +
                '<td>' +
                    '<input type="number" value="' + item.quantity + '" min="1" max="100" class="qty-input" onchange="' + updateCall + '">' +
                '</td>' +
                '<td>' + formatPrice(total) + '</td>' +
                '<td>' +
                    '<button onclick="' + removeCall + '" class="btn-remove">Remove</button>' +
                '</td>' +
            '</tr>';
        }).join('');

        updateCartSummary(cart);
    }

    function updateCartSummary(cart) {
        var subtotalNow = cart.reduce(function(sum, item) {
            var itemData;
            if (item.type === 'course') {
                itemData = item.courseData || courses.find(function(c) { return c.id === item.itemId; });
            } else {
                itemData = products.find(function(p) { return p.id === item.productId; });
            }
            if (!itemData) return sum;
            if (item.paymentPlan && item.paymentPlan.type === 'credit') {
                var months = item.paymentPlan.months || 3;
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(itemData.price * item.quantity, months, interestRate);
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
                var months = item.paymentPlan.months || 3;
                var interestRate = item.paymentPlan.interestRate || 0;
                var c = computeCredit(itemData.price * item.quantity, months, interestRate);
                return sum + c.remaining;
            }
            return sum;
        }, 0);

        var tax = subtotalNow * 0.18;
        var totalNow = subtotalNow + tax;

        document.getElementById('subtotal').textContent = 'UGX ' + subtotalNow.toFixed(2);
        document.getElementById('tax').textContent = 'UGX ' + tax.toFixed(2);
        document.getElementById('total').textContent = 'UGX ' + totalNow.toFixed(2);

        // Optionally show remaining balance info
        var existing = document.getElementById('remainingInfo');
        if (existing) existing.remove();
        if (remainingBalance > 0) {
            var node = document.createElement('div');
            node.id = 'remainingInfo';
            node.style.marginTop = '10px';
            node.style.fontSize = '14px';
            node.style.color = '#374151';
            node.innerHTML = 'Remaining to be paid over time: <strong>UGX ' + remainingBalance.toFixed(2) + '</strong>';
            document.querySelector('.cart-summary').appendChild(node);
        }
    }

    function setupCartEvents() {
        document.getElementById('applyPromo').addEventListener('click', function () {
            var promoCode = document.getElementById('promoCode').value;
            if (promoCode === 'SAVE10') {
                showToast('Promo code applied! 10% discount');
            } else if (promoCode === 'SAVE20') {
                showToast('Promo code applied! 20% discount');
            } else {
                showToast('Invalid promo code', 'error');
            }
        });

        document.getElementById('checkoutBtn').addEventListener('click', function () {
            var cart = getCart();
            if (cart.length === 0) {
                showToast('Your cart is empty.', 'warning');
                return;
            }
            window.location.href = '<?php echo SITE_URL; ?>/checkout';
        });
    }
</script>
