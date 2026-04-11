<?php
// ============================================================
// Zaga Technologies - Product Detail Page
// ============================================================

require_once __DIR__ . '/../includes/config.php';

$page_title = 'Product Details';
$current_page = '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container product-detail-container">
    <div class="breadcrumb">
        <a href="<?php echo SITE_URL; ?>/">Home</a> / <a href="<?php echo SITE_URL; ?>/shop">Shop</a> / <span id="breadcrumbProduct">Product</span>
    </div>

    <div class="product-detail">
        <div class="product-gallery">
            <img id="mainImage" src="" alt="Product" class="main-image">
            <div class="thumbnails" id="thumbnails"></div>
        </div>

        <div class="product-details-content">
            <div id="productDetailsContent"></div>

            <div class="product-actions">
                <div class="quantity-selector">
                    <label>Quantity:</label>
                    <button id="decreaseQty" class="qty-btn">-</button>
                    <input type="number" id="quantityInput" value="1" min="1" max="100" class="qty-input">
                    <button id="increaseQty" class="qty-btn">+</button>
                </div>
                <button id="addToCartBtn" class="btn btn-primary"
                    style="width: 100%; padding: 15px; font-size: 16px; text-align: center; display: block; border: none; cursor: pointer;">Add
                    to Cart</button>
                <button id="buyNowBtn" class="btn btn-secondary"
                    style="width: 100%; padding: 15px; font-size: 16px; text-align: center; display: block; border: none; cursor: pointer;">Buy
                    Now</button>
                <a href="https://wa.me/256700706809" target="_blank" class="btn-cta"
                    style="width: 100%; padding: 12px; font-size: 15px; margin-top:8px; background:#f59e0b;color:#fff;border:none;border-radius:6px; text-align: center; display: block; text-decoration: none;">Apply
                    for Credit Now</a>
            </div>

            <div class="product-info-box">
                <h3>Product Information</h3>
                <ul id="productInfoList"></ul>
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <section id="reviewsSection" style="margin-top:30px;padding-top:25px;border-top:1px solid #e2e8f0;">
        <h2 style="margin-bottom:15px;">Customer Reviews</h2>
        <div id="reviewsList" style="margin-bottom:25px;">
            <p style="color:#94a3b8;">Loading reviews...</p>
        </div>

        <!-- Reviews are managed by admin only -->
    </section>

    <!-- Related Products -->
    <section class="related-products">
        <h2>Related Products</h2>
        <div id="relatedProducts" class="products-grid"></div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
    var currentProductId = null;
    var currentProduct = null;

    window.addEventListener('DOMContentLoaded', function () {
        var urlParams = new URLSearchParams(window.location.search);
        var productId = urlParams.get('id') || '<?php echo intval($_GET['id'] ?? 0); ?>' || sessionStorage.getItem('productId');
        var productSlug = urlParams.get('slug') || '<?php echo safe_output($_GET['slug'] ?? ''); ?>' || null;
        if (productId === '0') productId = null;
        if (productSlug === '') productSlug = null;

        function initProduct() {
            if (productSlug && !productId) {
                // Find product by slug
                var found = products.find(function(p) { return p.slug === productSlug; });
                if (found) currentProductId = found.id;
            } else if (productId) {
                currentProductId = parseInt(productId);
            }

            if (currentProductId) {
                loadProductDetails();
                handleCreditIntent();
            }
        }

        if (window._productsLoaded) {
            initProduct();
        } else {
            window.addEventListener('products-loaded', function() {
                initProduct();
            });
        }

        updateCartCount();
    });

    function handleCreditIntent() {
        var openCredit = sessionStorage.getItem('openCredit');
        if (openCredit === '1') {
            var aprStr = sessionStorage.getItem('openCreditAPR');
            var apr = aprStr ? Number(aprStr) : undefined;
            sessionStorage.removeItem('openCredit');
            sessionStorage.removeItem('openCreditAPR');
            showPaymentChoice({
                productId: currentProductId,
                quantity: 1,
                defaultToCredit: true,
                defaultAPR: apr,
                onConfirm: function(paymentPlan) {
                    addToCart(currentProductId, 1, paymentPlan);
                    window.location.href = '<?php echo SITE_URL; ?>/checkout';
                }
            });
        }
    }

    function loadProductDetails() {
        currentProduct = products.find(function(p) { return p.id === currentProductId; });
        if (!currentProduct) {
            document.getElementById('productDetailsContent').innerHTML = '<p>Product not found</p>';
            return;
        }

        // Update breadcrumb
        document.getElementById('breadcrumbProduct').textContent = currentProduct.title;

        // Update main image
        var images = [currentProduct.image].concat(currentProduct.additionalImages || []);
        document.getElementById('mainImage').src = currentProduct.image;

        // Display thumbnails
        var thumbnailsContainer = document.getElementById('thumbnails');
        thumbnailsContainer.innerHTML = images.map(function(img, index) {
            return '<img src="' + escapeHtml(img) + '" alt="Thumbnail ' + (index + 1) + '" class="thumbnail ' + (index === 0 ? 'active' : '') + '" onclick="changeImage(this)">';
        }).join('');

        // Display product details
        var featuresHtml = '';
        if (currentProduct.features) {
            featuresHtml = currentProduct.features.map(function(feature) {
                return '<li>' + escapeHtml(feature) + '</li>';
            }).join('');
        }

        var detailsHtml =
            '<h1>' + escapeHtml(currentProduct.title) + '</h1>' +
            '<div class="rating">' +
                '<span class="stars">' + getStars(currentProduct.rating) + '</span>' +
                '<span class="rating-value">' + currentProduct.rating + ' (' + currentProduct.reviews + ' reviews)</span>' +
            '</div>' +
            '<div class="price-detail">' +
                '<span class="price-label">Price:</span>' +
                '<span class="price">' + formatPrice(currentProduct.price) + '</span>' +
                (currentProduct.originalPrice ? '<span class="original-price">' + formatPrice(currentProduct.originalPrice) + '</span>' : '') +
            '</div>' +
            '<div class="availability">' +
                '<span class="stock-status ' + (currentProduct.inStock ? 'in-stock' : 'out-of-stock') + '">' +
                    (currentProduct.inStock ? '&#10003; In Stock' : '&#10007; Out of Stock') +
                '</span>' +
            '</div>' +
            '<div class="description-full">' +
                '<h3>Description</h3>' +
                '<p>' + escapeHtml(currentProduct.description) + '</p>' +
            '</div>' +
            '<div class="specs">' +
                '<h3>Key Features</h3>' +
                '<ul>' + featuresHtml + '</ul>' +
            '</div>';

        document.getElementById('productDetailsContent').innerHTML = detailsHtml;

        // Update main image alt text
        document.getElementById('mainImage').alt = currentProduct.title;

        // Product info box
        var infoHtml =
            '<li><strong>Category:</strong> ' + escapeHtml(currentProduct.category) + '</li>' +
            '<li><strong>SKU:</strong> ' + escapeHtml(currentProduct.sku || '') + '</li>' +
            '<li><strong>Warranty:</strong> ' + escapeHtml(currentProduct.warranty || '') + '</li>' +
            '<li><strong>Shipping:</strong> Free Shipping</li>';
        document.getElementById('productInfoList').innerHTML = infoHtml;

        // Setup action buttons
        document.getElementById('addToCartBtn').addEventListener('click', handleAddToCart);
        document.getElementById('buyNowBtn').addEventListener('click', handleBuyNow);

        document.getElementById('increaseQty').addEventListener('click', function() {
            var input = document.getElementById('quantityInput');
            input.value = parseInt(input.value) + 1;
        });
        document.getElementById('decreaseQty').addEventListener('click', function() {
            var input = document.getElementById('quantityInput');
            if (input.value > 1) input.value = parseInt(input.value) - 1;
        });

        // Load related products
        loadRelatedProducts();

        // Load reviews
        loadProductReviews();
    }

    function changeImage(element) {
        document.querySelectorAll('.thumbnail').forEach(function(t) { t.classList.remove('active'); });
        element.classList.add('active');
        document.getElementById('mainImage').src = element.src;
    }

    function handleAddToCart() {
        var quantity = parseInt(document.getElementById('quantityInput').value);
        showPaymentChoice({
            productId: currentProductId, quantity: quantity, onConfirm: function(paymentPlan) {
                addToCart(currentProductId, quantity, paymentPlan);
                showToast('Added ' + quantity + ' item(s) to cart!');
            }
        });
    }

    function handleBuyNow() {
        var quantity = parseInt(document.getElementById('quantityInput').value);
        showPaymentChoice({
            productId: currentProductId, quantity: quantity, onConfirm: function(paymentPlan) {
                addToCart(currentProductId, quantity, paymentPlan, true);
                window.location.href = '<?php echo SITE_URL; ?>/checkout';
            }
        });
    }

    function loadRelatedProducts() {
        var related = products.filter(function(p) { return p.category === currentProduct.category && p.id !== currentProductId; }).slice(0, 4);
        var container = document.getElementById('relatedProducts');
        if (related.length === 0) {
            container.innerHTML = '<p style="color:#94a3b8;text-align:center;">No related products found.</p>';
            return;
        }
        container.innerHTML = related.map(function(product) {
            var badgeHtml = '';
            var eligible = (product.creditAvailable !== false) && (product.price >= 50);
            if (eligible) {
                var apr = Number(product.defaultAPR || product.default_apr || 0);
                if (apr > 0) {
                    var aprText = 'Available on ' + apr + '%';
                    badgeHtml = '<span class="credit-badge credit-badge--text" tabindex="0" role="button" aria-label="' + aprText + '">' + aprText + '</span>';
                }
            }
            var productUrl = product.slug ? '<?php echo SITE_URL; ?>/product/' + product.slug : '<?php echo SITE_URL; ?>/product-detail?id=' + product.id;

            return '<div class="product-card">' +
                '<div class="product-image">' +
                    '<a href="' + productUrl + '"><img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.title) + '" loading="lazy"></a>' +
                    (product.discount ? '<span class="discount-badge">' + product.discount + '% OFF</span>' : '') +
                    badgeHtml +
                '</div>' +
                '<div class="product-info">' +
                    '<span class="category-badge">' + escapeHtml(product.category) + '</span>' +
                    '<h3><a href="' + productUrl + '" style="color:inherit;text-decoration:none;">' + escapeHtml(product.title) + '</a></h3>' +
                    '<div class="rating">' +
                        '<span class="stars">' + getStars(product.rating) + '</span>' +
                        '<span class="rating-value">' + product.rating + ' (' + product.reviews + ')</span>' +
                    '</div>' +
                    '<p class="description">' + escapeHtml((product.description || '').substring(0, 80)) + '...</p>' +
                    '<div class="price-section">' +
                        '<span class="price">' + formatPrice(product.price) + '</span>' +
                    '</div>' +
                    '<div class="actions">' +
                        '<a href="' + productUrl + '" class="btn btn-primary">View Details</a>' +
                        '<button onclick="addToCart(' + product.id + ')" class="btn btn-secondary">Add to Cart</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function openCreditForRelated(id, apr) {
        showPaymentChoice({
            productId: id,
            quantity: 1,
            defaultToCredit: true,
            defaultAPR: (apr !== null && typeof apr !== 'undefined') ? Number(apr) : undefined,
            onConfirm: function(paymentPlan) {
                addToCart(id, 1, paymentPlan);
                window.location.href = '<?php echo SITE_URL; ?>/checkout';
            }
        });
    }

    // ========== REVIEWS ==========
    function loadProductReviews() {
        if (!currentProductId) return;
        fetch('<?php echo SITE_URL; ?>/api/reviews.php?action=list&item_type=product&item_id=' + currentProductId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var container = document.getElementById('reviewsList');
                if (data.success && data.data.length > 0) {
                    container.innerHTML = data.data.map(function(r) {
                        var stars = '';
                        for (var i = 0; i < Math.round(r.rating); i++) stars += '\u2605';
                        for (var j = Math.round(r.rating); j < 5; j++) stars += '\u2606';
                        return '<div style="padding:15px;background:white;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:12px;">' +
                            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">' +
                                '<strong>' + escapeHtml(r.customer_name) + '</strong>' +
                                '<span style="color:#f59e0b;font-size:16px;">' + stars + '</span>' +
                            '</div>' +
                            '<p style="margin:0;color:#475569;font-size:14px;line-height:1.5;">' + escapeHtml(r.review_text) + '</p>' +
                            '<div style="margin-top:6px;font-size:12px;color:#94a3b8;">' + new Date(r.created_at).toLocaleDateString() + '</div>' +
                        '</div>';
                    }).join('');
                } else {
                    container.innerHTML = '<p style="color:#94a3b8;font-size:14px;">No reviews yet. Be the first to review this product!</p>';
                }
            })
            .catch(function() {
                document.getElementById('reviewsList').innerHTML = '<p style="color:#94a3b8;font-size:14px;">No reviews yet.</p>';
            });
    }

    // Submit review
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('reviewForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!currentProductId) return;

                var fd = new FormData();
                fd.append('action', 'submit');
                fd.append('customer_name', document.getElementById('reviewName').value.trim());
                fd.append('customer_email', document.getElementById('reviewEmail').value.trim());
                fd.append('item_type', 'product');
                fd.append('item_id', currentProductId);
                fd.append('rating', document.getElementById('reviewRating').value);
                fd.append('review_text', document.getElementById('reviewText').value.trim());

                fetch('<?php echo SITE_URL; ?>/api/reviews.php', { method: 'POST', body: fd })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        var msg = document.getElementById('reviewFormMessage');
                        if (data.success) {
                            msg.style.display = 'block';
                            msg.innerHTML = '<p style="color:#16a34a;font-weight:600;">Thank you! Your review has been submitted and is pending approval.</p>';
                            form.reset();
                        } else {
                            msg.style.display = 'block';
                            msg.innerHTML = '<p style="color:#dc2626;">' + escapeHtml(data.message || 'Failed to submit review.') + '</p>';
                        }
                    })
                    .catch(function() {
                        var msg = document.getElementById('reviewFormMessage');
                        msg.style.display = 'block';
                        msg.innerHTML = '<p style="color:#dc2626;">Unable to submit. Please try again later.</p>';
                    });
            });
        }
    });
</script>
