<?php
// ============================================================
// Zaga Technologies - Homepage
// ============================================================

require_once __DIR__ . '/includes/config.php';

$page_title = 'Home';
$current_page = 'home';

// Load categories from database
$conn = getDbConnection();

$catResult = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name ASC");
$categories = [];
while ($row = $catResult->fetch_assoc()) { $categories[] = $row; }

// Load courses from database
$digitalResult = $conn->query("SELECT * FROM courses WHERE course_type = 'digital_skilling' ORDER BY id ASC");
$digitalCourses = [];
while ($row = $digitalResult->fetch_assoc()) { $digitalCourses[] = $row; }

$entreprenResult = $conn->query("SELECT * FROM courses WHERE course_type = 'entrepreneurship' ORDER BY id ASC");
$entreprenCourses = [];
while ($row = $entreprenResult->fetch_assoc()) { $entreprenCourses[] = $row; }

$conn->close();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h2>Zaga Tech Credit</h2>
        <h3>Financing Digital Empowerment!</h3>
        <p>Buy Now &amp; Pay Later</p>
        <a href="<?php echo SITE_URL; ?>/shop" class="hero-btn">Apply Now</a>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <h2>Own Now &amp; Pay Later</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <div class="category-card" onclick="filterByCategory('<?php echo safe_output($cat['name']); ?>')">
                <div class="category-icon"><?php echo $cat['icon']; ?></div>
                <h3><?php echo safe_output($cat['name']); ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="featured-section">
    <div class="container">
        <h2>Featured Products</h2>
        <div id="featuredProducts" class="products-grid">
            <!-- Products will be loaded here by JavaScript -->
        </div>
    </div>
</section>

<!-- Offline Courses for Students Section -->
<section class="courses-section">
    <div class="container">
        <h2>Offline Courses for Students</h2>

        <!-- Digital Skilling Courses Subsection -->
        <h3 style="margin-top: 40px; margin-bottom: 20px; color: #1f2937; font-size: 24px;">Digital Skilling Courses</h3>
        <div class="products-grid">
            <?php foreach ($digitalCourses as $course): ?>
            <div class="product-card">
                <div class="product-image" style="display:flex;align-items:center;justify-content:center;font-size:48px;background:#f8fafc;position:relative;">
                    <span><?php echo $course['icon']; ?></span>
                    <?php if (!empty($course['credit_available']) && floatval($course['default_apr'] ?? 0) > 0): ?>
                    <span class="credit-badge credit-badge--text">Available on <?php echo number_format($course['default_apr'], 0); ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="category-badge">Digital Skills</span>
                    <h3><?php echo safe_output($course['title']); ?></h3>
                    <div class="rating">
                        <span class="stars"><?php echo str_repeat('&#11088;', (int)($course['rating'] ?? 4)); ?></span>
                        <span class="rating-value"><?php echo $course['rating'] ?? '4.0'; ?> (<?php echo $course['reviews'] ?? 0; ?>)</span>
                    </div>
                    <p class="description"><?php echo safe_output(mb_substr($course['description'], 0, 80)); ?>...</p>
                    <div class="price-section">
                        <span class="price">UGX <?php echo number_format($course['price'], 0, '.', ','); ?></span>
                    </div>
                    <div class="actions">
                        <?php
                        $courseSlug = $course['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($course['title'])), '-'));
                        $linkUrl = SITE_URL . '/course/' . $courseSlug;
                        ?>
                        <a href="<?php echo $linkUrl; ?>" class="btn btn-primary">Learn More</a>
                        <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-secondary">Enroll Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Entrepreneurship Courses Subsection -->
        <h3 style="margin-top: 60px; margin-bottom: 20px; color: #1f2937; font-size: 24px;">Entrepreneurship Courses</h3>
        <div class="products-grid">
            <?php foreach ($entreprenCourses as $course): ?>
            <div class="product-card">
                <div class="product-image" style="display:flex;align-items:center;justify-content:center;font-size:48px;background:#f8fafc;position:relative;">
                    <span><?php echo $course['icon']; ?></span>
                    <?php if (!empty($course['credit_available']) && floatval($course['default_apr'] ?? 0) > 0): ?>
                    <span class="credit-badge credit-badge--text">Available on <?php echo number_format($course['default_apr'], 0); ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="category-badge">Entrepreneurship</span>
                    <h3><?php echo safe_output($course['title']); ?></h3>
                    <div class="rating">
                        <span class="stars"><?php echo str_repeat('&#11088;', (int)($course['rating'] ?? 4)); ?></span>
                        <span class="rating-value"><?php echo $course['rating'] ?? '4.0'; ?> (<?php echo $course['reviews'] ?? 0; ?>)</span>
                    </div>
                    <p class="description"><?php echo safe_output(mb_substr($course['description'], 0, 80)); ?>...</p>
                    <div class="price-section">
                        <span class="price">UGX <?php echo number_format($course['price'], 0, '.', ','); ?></span>
                    </div>
                    <div class="actions">
                        <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-primary">View Details</a>
                        <a href="<?php echo SITE_URL; ?>/courses" class="btn btn-secondary">Enroll Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
    // --- Featured products loaded via JS from API ---
    function displayFeaturedProducts() {
        var featured = products.slice(0, 8);
        var container = document.getElementById('featuredProducts');
        if (!container) return;
        container.innerHTML = featured.map(function(product) {
            var badgeHtml = '';
            var apr = Number(product.defaultAPR || product.default_apr || 0);
            if (product.creditAvailable !== false && apr > 0) {
                badgeHtml = '<span class="credit-badge credit-badge--text">Available on ' + apr + '%</span>';
            }
            var productUrl = product.slug ? '<?php echo SITE_URL; ?>/product/' + product.slug : '<?php echo SITE_URL; ?>/product-detail?id=' + product.id;
            return '<div class="product-card">' +
                '<div class="product-image" style="position:relative;">' +
                    '<a href="' + productUrl + '"><img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.title) + '" loading="lazy"></a>' +
                    (product.discount ? '<span class="discount-badge">' + product.discount + '% OFF</span>' : '') +
                    badgeHtml +
                '</div>' +
                '<div class="product-info">' +
                    '<span class="category-badge">' + escapeHtml(product.category || '') + '</span>' +
                    '<h3><a href="' + productUrl + '" style="color:inherit;text-decoration:none;">' + escapeHtml(product.title) + '</a></h3>' +
                    '<div class="rating"><span class="stars">' + getStars(product.rating) + '</span>' +
                    '<span class="rating-value">' + product.rating + ' (' + product.reviews + ')</span></div>' +
                    '<p class="description">' + escapeHtml((product.description || '').substring(0, 80)) + '...</p>' +
                    '<div class="price-section"><span class="price">' + formatPrice(product.price) + '</span></div>' +
                    '<div class="actions">' +
                        '<a href="' + productUrl + '" class="btn btn-primary">View Details</a>' +
                        '<button onclick="addToCartWithChoice(' + product.id + ')" class="btn btn-secondary">Add to Cart</button>' +
                    '</div>' +
                '</div></div>';
        }).join('');
    }

    // Listen for products loaded from API
    window.addEventListener('products-loaded', displayFeaturedProducts);

    window.addEventListener('DOMContentLoaded', function () {
        if (window._productsLoaded) displayFeaturedProducts();
    });

    function filterByCategory(category) {
        sessionStorage.setItem('selectedCategory', category);
        window.location.href = '<?php echo SITE_URL; ?>/shop';
    }

    function addToCartWithChoice(id) {
        showPaymentChoice({
            productId: id,
            quantity: 1,
            onConfirm: function(paymentPlan) {
                addToCart(id, 1, paymentPlan);
                showToast('Product added to cart!');
            }
        });
    }
</script>
